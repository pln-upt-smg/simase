<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AppInstall extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup the Application Project based on the current environment';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        passthru('chmod -R ug+rwx storage bootstrap/cache');
        passthru('chmod -R g+w storage/logs');
        passthru('php artisan down --ansi');
        passthru('php artisan optimize:clear --ansi');
        passthru('php artisan view:clear --ansi');
        passthru('npm i --silent');
        passthru(app()->isProduction() ? 'composer i -o --no-dev --ansi --ignore-platform-reqs' : 'composer i -o --ansi --ignore-platform-reqs');
        passthru('php artisan key:generate --ansi');
        passthru('php artisan storage:link --ansi');
        passthru('php artisan migrate --seed --force --ansi');
        passthru('php artisan cloudflare:reload --ansi');
        passthru('php artisan optimize --ansi');
        passthru('php artisan view:cache --ansi');
        passthru('npm run prod --silent');
        passthru('php artisan env --ansi');
        passthru('php artisan up --ansi');
        return 0;
    }
}
