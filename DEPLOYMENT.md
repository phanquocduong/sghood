# Hướng dẫn triển khai dự án

Dự án này bao gồm **backend Laravel 12** (tại `admin.sghood.com.vn`) và **frontend Nuxt 3 SSR** (tại `sghood.com.vn`), sử dụng Firebase (Firestore và Authentication). Hướng dẫn này mô tả cách triển khai trên VPS Ubuntu 22.04 với Nginx, PHP 8.2, Node.js, PM2, và Let’s Encrypt.

## Yêu cầu

-   VPS chạy **Ubuntu 22.04**.
-   Domain: `sghood.com.vn` và `admin.sghood.com.vn` (DNS A record trỏ đến IP VPS).
-   Quyền root hoặc sudo.
-   File `firebase-adminsdk.json` từ Google Cloud Console.
-   Repository GitHub: `https://github.com/phanquocduong/sghood` (backend & frontend).
-   Personal Access Token (PAT) GitHub hoặc SSH key.

## Xóa Deployment Cũ

Để triển khai mới, bạn cần xóa các tệp, cấu hình, và tiến trình liên quan đến deployment cũ để tránh xung đột.

### 1. Dừng và xóa tiến trình PM2 (Nuxt 3 SSR)

-   Kiểm tra tiến trình:
    ```bash
    pm2 list
    ```
-   Dừng và xóa tiến trình `sghood-nuxt-app`:
    ```bash
    sudo -u www-data pm2 stop sghood-nuxt-app
    sudo -u www-data pm2 delete sghood-nuxt-app
    sudo -u www-data pm2 save
    ```
-   Xóa thư mục PM2:
    ```bash
    sudo rm -rf /var/www/.pm2
    ```

### 2. Xóa thư mục dự án

-   Xóa thư mục Laravel:
    ```bash
    sudo rm -rf /var/www/html/admin.sghood.com.vn
    ```
-   Xóa thư mục Nuxt:
    ```bash
    sudo rm -rf /var/www/html/sghood.com.vn
    ```

### 3. Xóa cấu hình Nginx

-   Xóa file cấu hình:
    ```bash
    sudo rm /etc/nginx/sites-available/admin.phanquocduong.id.vn
    sudo rm /etc/nginx/sites-enabled/admin.phanquocduong.id.vn
    sudo rm /etc/nginx/sites-available/phanquocduong.id.vn
    sudo rm /etc/nginx/sites-enabled/phanquocduong.id.vn
    ```
-   Kiểm tra và reload Nginx:
    ```bash
    sudo nginx -t
    sudo systemctl reload nginx
    ```

### 4. Xóa chứng chỉ SSL (nếu cần)

-   Nếu không tái sử dụng chứng chỉ SSL:
    ```bash
    sudo certbot delete --cert-name admin.phanquocduong.id.vn
    sudo certbot delete --cert-name phanquocduong.id.vn
    ```

### 5. Xóa cache Laravel và Nuxt

-   Đảm bảo không còn cache cũ:
    ```bash
    sudo rm -rf /var/www/html/admin.sghood.com.vn/bootstrap/cache/*
    sudo rm -rf /var/www/html/sghood.com.vn/.output
    sudo rm -rf /var/www/html/sghood.com.vn/node_modules
    ```

### 6. Kiểm tra dịch vụ

-   Đảm bảo PHP-FPM và Nginx chạy bình thường:
    ```bash
    sudo systemctl status php8.2-fpm
    sudo systemctl status nginx
    ```

## Cài đặt môi trường

### 1. Cập nhật hệ thống

```bash
sudo apt update && sudo apt upgrade -y
```

### 2. Cài Nginx

```bash
sudo apt install -y nginx
sudo systemctl enable nginx
sudo systemctl start nginx
```

### 3. Cài PHP 8.2 và PHP-FPM (cho Laravel)

```bash
sudo apt install -y php8.2 php8.2-fpm php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip
sudo systemctl enable php8.2-fpm
sudo systemctl start php8.2-fpm
```

