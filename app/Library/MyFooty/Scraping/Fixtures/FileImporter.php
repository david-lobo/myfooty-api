<?php
namespace Library\MyFooty\Scraping\Fixtures;

use App\Models\Match;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class FileImporter
{
    /**
     * The path to the fixtures dir
     *
     * @var string
     */
    public $fixturesPath;

    /**
     * The unique date generated archive folder name
     *
     * @var string
     */
    public $archiveName;

    /**
     * Whether to archive the file after processing
     *
     * @var boolean
     */
    public $isArchiveFiles;

    /**
     * Create a new FileImporter instance.
     *
     */
    public function __construct()
    {
        $archiveDate = new \DateTime();
        $this->archiveName = $archiveDate->format('ymd_His');
        $this->fixturesPath = config()->get('scraping.settings.paths.fixtures');
        $this->isArchiveFiles = config()->get('scraping.settings.archive');
    }

    /**
     *  Begin the import process
     *
     * @return void
     */
    public function import()
    {
        Log::useFiles('php://stderr');
        Log::info("FileImporter running");

        DB::table('fixtures_data')->delete();
        DB::table('broadcast_data')->delete();

        $competitions = config()->get('scraping.settings.competitions');
        $scrapeLiveUrls = env('SCRAPE_LIVE_URLS', false);

        foreach ($competitions as $competition) {
            $this->importCompetition($competition["id"]);
            $this->importBroadcasts($competition["id"]);
        }
    }

    /**
     *  Copies file to archive folder
     *
     * @param  string $file
     * @return void
     */
    protected function archiveFile($file)
    {
        Log::info("archiveFile ${file}");

        $archiveFile = str_replace('pending', 'archive' . DIRECTORY_SEPARATOR . $this->archiveName, $file);

        Log::info("archiveFilePath ${archiveFile}");

        Storage::copy($file, $archiveFile);
    }

    /**
     * Storage path for the fixtures
     *
     * @param  string $competitionId
     * @return string
     */
    protected function fixturesPathForCompetition($competitionId)
    {
        return $this->fixturesPath . $competitionId;
    }

    /**
     * Storage path for the broadcasts
     *
     * @param  string $competitionId
     * @return string
     */
    protected function broadcastsPathForCompetition($competitionId)
    {
        return $this->fixturesPathForCompetition($competitionId)
        . DIRECTORY_SEPARATOR . 'broadcasts';
    }

    /**
     * Call import method for each broadcast file
     *
     * @param  string $competitionId
     * @return void
     */
    protected function importBroadcasts($competitionId)
    {
        Log::info("importBroadcasts " . $competitionId);

        $broadcastsPath = $this->broadcastsPathForCompetition($competitionId);

        $files = Storage::files($broadcastsPath);

        // filter the ones that are .json
        $filteredFiles = preg_grep('/\.json$/', $files);

        foreach ($filteredFiles as $file) {
            $this->importBroadcastsFile($file);
            $this->archiveFile($file);
        }
    }

    /**
     * Process data from broadcast import file
     *
     * @param  string $file
     * @return void
     */
    protected function importBroadcastsFile($file)
    {
        Log::info("importBroadcastsFile " . $file);

        $contents = Storage::get($file);

        $fileContents = json_decode($contents, true);

        $content = $fileContents["content"];

        foreach ($content as $broadcastInfo) {
            // fixture_id, competition, kickoff, ground,  home, away, created_at
            $fixtureId = $broadcastInfo["fixture"]["id"];
            $broadcasters = $broadcastInfo["broadcasters"];

            foreach ($broadcasters as $broadcaster) {
                $channel = $broadcaster["name"];

                $broadcastObj = array();
                $broadcastObj['fixture_id'] = $fixtureId;
                $broadcastObj['name'] = $channel;

                /*echo "broadcast" . PHP_EOL;
                echo "fixture: ${broadcastObj['fixture_id']}" . PHP_EOL;
                echo "broadcaster name: ${broadcastObj['name']}" . PHP_EOL;
                echo "" . PHP_EOL;
                echo "=================" . PHP_EOL;*/

                $this->saveBroadcast($broadcastObj);
            }
        }
    }

    /**
     * Save broadcast data to db
     *
     * @param  array $broadcast
     * @return void
     */
    protected function saveBroadcast($broadcast)
    {
        if (!empty($broadcast)) {
                $createdAtDate = new \DateTime;
                $createdAt = $createdAtDate->format('Y-m-d H:i:s');

                DB::table('broadcast_data')->insert(
                    [
                        'fixture_id' => trim($broadcast['fixture_id']),
                        'name' => trim($broadcast['name']),
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt
                    ]
                );
        }
    }

    /**
     * Call import method for each fixtures file
     *
     * @param  string $competitionId
     * @return void
     */
    protected function importCompetition($competitionId)
    {
        Log::info("importCompetition ", [$competitionId]);

        $competitionPath = $this->fixturesPath . DIRECTORY_SEPARATOR . $competitionId;

        $files = Storage::files($competitionPath);

        // filter the ones that are .json
        $filteredFiles = preg_grep('/\.json$/', $files);

        foreach ($filteredFiles as $file) {
            $this->importCompetitionFile($file);
            $this->archiveFile($file);
        }
    }

    /**
     * Process data from fixtures import file
     *
     * @param  string $file
     * @return void
     */
    protected function importCompetitionFile($file)
    {
        Log::info("importCompetitionFile ", [$file]);

        $contents = Storage::get($file);

        $fileContents = json_decode($contents, true);

        $content = $fileContents["content"];

        foreach ($content as $fixture) {
            $home = "";

            if (isset($fixture["teams"][0]["team"]["club"]["shortName"])) {
                $home = $fixture["teams"][0]["team"]["club"]["shortName"];
            } else {
                $home = $fixture["teams"][0]["team"]["club"]["name"];
            }

            $away = "";
            if (isset($fixture["teams"][1]["team"]["club"]["shortName"])) {
                $away = $fixture["teams"][1]["team"]["club"]["shortName"];
            } else {
                $away = $fixture["teams"][1]["team"]["club"]["name"];
            }

            $fixtureObj = array();

            $fixtureObj['fixture_id'] = $fixture["id"];
            $fixtureObj['kickoff'] = $fixture["kickoff"]["label"];
            $fixtureObj['competition'] = $fixture["gameweek"]["compSeason"]["competition"]["description"];
            $fixtureObj['ground'] = $fixture["ground"]["name"];
            $fixtureObj['home'] = $home;
            $fixtureObj['away'] = $away;
            $fixtureObj['file'] = $file;

            /*echo "fixtureId: ${fixtureObj['fixture_id']}" . PHP_EOL;
            echo "competition: ${fixtureObj['competition']}" . PHP_EOL;
            echo "kickoff: ${fixtureObj['kickoff']}" . PHP_EOL;
            echo "ground: ${fixtureObj['ground']}" . PHP_EOL;
            echo "home: ${fixtureObj['home']}" . PHP_EOL;
            echo "away: ${fixtureObj['away']}" . PHP_EOL;
            echo "=================" . PHP_EOL;*/

            $this->saveFixture($fixtureObj);
        }
    }

    /**
     * Save fixture data to db
     *
     * @param  array $fixture
     * @return void
     */
    protected function saveFixture($fixture)
    {
        if (!empty($fixture)) {
            $createdAtDate = new \DateTime;
            $createdAt = $createdAtDate->format('Y-m-d H:i:s');

            DB::table('fixtures_data')->insert(
                [
                    'fixture_id' => trim($fixture['fixture_id']),
                    'competition' => trim($fixture['competition']),
                    'kickoff' => trim($fixture['kickoff']),
                    'ground' => trim($fixture['ground']),
                    'home' => trim($fixture['home']),
                    'away' => trim($fixture['away']),
                    'file' => trim($fixture['file']),
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt
                ]
            );
        }
    }
}
