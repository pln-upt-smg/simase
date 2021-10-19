<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SessionClear extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'session:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all the user sessions';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $driver = config('session.driver');
        $method_name = 'clean' . ucfirst($driver);
        if (method_exists($this, $method_name)) {
            try {
                $this->$method_name();
                $this->info('Session data cleared!');
            } catch (Exception $e) {
                $this->error($e->getMessage());
            }
        } else {
            $this->error("Unable to remove the sessions of the driver $driver.");
        }
        return 0;
    }

    protected function cleanFile(): void
    {
        $directory = config('session.files');
        $ignoreFiles = ['.gitignore', '.', '..'];
        $files = scandir($directory);
        foreach ($files as $file) {
            if (!in_array($file, $ignoreFiles, true)) {
                unlink($directory . '/' . $file);
            }
        }
    }

    protected function cleanDatabase(): void
    {
        DB::table(config('session.table'))->truncate();
    }
}