### 4. Cài Node.js 20.x (cho Nuxt 3)

```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo bash -
sudo apt install -y nodejs
node -v  # Nên là v20.x
npm -v
```

### 5. Cài PM2 (cho Nuxt 3 SSR)

```bash
sudo npm install -g pm2
```

### 6. Cài Composer (cho Laravel)

```bash
sudo apt install -y composer
```

### 7. Cài Certbot (cho SSL)

```bash
sudo apt install -y certbot python3-certbot-nginx
```

### 8. Cài `ext-grpc` (cho Firestore)

```bash
sudo pecl install protobuf-3.21.12
sudo pecl install grpc-1.56.0
echo "extension=protobuf.so" | sudo tee -a /etc/php/8.2/cli/php.ini
echo "extension=grpc.so" | sudo tee -a /etc/php/8.2/cli/php.ini
echo "extension=protobuf.so" | sudo tee -a /etc/php/8.2/fpm/php.ini
echo "extension=grpc.so" | sudo tee -a /etc/php/8.2/fpm/php.ini
php -m | grep -E "grpc|protobuf"
```

### 9. Cài Git

```bash
sudo apt install -y git
```

## Triển khai Backend Laravel (`admin.sghood.com.vn`)

### 1. Tạo thư mục dự án

```bash
sudo mkdir -p /var/www/html/admin.sghood.com.vn
sudo chown -R www-data:www-data /var/www/html/admin.sghood.com.vn
sudo chmod -R 755 /var/www/html/admin.sghood.com.vn
```

### 2. Clone dự án

-   Tạo PAT trên GitHub: **Settings** > **Developer settings** > **Personal access tokens** > **Generate new token** (chọn quyền `repo`).

```bash
cd /var/www/html/admin.sghood.com.vn
sudo -u www-data git clone https://phanquocduong:<token>@github.com/phanquocduong/sghood.git .
rm -rf frontend/ .git/
rm .gitignore DEPLOYMENT.md README.md
mv /var/www/html/admin.sghood.com.vn/backend/* /var/www/html/admin.sghood.com.vn/
```

### 3. Cài dependencies

```bash
cd /var/www/html/admin.sghood.com.vn
sudo -u www-data composer install --no-dev --optimize-autoloader
```

### 4. Cấu hình `.env`

```bash
sudo nano /var/www/html/admin.sghood.com.vn/.env
```

### 5. Cấp quyền

```bash
sudo chown -R www-data:www-data /var/www/html/admin.sghood.com.vn/storage
sudo chown -R www-data:www-data /var/www/html/admin.sghood.com.vn/bootstrap/cache
sudo chmod -R 775 /var/www/html/admin.sghood.com.vn/storage
sudo chmod -R 775 /var/www/html/admin.sghood.com.vn/bootstrap/cache
```

### 6. Cấu hình Nginx

Tạo file:

```bash
sudo nano /etc/nginx/sites-available/admin.sghood.com.vn
```

