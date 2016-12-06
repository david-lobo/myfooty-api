<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Library\MyFooty\Scraping\Fixtures\ModelUpdater;

class UpdateFixturesModel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fixtures:update-model';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update fixtures model tables with imported data';

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
        $this->info('UpdateFixturesModel running');

        $modelUpdater = new ModelUpdater;
        $modelUpdater->update();
    }
}
