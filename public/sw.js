const APP_SHELL_CACHE = "advance-billing-shell-v5";
const RUNTIME_CACHE = "advance-billing-runtime-v5";
const OFFLINE_FALLBACK = "/offline.html";
const MAX_RUNTIME_ENTRIES = 80;
const MAX_CACHEABLE_BYTES = 1_500_000; // 1.5 MB

const appShellUrls = [
  OFFLINE_FALLBACK,
  "/manifest.json",
  "/icons/icon-192.png",
  "/icons/icon-512.png",
];

function isSameOrigin(url) {
  try {
    return new URL(url).origin === self.location.origin;
  } catch {
    return false;
  }
}

function isNavigationRequest(request) {
  return request.mode === "navigate" || request.headers.get("accept")?.includes("text/html");
}

function shouldSkipRequest(request) {
  const url = request.url;
  const pathname = new URL(url).pathname;

  if (request.method !== "GET") {
    return true;
  }

  // Avoid auth/session endpoints and anything cross-origin.
  if (
    !isSameOrigin(url) ||
    pathname.startsWith("/logout")
  ) {
    return true;
  }

  return false;
}

async function trimCache(cacheName, maxEntries) {
  const cache = await caches.open(cacheName);
  const keys = await cache.keys();

  if (keys.length <= maxEntries) {
    return;
  }

  const deleteCount = keys.length - maxEntries;
  for (let i = 0; i < deleteCount; i += 1) {
    await cache.delete(keys[i]);
  }
}

async function putIntoRuntimeCache(request, response) {
  if (!response || response.status !== 200 || response.type !== "basic") {
    return;
  }

  const contentLength = Number(response.headers.get("content-length") || 0);
  if (contentLength > 0 && contentLength > MAX_CACHEABLE_BYTES) {
    return;
  }

  // Respect explicit no-store responses.
  if ((response.headers.get("cache-control") || "").toLowerCase().includes("no-store")) {
    return;
  }

  const cache = await caches.open(RUNTIME_CACHE);
  await cache.put(request, response.clone());
  await trimCache(RUNTIME_CACHE, MAX_RUNTIME_ENTRIES);
}

async function networkFirstWithOfflineFallback(request) {
  try {
    const networkResponse = await fetch(request);
    await putIntoRuntimeCache(request, networkResponse);
    return networkResponse;
  } catch (error) {
    const cached = await caches.match(request);
    if (cached) {
      return cached;
    }
    const offlinePage = await caches.match(OFFLINE_FALLBACK);
    if (offlinePage) {
      return offlinePage;
    }
    throw error;
  }
}

async function staleWhileRevalidate(request) {
  const cached = await caches.match(request);
  const networkPromise = fetch(request)
    .then(async (networkResponse) => {
      await putIntoRuntimeCache(request, networkResponse);
      return networkResponse;
    })
    .catch(() => null);

  if (cached) {
    return cached;
  }

  const networkResponse = await networkPromise;
  if (networkResponse) {
    return networkResponse;
  }

  return new Response(JSON.stringify({ message: "Offline and no cached data found." }), {
    status: 503,
    headers: { "Content-Type": "application/json" },
  });
}

self.addEventListener("message", (event) => {
  if (event.data && event.data.type === "CACHE_CURRENT_PAGE" && event.data.url) {
    event.waitUntil(
      fetch(event.data.url)
        .then((response) => putIntoRuntimeCache(event.data.url, response))
        .catch(() => {})
    );
  }
});

self.addEventListener("install", (event) => {
  event.waitUntil(
    caches
      .open(APP_SHELL_CACHE)
      .then((cache) =>
        Promise.all(
          appShellUrls.map((url) => cache.add(new Request(url, { cache: "reload" })).catch(() => null))
        )
      )
      .catch(() => {})
  );
  self.skipWaiting();
});

self.addEventListener("activate", (event) => {
  event.waitUntil(
    caches.keys().then((names) =>
      Promise.all(
        names
          .filter((name) => name !== APP_SHELL_CACHE && name !== RUNTIME_CACHE)
          .map((name) => caches.delete(name))
      )
    )
  );
  self.clients.claim();
});

self.addEventListener("fetch", (event) => {
  const { request } = event;

  if (shouldSkipRequest(request)) {
    return;
  }

  if (isNavigationRequest(request)) {
    event.respondWith(networkFirstWithOfflineFallback(request));
    return;
  }

  event.respondWith(staleWhileRevalidate(request));
});
