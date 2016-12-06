<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Library\MyFooty\Scraping\Fixtures\FileImporter;

class ImportFixturesFromFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fixtures:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import fixtures data from file';

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
        //
        $this->info('ImportFixturesFromFile running');

        $fileImporter = new FileImporter;
        $fileImporter->import();
    }
}
