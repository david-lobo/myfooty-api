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
class ModelUpdater
{
    /**
     * The counts of records being added
     *
     * @var array
     */
    public $counts = [
        'added' => [
            Team::class => 0,
            Competition::class => 0,
            Broadcaster::class => 0,
            Match::class => 0,
            MatchBroadcaster::class => 0
        ]
    ];

    /**
    * Create a new ModelUpdater instance.
    *
    * @return void
    */
    public function __construct()
    {
        //
    }

    /**
     * Begin the update process
     * @return void
     */
    public function update()
    {
        Log::useFiles('php://stderr');
        Log::info("ModelUpdater running");

        $this->clearModel();

        $fixtures = DB::table('fixtures_data')->get();
        $i = 0;

        //======================================================================
        // FIXTURES - CREATING DB ENTRIES
        //======================================================================
        foreach ($fixtures as $fixture) {
            if ($i >= 1000000) {
                continue;
            }

            $i++;

            $fixtureId = $fixture->fixture_id;

            Log::info("Fixture Id ${fixtureId}");

            //-----------------------------------------------------
            // KICKOFF - TIME & DATE
            //-----------------------------------------------------
            $kickoff = $fixture->kickoff;
            if (empty($kickoff)) {
                Log::info("Kickoff value missing");
                continue;
            }

            if (!$this->checkIsAValidDate($kickoff)) {
                Log::info("Invalid kickoff: " . $kickoff);
                continue;
            }

            $kickoffDate = new \DateTime($kickoff);
            //var_dump($kickoffDate->format('Y-m-d H:i:s'));

            //-----------------------------------------------------
            // COMPETITION
            //-----------------------------------------------------
            $titleNormalised = $this->normaliseTitle($fixture->competition);
            $titleAlias = trim($fixture->competition);
            $titleAlias = trim($titleAlias);
            $title = trim($fixture->competition);

            $competition = $this->findOrCreate(
                Competition::class,
                [
                    'title' => $title,
                    'title_normalised' => $titleNormalised
                ]
            );

            if (!$this->isModelObject($competition, Competition::class)) {
                Log::info(
                    "Can't proceed without competition for import id  $fixture->id with competition
                    $fixture->competition  $titleNormalised"
                );
                continue;
            }

            //-----------------------------------------------------
            // TEAMS - HOME TEAM
            //-----------------------------------------------------
            if (isset($fixture->home) && !empty($fixture->home) &&
                isset($fixture->away) && !empty($fixture->away)) {
                $homeTeam = $this->findOrCreateTeam(['title' => $fixture->home]);

                if (!$this->isModelObject($homeTeam, Team::class)) {
                    Log::info(
                        "Can't proceed without home team for import id '$fixture->id'  with home team '$fixture->home'"
                    );
                    continue;
                }
            }

            //-----------------------------------------------------
            // TEAMS - AWAY TEAM
            //-----------------------------------------------------
            if (isset($fixture->away) && !empty($fixture->away) &&
                isset($fixture->away) && !empty($fixture->away)) {
                $awayTeam = $this->findOrCreateTeam(['title' => $fixture->away]);

                if (!$this->isModelObject($awayTeam, Team::class)) {
                    Log::info(
                        "Can't proceed without away team for import id '$fixture->id'  with home team '$fixture->away'"
                    );
                    continue;
                }
            }

            //-----------------------------------------------------
            // GROUND - NOT USED
            //-----------------------------------------------------
            $ground = trim($fixture->ground);

            //-----------------------------------------------------
            // MATCH
            //-----------------------------------------------------
            $match = $this->findOrCreate(
                Match::class,
                [
                    'home_id' => $homeTeam->id,
                    'away_id' => $awayTeam->id,
                    'competition_id' => $competition->id,
                    'kickoff' => $kickoffDate->format('Y-m-d H:i:s')
                ]
            );

            if (!$this->isModelObject($match, Match::class)) {
                Log::info("Can't proceed without match entry for import id '{$fixture->id}'' with teams 'homeTeam->id}',
                          '{$awayTeam->id}' and competition '{$competition->id}'");
                continue;
            }

            //-----------------------------------------------------
            // BROADCASTS
            //-----------------------------------------------------
            $broadcasts = DB::table('broadcast_data')->where('fixture_id', $fixtureId)->get();
            $broadcastsCount = count($broadcasts);
            Log::info("Found {$broadcastsCount} broadcasts for fixture {$fixtureId}");

            foreach ($broadcasts as $broadcast) {
                if (isset($broadcast->name) && !empty($broadcast->name)) {
                    $broadcasterName = $broadcast->name;
                    $broadcasterName = str_replace('UK - ', '', $broadcasterName);
                    //$broadcasterName = str_replace('-bc', ' ', $broadcasterName);
                    //$broadcasterName = str_replace('-', ' ', $broadcasterName);
                    $broadcasterName = ucwords($broadcasterName);

                    $broadcasterNameNormalised = $this->normaliseTitle($broadcasterName);

                    Log::info("Broadcaster {$broadcasterNameNormalised}");

                    $broadcaster = $this->findOrCreate(
                        Broadcaster::class,
                        [
                            'title' => $broadcasterName,
                            'title_normalised' => $broadcasterNameNormalised
                        ]
                    );

                    if (!$this->isModelObject($broadcaster, Broadcaster::class)) {
                        Log::info(
                            "Can't proceed without broadcaster for import id '$fixture->id'  with broadcaster '$broadcasterName'"
                        );
                        continue;
                    }

                    $match = $this->findOrCreate(
                        MatchBroadcaster::class,
                        [
                            'match_id' => $match->id,
                            'broadcaster_id' => $broadcaster->id
                        ]
                    );
                }
            }
        }

        $this->logRecordsAddedCount();
    }

    /**
     * Check object is instance of BaseModel
     *
     * @param  BaseModel $object    object to check
     * @param  string    $modelName class name of model
     * @return boolean              is a model object
     */
    protected function isModelObject(BaseModel $object, $modelName)
    {
        if ((is_object($object))
            && ($object->id)
            && (get_class($object) == $modelName)
        ) {
            $className = get_class($object);
            Log::info("isModelObject valid {$className} - {$object->id}");
            return true;
        }
        Log::info("isModelObject invalid");
        return false;
    }

    /**
     * Find matching record in the db or create a new db entry
     *
     * @param  string     $modelName    class name of the model
     * @param  array      $uniqueValues db values that should be unique
     * @param  array|null $otherValues  other db values that be stored
     * @return BaseModel                an instance of a model
     */
    protected function findOrCreate(
        $modelName,
        array $uniqueValues,
        array $otherValues = null
    ) {
        $model = $modelName::firstOrNew($uniqueValues);
        $values = $uniqueValues;
        $otherValuesToSet = false;

        if (!$model->id) {
            if ($otherValues) {
                foreach ($otherValues as $key => $value) {
                    if (!array_key_exists($key, $values)) {
                        $values[$key] = $value;
                        $otherValuesToSet = true;
                        Log::info("Setting value {$key} with {$value}");
                    } else {
                        Log::info("Not setting value {$key} with {$value}");
                    }
                }
            }

            if ($model->validate($values)) {
                if ($otherValuesToSet) {
                    $model->fill($values);
                }
                Log::info("Creating {$modelName} with values", $values);
                $model->save();
                $this->counts['added'][$modelName]++;
                Log::info("Using new {$modelName} - '{$model->id}'");
            } else {
                // get the valdiation errors
                $errors = $model->errors();

                // convert errors from stdobject to array
                $errorArray = json_decode(json_encode($errors), true);

                Log::info("Unable to create new {$modelName}", $errorArray);
            }
        } else {
            Log::info("Using existing {$modelName} - '{$model->id}'");
        }
        return $model;
    }

    /**
     * Find matching Team in the db or create a new db entry.
     * If its a EPL team, set some additional values.
     *
     * @param  array $teamValues  the values to set
     * @return Team               a Team instance
     */
    protected function findOrCreateTeam($teamValues)
    {
        $teamValues['title'] = trim(html_entity_decode($teamValues['title']));
        $teamValues['title_normalised'] = $this->normaliseTitle($teamValues['title']);
        $teamValues['premier_league'] = false;
        $teamValues['image'] = null;
        $teamValues['background_color'] = null;
        $teamValues['text_color'] = null;

        $staticTeamData = $this->getPremierLeagueTeam($teamValues['title_normalised']);
        if (!empty($staticTeamData)) {
            $teamValues['premier_league'] = true;
            $teamValues['image'] = $staticTeamData['image'];
            $teamValues['background_color'] = $staticTeamData['background_color'];
            $teamValues['text_color'] = $staticTeamData['text_color'];
        }

        $team = $this->findOrCreate(
            Team::class,
            [
                'title_normalised' => $teamValues['title_normalised']
            ],
            $teamValues
        );

        return $team;
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

    /**
     * Clears the db tables for the models
     *
     * @return void
     */
    protected function clearModel()
    {
        $tablesToClear = [
            'broadcaster',
            'competition',
            'match',
            'match_broadcaster',
            'team'
        ];

        foreach ($tablesToClear as $tableName) {
            if (Schema::hasTable($tableName)) {
                DB::table($tableName)->delete();
            }
        }
    }

    /**
     * Check date is valid
     *
     * @param  string $myDateString date as a string
     * @return boolean
     */
    protected function checkIsAValidDate($myDateString)
    {
        return (bool)strtotime($myDateString);
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
     * Get the data for the premier league team given
     * @param  string $alias normalised title of team
     * @return array
     */
    protected function getPremierLeagueTeam($alias)
    {
        $team = \App\Models\TeamConfig::where('title_normalised', $alias)->first();
        if ($team) {
            return $team->toArray();
        }

        return [];
    }
}
