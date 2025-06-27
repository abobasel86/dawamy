# Dawamy

Dawamy is an internal HR tool built with Laravel for managing employee attendance and leave requests. It also integrates web push notifications to keep staff informed about approvals and other events.

## Setup

1. Clone the repository and install PHP dependencies:
   ```bash
   composer install
   ```
2. Install JavaScript dependencies and build assets:
   ```bash
   npm install && npm run build
   ```
3. Copy `.env.example` to `.env` and update your database credentials.
4. Generate an application key and run the migrations:
   ```bash
   php artisan key:generate
   php artisan migrate
   ```
5. Configure VAPID keys for push notifications in your `.env` file:
   ```
   VAPID_PUBLIC_KEY=
   VAPID_PRIVATE_KEY=
   VAPID_SUBJECT=mailto:example@example.com
   ```

## Features

- **Attendance logging** – Employees can punch in and out to record working hours and view their history.
- **Leave requests** – Submit leave requests with optional attachments and track approval status.
- **Push notifications** – Receive real‑time alerts for approvals and other system events.

## Optional features

Biometric verification using WebAuthn is under development. Once enabled, employees will be able to register hardware security keys or biometric devices for an additional layer of verification.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
