<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('deploy:finish', function () {
    if (! is_file(base_path('.deploy-pending')) || is_file(base_path('.deploy-uploading'))) {
        return;
    }

    $lock = fopen(storage_path('framework/deploy.lock'), 'c');
    if (! $lock || ! flock($lock, LOCK_EX | LOCK_NB)) {
        return;
    }

    try {
        foreach (['config:clear', 'migrate', 'route:cache', 'view:cache', 'config:cache'] as $command) {
            $arguments = $command === 'migrate' ? ['--force' => true, '--no-interaction' => true] : [];
            if ($this->call($command, $arguments) !== 0) {
                return 1;
            }
        }
        unlink(base_path('.deploy-pending'));
        $this->info('Deployment finalized.');
    } finally {
        flock($lock, LOCK_UN);
        fclose($lock);
    }
})->purpose('Finalize a completed FTP deployment once');
