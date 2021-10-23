<a href="https://github.com/evercode-software/stocktake-web/actions/workflows/laravel.yml">
    <img alt="Laravel" src="https://github.com/evercode-software/stocktake-web/actions/workflows/laravel.yml/badge.svg">
</a>
<a href="https://github.com/evercode-software/stocktake-web/actions/workflows/docker.yml">
    <img alt="Docker" src="https://github.com/evercode-software/stocktake-web/actions/workflows/docker.yml/badge.svg">
</a>
<a href="https://stocktake.evercode.software">
    <img alt="Staging" src="https://img.shields.io/website?label=staging&up_message=deployed&url=https%3A%2F%2Fstocktake.evercode.software">
</a>

<br/>

# 📦 stocktake

Item Stock Management System for Unilever Indonesia.

<br/>

## 🤖 Technologies & Standards

1. [Laravel](https://laravel.com/docs/8.x) `v8`
2. [Laravel Eloquent](https://laravel.com/docs/8.x/eloquent)
3. [Laravel Events](https://laravel.com/docs/8.x/events)
4. [Laravel Queues](https://laravel.com/docs/8.x/queues)
5. [Laravel Task Scheduling](https://laravel.com/docs/8.x/scheduling)
6. [Laravel Mix](https://laravel.com/docs/8.x/mix)
7. [Laravel Broadcasting](https://laravel.com/docs/8.x/broadcasting)
8. [Laravel Jetstream](https://jetstream.laravel.com)
9. [Laravel Excel](https://laravel-excel.com)
10. [Laravel Fluent](https://github.com/lepikhinb/laravel-fluent)
11. [Laravel Cloudflare](https://github.com/monicahq/laravel-cloudflare) <sup><i>optional</i></sup>
12. [Laravel Sail](https://laravel.com/docs/8.x/sail) <sup><i>optional</i></sup>
13. [PHP PSR2 Coding Style Standard](https://www.php-fig.org/psr/psr-2)
14. [PHPStan](https://phpstan.org/) (with [Larastan](https://github.com/nunomaduro/larastan) extension)
15. [Inertia.js](https://inertiajs.com)
16. [Inertia.js Tables](https://github.com/protonemedia/inertiajs-tables-laravel-query-builder)
17. [Vue.js](https://vuejs.org)
18. [Webpack](https://webpack.js.org)
19. [Supercronic](https://github.com/aptible/supercronic)
20. [Supervisor](http://supervisord.org/index.html)
21. [MySQL 5.x](https://dev.mysql.com)
22. [Github Action CI/CD](https://github.com/features/actions) <sup><i>optional</i></sup>
23. [Docker](https://www.docker.com) <sup><i>optional</i></sup>
24. [Sentry](https://sentry.io) <sup><i>optional</i></sup>

<br/>

## 👨‍💻 Getting Started

Ikuti salah satu dari langkah berikut untuk melakukan deploy aplikasi pada tahap **development**.

### 🐋 Docker

Langkah instalasi dockerized application dengan ⛵ Laravel Sail untuk memudahkan proses deployment aplikasi

#### ✔️ Requirement

1. PHP `v8.0`
2. Composer `v2`
3. [Docker](https://www.docker.com)
    - [Docker for Windows](https://docs.docker.com/docker-for-windows/install) `v4.0.1`
    - [Docker for Linux](https://docs.docker.com/engine/install) `v4.0.1`
    - [Docker for Mac](https://docs.docker.com/docker-for-mac/install) `v20.10`

#### 🖥️ Installation

1. Clone repository ini ke local environment, lalu checkout ke `development` branch
2. Buat file `.env` dengan menyalin file `.env.example`, lalu konfigurasikan seperti berikut:
    - Ubah `APP_ENV` menjadi `local`
    - Ubah `APP_DEBUG` menjadi `true`
3. `composer i --ignore-platform-reqs`
4. `vendor/bin/sail up`

> #### 📝 Informasi
> - Jika menggunakan OS Windows, jalankan Laravel Sail dalam [Windows WSL v2](https://ubuntu.com/wsl)
> - Akses aplikasi via web pada alamat `http://127.0.0.1`
> - Alamat URL dan port aplikasi yang digunakan Laravel Sail sesuai konfigurasi pada `.env`

> #### 👌 Rekomendasi
> - Pelajari perintah **Laravel Sail** pada dokumentasi [berikut](https://laravel.com/docs/8.x/sail)

### 🏡 Self Hosted

Langkah tradisional untuk melakukan deploy aplikasi pada local atau self-hosted environment

#### ✔️ Requirement

1. PHP `v8.0`
2. Composer `v2`
3. MySQL Server `v5.7`
4. NodeJS `v15`
5. npm `v7`
6. [Laragon](https://laragon.org/) / [Laravel Valet](https://laravel.com/docs/8.x/valet) <sup><i>optional</i></sup>

#### 🖥️ Installation

1. Buat MySQL database untuk aplikasi:
    - `mysql -u root -p`
    - `create database stocktake;`
    - `create user 'stocktake'@'localhost' identified by 'stocktake';`
    - `grant all privileges on stocktake.* to 'stocktake'@'localhost';`
    - `flush privileges;`
    - `exit;`
2. Clone repository ini ke local environment, lalu checkout ke `development` branch
3. Buat file `.env` dengan menyalin file `.env.example`, lalu konfigurasikan seperti berikut:
    - Ubah `APP_ENV` menjadi `local`
    - Ubah `APP_DEBUG` menjadi `true`
4. `composer i`
5. `php artisan app:install`

> #### 📝 Informasi
> - Pastikan ekstensi PHP yang dibutuhkan Laravel `v8` sudah terpasang pada local development environment, sesuai dokumentasi [berikut](https://laravel.com/docs/8.x/deployment)

> #### 💡 Opsional
> - Gunakan `php artisan serve` untuk membuka aplikasi via PHP Built-in Web Server
> - Gunakan `php artisan queue:work database` untuk testing fitur [Queues](https://laravel.com/docs/8.x/queues)
> - Gunakan `php artisan schedule:run` untuk testing fitur [Task Scheduling](https://laravel.com/docs/8.x/scheduling)
> - Gunakan `php artisan optimize` saat setelah proses development untuk mempercepat performa aplikasi

> #### 👌 Rekomendasi
> - Gunakan `composer dev` untuk optimalisasi Laravel Intellisense pada IDE anda
> - Gunakan `composer lint` untuk menjalankan PHP Linter (Static Code Analaysis)
> - Gunakan local development environment seperti [Laragon](https://laragon.org) atau [Laravel Valet](https://laravel.com/docs/8.x/valet)

<br/>

## 🚀️ Production Deployment

Ikuti langkah berikut ini untuk melakukan deploy aplikasi pada tahap **staging** atau **production**.

### 🏡 Self Hosted

Langkah tradisional untuk melakukan deploy aplikasi pada local atau self-hosted environment

#### ✔️ Requirement

1. PHP `v8.0`
2. Composer `v2`
3. MySQL Server `v5.7`
4. NodeJS `v15`
5. npm `v7`
6. [Nginx](https://nginx.org/en/download.html) / [Apache2](https://httpd.apache.org/download.cgi)
   / [LiteSpeed](https://openlitespeed.org/Downloads/)
7. [Supercronic](https://github.com/aptible/supercronic)
8. [Supervisor](http://supervisord.org/index.html)

#### 🖥️ Installation

1. Buat MySQL database untuk aplikasi:
    - `mysql -u root -p`
    - `create database stocktake;`
    - `create user 'stocktake'@'localhost' identified by 'stocktake';`
    - `grant all privileges on stocktake.* to 'stocktake'@'localhost';`
    - `flush privileges;`
    - `exit;`
2. Clone repository ini ke server, lalu checkout ke `main` branch
3. Buat file `.env` dengan menyalin file `.env.example`, lalu konfigurasikan seperti berikut:
    - Ubah `APP_ENV` menjadi `production`
    - Ubah `APP_DEBUG` menjadi `false`
4. `composer i --no-dev`
5. `php artisan app:install`
6. Konfigurasikan Web Server dengan path **Document Root** mengarah ke lokasi direktori `public` pada proyek
7. Install **redis-server** untuk aplikasi:
    - `sudo apt install redis-server`
    - `sudo nano /etc/redis/redis.conf`, lalu ubah `supervised no` menjadi `supervised systemd`
    - `sudo service redis-server restart`
8. Install **supercronic** untuk aplikasi:
    - `sudo apt install snap`
    - `sudo snap install go`
    - `go get -d github.com/aptible/supercronic`
    - `cd ~/go/pkg/mod/github.com/aptible/supercronic@v<version>`
      <div style="margin-top: 21px">

      > Ubah `<version>` ke versi terakhir supercronic sesuai laman [berikut](https://github.com/aptible/supercronic/releases)

      </div>
    - `go mod vendor`
    - `go install`
    - `source /etc/profile`
    - Verifikasi instalasi supercronic dengan perintah `supercronic`
    - Siapkan cronjob untuk aplikasi:
      ```bash
      rm -rf ~/supercronic && \
      sudo mkdir ~/supercronic && \
      sudo touch ~/supercronic/stocktake-web.cron && \
      sudo echo "path=/var/www/stocktake/web" >> ~/supercronic/stocktake-web.cron && \
      sudo echo "0 1 * * * rm -rf $path/storage/logs/laravel.log && touch $path/storage/logs/laravel.log" >> ~/supercronic/stocktake-web.cron && \
      sudo echo "0 1 * * * rm -rf $path/storage/logs/stocktake-web-cron.log && touch $path/storage/logs/stocktake-web-cron.log" >> ~/supercronic/stocktake-web.cron && \
      sudo echo "0 1 * * * rm -rf $path/storage/logs/stocktake-web-worker.log && touch $path/storage/logs/stocktake-web-worker.log" >> ~/supercronic/stocktake-web.cron && \
      sudo echo "0 1 * * * rm -rf $path/storage/logs/nginx-access.log && touch $path/storage/logs/nginx-access.log" >> ~/supercronic/stocktake-web.cron && \
      sudo echo "0 1 * * * rm -rf $path/storage/logs/nginx-error.log && touch $path/storage/logs/nginx-error.log" >> ~/supercronic/stocktake-web.cron
      ```
      > Sesuaikan path `/var/www/stocktake/web` dengan lokasi direktori proyek
9. Install **supervisor** untuk aplikasi:
    - `sudo apt install supervisor`
    - `sudo chown -R www-data:www-data /var/www/`
    - `nano /etc/supervisor/conf.d/stocktake-web-supervisor.conf`
    - Masukkan konfigurasi berikut:
      <div style="margin-top: 21px">

      ```
      [supervisord]
      nodaemon=false
      logfile=/dev/null
      logfile_maxbytes=0
      pidfile=/run/supervisord.pid
      
      [program:stocktake-web-cron]
      process_name=%(program_name)s_%(process_num)02d
      command=/root/go/bin/supercronic /root/supercronic/stocktake-web.cron
      autostart=true
      autorestart=true
      user=root
      numprocs=1
      redirect_stderr=true
      stdout_logfile=/var/www/stocktake/web/storage/logs/stocktake-web-cron.log
      stdout_logfile_maxbytes=0
      stopwaitsecs=3600
       
      [program:stocktake-web-worker]
      process_name=%(program_name)s_%(process_num)02d
      command=php /var/www/stocktake/web/artisan queue:work database --sleep=3 --tries=3
      autostart=true
      autorestart=true
      user=root
      numprocs=8
      redirect_stderr=true
      stdout_logfile=/var/www/stocktake/web/storage/logs/stocktake-web-worker.log
      stdout_logfile_maxbytes=0
      stopwaitsecs=3600
      ```

      </div>

      > Sesuaikan `/var/www/stocktake/web` dengan lokasi direktori proyek

    - `sudo supervisorctl reread`
    - `sudo supervisorctl update`
    - `sudo supervisorctl restart all` <br/>

> #### 📝 Informasi
> - Pastikan ekstensi PHP yang dibutuhkan Laravel `v8` sudah terpasang pada server, sesuai dokumentasi [berikut](https://laravel.com/docs/8.x/deployment)

> #### 💡 Opsional
> - Untuk mempercepat performa aplikasi, jalankan `php artisan optimize` saat setelah update source code pada aplikasi

> #### 👌 Rekomendasi
> - Gunakan OS berbasis Linux (e.g. Ubuntu 20.04.3 LTS) <br/>
> - Gunakan Web Server [Nginx](https://nginx.org/en/download.html), [Apache2](https://httpd.apache.org/download.cgi), atau [LiteSpeed](https://openlitespeed.org/Downloads/)
