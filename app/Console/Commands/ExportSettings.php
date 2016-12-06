<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;


class ExportSettings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fixtures:export-settings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the shared settings json file';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('ExportSettings running');

        $configs = config()->get('scraping.configs');
        $filename = "settings.json";
        $scrapeLiveUrls = env('SCRAPE_LIVE_URLS', false);

        $settings = [
            'scrape_live_urls' => $scrapeLiveUrls,
            'storage_path' => storage_path() . DIRECTORY_SEPARATOR . 'app',
            'configs' => $configs
        ];

        Storage::disk('nodejs')->put('config' . DIRECTORY_SEPARATOR . $filename, json_encode($settings));
    }
}
