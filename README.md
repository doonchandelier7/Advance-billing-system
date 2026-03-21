# Advance Payment Billing System

Laravel-based billing system with API-first architecture, Laravel Sanctum authentication, and role-based access control.

## Phase 0 – Setup (Current)

- **Laravel** project with MySQL
- **Laravel Sanctum** for API authentication (token-based)
- **Roles:** Super Admin, Admin, Employee, CA / Accountant
- **API routes:** `/api/login`, `/api/register`, `/api/user`, `/api/logout`

### Requirements

- PHP 8.2+
- Composer
- MySQL (e.g. WAMP/XAMPP)
- Node.js (for frontend assets, optional)

### Setup

1. **Configure database**  
   Copy `.env.example` to `.env` if needed. Set in `.env`:
   - `DB_CONNECTION=mysql`
   - `DB_DATABASE=advance_billing_system`
   - `DB_USERNAME` and `DB_PASSWORD` (default `root` / empty)

2. **Create MySQL database** (if it doesn’t exist):
   ```sql
   CREATE DATABASE advance_billing_system;
   ```

3. **Install dependencies & key** (if not already done):
   ```bash
   composer install
   php artisan key:generate
   ```
   If `vendor/autoload.php` is missing, run `composer install` again until it completes.

4. **Run migrations**
   ```bash
   php artisan migrate
   ```

5. **Seed roles and default Super Admin user**
   ```bash
   php artisan db:seed
   ```
   Default Super Admin: `superadmin@example.com` (password from `UserFactory`; change in production).

### API (Sanctum)

- **POST** `/api/login` – Body: `email`, `password`, optional `device_name`. Returns `token` and `user` (with `roles`).
- **POST** `/api/register` – Body: `name`, `email`, `password`, `password_confirmation`, optional `device_name`. Returns `token` and `user`.
- **GET** `/api/user` – Requires `Authorization: Bearer {token}`. Returns current user with roles.
- **POST** `/api/logout` – Requires `Authorization: Bearer {token}`. Revokes current token.

### Role-based access

Use the `role` middleware on routes, e.g.:

```php
Route::middleware(['auth:sanctum', 'role:super_admin,admin'])->get('/admin/users', ...);
```

Role slugs: `super_admin`, `admin`, `employee`, `ca_accountant`.

### Phase 4 – AI Invoice Scan & PWA

- **AI Invoice Scan** (route: `/ai-invoice`): Upload or capture an invoice/bill image; system uses configurable OCR/AI (OpenAI Vision, Google Vision, or AWS Textract) to extract header, line items, and totals. Data is auto-filled into an editable form; low-confidence fields are highlighted. After save: invoice record + print-ready view (Print to PDF).
- **Image upload**: JPG, PNG, JPEG (max 10 MB); from device or camera (mobile/laptop).
- **Configurable OCR**: Set `INVOICE_OCR_PROVIDER` in `.env` to `openai_vision`, `google_vision`, or `aws_textract` and add the corresponding API keys (see `.env.example`).
- **Security**: Uploads stored locally; optional auto-delete after extraction (`INVOICE_OCR_AUTO_DELETE_IMAGE`).
- **PWA**: `manifest.json` and service worker (`sw.js`) for installable web app on mobile and laptop.

### Offline-first Features (Added)

- **Offline save queue**: Create/update/delete operations are saved locally when internet is unavailable, then auto-synced when online.
- **Network-failure fallback queueing**: Write requests are also queued if the request fails due to runtime network errors (even if `navigator.onLine` is inaccurate).
- **Offline read cache**: Successful same-origin `GET` responses (JSON/HTML/text) are cached in local storage and reused when offline.
- **Module/table visibility offline**: Previously loaded pages and table/list data continue to display without internet.
- **Service worker runtime caching**: Updated caching strategy for navigation and runtime GET requests with offline fallback.
- **Offline data pre-download button**: `Download Offline Data` preloads core modules and API datasets for offline use.
- **Automatic background preload**: After each full page load (when online), the same preload runs in the background after ~2.5s **without toast popups** (default: at most once every **10 minutes** per browser). Adjust interval in browser DevTools → Application → Local Storage:
  - Key `abs-offline-auto-interval-min`: minutes between auto-runs (`0` or `every` = run on **every** page load — heavier on server).
- **Retry failed preload URLs**: Failed preload endpoints are tracked and can be retried via `Retry Failed Offline URLs`.
- **Preload status metadata**: Last download timestamp + success/failure counts are shown in-app and persisted locally.

#### Current limitations

- File uploads are not queued for offline sync.
- Data must be loaded once (or pre-downloaded) before it is available offline.

#### Offline preload troubleshooting

- **Success: 0 / all failed**: Usually `APP_URL` host did not match how you open the site (e.g. `http://localhost` vs `http://127.0.0.1`). Preload now uses **relative URLs** so cookies stay on the same host. Also ensure `bootstrap/app.php` has **`statefulApi()`** so `/api/*` accepts your **web session** (Sanctum).
- **Custom WAMP vhost**: Add your hostname to `SANCTUM_STATEFUL_DOMAINS` in `.env` if API preload still returns 401.

### Quick Actions (Billing Shortcuts)

Quick Actions is available in authenticated app pages (`layouts/app.blade.php`) to quickly open core billing workflows.

#### Open palette

- **Windows/Linux**: `Ctrl + K`
- **Mac**: `Cmd + K`

#### Run actions inside palette

- **Enter**: Open first (top) search result
- **1-9**: Open visible result position 1 to 9
- **0**: Open visible result position 10
- **Esc**: Close palette

#### Current action list

1. Create Sale Bill
2. Create Purchase Bill
3. Create Sales Return
4. Create Purchase Return
5. Create GST Invoice
6. Invoice List
7. Master Setup
8. Accounting
9. Reports
10. AI Invoice Scan
11. Download Offline Data

#### Notes

- Number shortcuts act on the **currently filtered list**, not only the default order.
- Key `0` is mapped to the 10th visible item.
- If fewer items are visible after searching, only available shortcut numbers are active.
- Use **Manage** button in the Quick Actions header to enable/disable actions from a panel.
- Manage panel preferences are saved in browser local storage per user/device.
- You can also open Manage from sidebar: **Admin Panel -> Manage Quick Actions**.

---

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
