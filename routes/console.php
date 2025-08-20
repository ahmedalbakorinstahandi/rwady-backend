<?php

use App\Console\Commands\ImportProductsCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


// file storage/files/catalog_2025-08-04_16-00.csv
Schedule::command(ImportProductsCommand::class, ['file' => 'storage/files/catalog_2025-08-04_16-00.csv'])->everyMinute();
