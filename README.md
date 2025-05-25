# SGHood

Website quản lý thuê trọ thông minh tại TP.HCM.

## Cấu trúc

-   `backend/`: Laravel API cho user và admin.
-   `frontend/`: Nuxt app cho người thuê trọ.

## Cài đặt

1. Clone repository: `git clone https://github.com/phanquocduong/troviet-platform.git`
2. Backend:
    - `cd backend`
    - `composer install`
    - `cp .env.example .env`
    - `php artisan key:generate`
    - `php artisan migrate`
3. Frontend:

    - `cd frontend`
    - `npm install`
    - `npm dev`

## Triển khai

-   Backend: Laravel Forge
-   User: Vercel
-   Admin: Vercel
