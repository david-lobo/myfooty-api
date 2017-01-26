<?php
namespace Library\MyFooty\Scraping\Fixtures;

use App\Models\BaseModel;
use App\Models\Team;
use App\Models\Match;
use App\Models\Broadcaster;
use App\Models\Competition;
use App\Models\MatchBroadcaster;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
* Creating records in the model using the fixtures import data
*/
class TeamConfig
{
    /**
     * The counts of records being added
     *
     * @var array
     */
    public $counts = [
        'added' => [
            Team::class => 0,
        ]
    ];

    /**
     * Static data about premier league teams
     *
     * @var array
     */
    public $premierLeagueTeams;

    /**
    * Create a new ModelUpdater instance.
    *
    * @return void
    */
    public function __construct()
    {
        $this->teams = config()->get('api.teams');
    }

    /**
     * Begin the update process
     * @return void
     */
    public function setup()
    {
        Log::useFiles('php://stderr');
        Log::info("TeamConfig running");

        DB::table('team_config')->delete();

        $i = 0;

        //======================================================================
        // FIXTURES - CREATING DB ENTRIES
        //======================================================================
        foreach ($this->teams as $team) {
            if ($i >= 1000000) {
                continue;
            }

            \App\Models\TeamConfig::create([
                'title' => $team['title'],
                'title_normalised' => self::normaliseTitle($team['title']),
                'background_color' => $team['background_color'],
                'text_color' => $team['text_color'],
                'image' => $team['image']
            ]);

        }

        //$this->logRecordsAddedCount();
    }

    /**
     * Creates a normalised version of the string
     *
     * @param  string $title string to normalise
     * @return string        normalised string
     */
    protected function normaliseTitle($title)
    {
        $title = trim($title);
        $title = mb_strtolower($title, 'UTF-8');
        $title = str_replace(' ', '_', $title);
        $title = str_replace('&', 'and', $title);
        //$title = strtolower($title);
        return $title;
    }

    /**
     * Log the values from records added count
     *
     * @return void
     */
    protected function logRecordsAddedCount()
    {
        foreach ($this->counts['added'] as $key => $value) {
            Log::info("Added {$value} {$key} entries");
        }
    }

}
