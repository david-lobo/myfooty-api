<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Library\MyFooty\Scraping\Fixtures\TeamConfig;

class SetupTeamConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fixtures:setup-team-config';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create the team config db entries';

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
        $this->info('SetupTeamConfig running');

        $modelUpdater = new TeamConfig;
        $modelUpdater->setup();
    }
}
