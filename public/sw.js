const APP_SHELL_CACHE = "advance-billing-shell-v4";
const RUNTIME_CACHE = "advance-billing-runtime-v4";
const OFFLINE_FALLBACK = "/offline.html";
const MAX_RUNTIME_ENTRIES = 80;
const MAX_CACHEABLE_BYTES = 1_500_000; // 1.5 MB

const appShellUrls = [OFFLINE_FALLBACK];

function isSameOrigin(url) {
  try {
    return new URL(url).origin === self.location.origin;
  } catch {
    return false;
  }
}

function shouldSkipRequest(request) {
  const url = request.url;
  const pathname = new URL(url).pathname;

  if (request.method !== "GET") {
    return true;
  }

  // Never cache HTML navigation requests — they contain Laravel CSRF tokens
  // that expire, causing 419 Page Expired errors when served from cache.
  if (request.mode === "navigate" || request.headers.get("accept")?.includes("text/html")) {
    return true;
  }

  // Avoid caching API, upload/process endpoints and anything cross-origin.
  if (
    !isSameOrigin(url) ||
    pathname.startsWith("/api/") ||
    pathname.includes("/upload") ||
    pathname.includes("/process") ||
    pathname.includes("/save")
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

self.addEventListener("install", (event) => {
  event.waitUntil(
    caches
      .open(APP_SHELL_CACHE)
      .then((cache) => cache.addAll(appShellUrls.map((url) => new Request(url, { cache: "reload" }))))
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

  event.respondWith(
    fetch(request)
      .then(async (networkResponse) => {
        if (!networkResponse || networkResponse.status !== 200 || networkResponse.type !== "basic") {
          return networkResponse;
        }

        const contentLength = Number(networkResponse.headers.get("content-length") || 0);
        if (contentLength > 0 && contentLength > MAX_CACHEABLE_BYTES) {
          return networkResponse;
        }

        const responseClone = networkResponse.clone();
        const cache = await caches.open(RUNTIME_CACHE);
        await cache.put(request, responseClone);
        await trimCache(RUNTIME_CACHE, MAX_RUNTIME_ENTRIES);

        return networkResponse;
      })
      .catch(async () => {
        const cached = await caches.match(request);
        if (cached) {
          return cached;
        }

        if (request.mode === "navigate") {
          const offlinePage = await caches.match(OFFLINE_FALLBACK);
          if (offlinePage) {
            return offlinePage;
          }
        }

        return caches.match("/login");
      })
  );
});