Nội dung:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name admin.sghood.com.vn;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl;
    listen [::]:443 ssl;
    server_name admin.sghood.com.vn;

    ssl_certificate /etc/letsencrypt/live/admin.sghood.com.vn/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/admin.sghood.com.vn/privkey.pem;
    include /etc/letsencrypt/options-ssl-nginx.conf;
    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem;

    root /var/www/html/admin.sghood.com.vn/public;
    index index.php index.html index.htm;

    client_max_body_size 100M;
    autoindex off;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location /phpmyadmin {
        root /var/www/html/admin.sghood.com.vn;
        index index.php index.html index.htm;
        try_files $uri $uri/ /phpmyadmin/index.php?$args;
    }

    location ~ ^/phpmyadmin/(.+\.php)$ {
        root /var/www/html/admin.sghood.com.vn;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        include snippets/fastcgi-php.conf;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ ^/phpmyadmin/(.+\.(eot|ttf|woff|woff2|jpg|jpeg|gif|css|png|js|ico|html|xml|txt))$ {
        root /var/www/html/admin.sghood.com.vn;
        add_header Access-Control-Allow-Origin *;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\. {
        deny all;
    }

}
```

Kích hoạt:

```bash
sudo ln -s /etc/nginx/sites-available/admin.sghood.com.vn /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 7 . Cài SSL

```bash
sudo certbot --nginx -d admin.sghood.com.vn -d www.admin.sghood.com.vn
```

### 8 . Cài Cronjob

```bash
cd /var/www/html/admin.sghood.com.vn
```

Mở file crontab:

```bash
crontab -e
```

Thêm lệnh cron vào crontab:

```bash
* * * * * cd /var/www/html/admin.sghood.com.vn/ && php artisan schedule:run >> /dev/null 2>&1
```

Kiểm tra cronjob đã được thêm chưa:

```bash
crontab -l
```

### 9 . Cài Suppervisor

#### Cài đặt Supervisor:

Cập nhật hệ thống và cài đặt Supervisor:

```bash
sudo apt update
sudo apt install supervisor
```

Kích hoạt và khởi động Supervisor:

```bash
sudo systemctl enable supervisor
sudo systemctl start supervisor
```

Kiểm tra trạng thái:

```bash
sudo systemctl status supervisor
```

#### Tạo file cấu hình Supervisor

Tạo file cấu hình cho Laravel worker:

```bash
sudo nano /etc/supervisor/conf.d/laravel-worker.conf
```

Nội dung cấu hình:

```bash
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/html/admin.sghood.com.vn/artisan queue:work --queue=default --sleep=3 --tries=3 --max-time=3600
directory=/var/www/html/admin.sghood.com.vn
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/html/admin.sghood.com.vn/storage/logs/worker.log
stopwaitsecs=3600
```

#### Cấu hình quyền truy cập

Đảm bảo user www-data có quyền:

```bash
sudo chown -R www-data:www-data /var/www/html/admin.sghood.com.vn
sudo chmod -R 775 /var/www/html/admin.sghood.com.vn/storage
sudo chmod -R 775 /var/www/html/admin.sghood.com.vn/bootstrap/cache
```

Cấp quyền thực thi cho artisan:

```bash
sudo chmod +x /var/www/html/admin.sghood.com.vn/artisan
```

#### Khởi động và kiểm tra Supervisor

Áp dụng cấu hình:

```bash
sudo supervisorctl reread
sudo supervisorctl update
```

Áp dụng cấu hình:

```bash
sudo supervisorctl start laravel-worker:*
```

### 10 . Tạo symbolic link để phpMyAdmin hoạt động tại admin.sghood.com.vn/phpmyadmin

```bash
scp -r D:\PRO224\sghood\backend\storage\app\public root@103.90.224.188:/var/www/html/admin.sghood.com.vn/storage/app
scp -r D:\PRO224\sghood\backend\storage\app\private root@103.90.224.188:/var/www/html/admin.sghood.com.vn/storage/app
```

### 11 . Upload storage

```bash
sudo ln -s /usr/share/phpmyadmin /var/www/html/admin.sghood.com.vn/phpmyadmin
```

## Triển khai Frontend Nuxt 3 SSR (`sghood.com.vn`)

### 1. Tạo thư mục dự án

```bash
sudo mkdir -p /var/www/html/sghood.com.vn
sudo chown -R www-data:www-data /var/www/html/sghood.com.vn
sudo chmod -R 755 /var/www/html/sghood.com.vn
```

### 2. Clone dự án

```bash
cd /var/www/html/sghood.com.vn
sudo -u www-data git clone https://phanquocduong:<token>@github.com/phanquocduong/sghood.git .
rm -rf backend/ .git/
rm .gitignore DEPLOYMENT.md README.md
mv /var/www/html/sghood.com.vn/frontend/* /var/www/html/sghood.com.vn/

sudo nano nuxt.config.ts
```

### 3. Cài dependencies

```bash
cd /var/www/html/sghood.com.vn
sudo npm install --production
sudo npm run build
```

### 4. Cấu hình reCAPTCHA

-   Thêm domain `sghood.com.vn` và `www.sghood.com.vn` vào reCAPTCHA.

### 5. Cấu hình PM2

```bash
sudo mkdir -p /var/www/.pm2/{logs,pids,modules}
sudo chown -R www-data:www-data /var/www/.pm2
sudo chmod -R 775 /var/www/.pm2
sudo -u www-data pm2 start /var/www/html/sghood.com.vn/.output/server/index.mjs --name sghood-nuxt-app
sudo -u www-data pm2 save
sudo env PATH=$PATH:/usr/bin pm2 startup systemd -u www-data --hp /var/www
```

### 6. Cấu hình Nginx

```bash
sudo nano /etc/nginx/sites-available/sghood.com.vn
```

Nội dung:

```nginx
server {
listen 80;
listen [::]:80;
server_name sghood.com.vn www.sghood.com.vn;
return 301 https://$host$request_uri;
}

server {
listen 443 ssl;
listen [::]:443 ssl;
server_name sghood.com.vn www.sghood.com.vn;

    ssl_certificate /etc/letsencrypt/live/sghood.com.vn/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/sghood.com.vn/privkey.pem;
    include /etc/letsencrypt/options-ssl-nginx.conf;
    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem;

    location / {
        proxy_pass http://localhost:3000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }

    location ~* \.(eot|ttf|woff|woff2|jpg|jpeg|gif|css|png|js|ico|html|xml|txt)$ {
        root /var/www/html/sghood.com.vn/.output/public;
        add_header Access-Control-Allow-Origin *;
    }

}
```

Kích hoạt:

```bash
sudo ln -s /etc/nginx/sites-available/sghood.com.vn /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 9. Cài SSL

```bash
sudo certbot --nginx -d sghood.com.vn -d www.sghood.com.vn
```

## Kiểm tra và xử lý lỗi

### 1. Kiểm tra log

-   **Laravel**:
    ```bash
    sudo tail -f /var/www/html/admin.sghood.com.vn/storage/logs/laravel.log
    ```
-   **Nginx**:
    ```bash
    sudo tail -f /var/log/nginx/error.log
    sudo tail -f /var/log/nginx/access.log
    ```
-   **PHP-FPM**:
    ```bash
    sudo tail -f /var/log/php8.2-fpm.log
    ```
-   **PM2**:
    ```bash
    pm2 logs sghood-nuxt-app
    ```

### 2. Xử lý lỗi thường gặp

-   **Lỗi `SplFileObject::__construct(storage/firebase/firebase-adminsdk.json)`**:
    -   Đảm bảo file `firebase-adminsdk.json` tồn tại và đúng quyền:
        ```bash
        ls -l /var/www/html/admin.sghood.com.vn/storage/firebase/firebase-adminsdk.json
        ```
    -   Kiểm tra `FIREBASE_CREDENTIALS` trong `.env`.
-   **Lỗi `auth/captcha-check-failed`**:
    -   Thêm domain `sghood.com.vn` và `www.sghood.com.vn` vào Firebase Console > **Authentication** > **Authorized Domains**.
    -   Cấu hình reCAPTCHA trong Google Cloud Console.
-   **Lỗi PM2 quyền**:
    -   Kiểm tra quyền `/var/www/.pm2`:
        ```bash
        sudo chown -R www-data:www-data /var/www/.pm2
        sudo chmod -R 775 /var/www/.pm2
        ```

### 3. Kiểm tra truy cập

-   Backend: `https://admin.sghood.com.vn`
-   Frontend: `https://sghood.com.vn`
-   Kiểm tra OTP và Firestore qua giao diện.

## Bảo mật

-   Thêm firewall:
    ```bash
    sudo ufw allow 80
    sudo ufw allow 443
    sudo ufw enable
    ```
-   Kiểm tra SSL:
    ```bash
    sudo certbot renew --dry-run
    ```
