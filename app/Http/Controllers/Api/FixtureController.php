<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\Competition;
use App\Models\Match;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;

class FixtureController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->has('club')) {
            // request is filtered by club
            return $this->fixturesForTeam($request);
        } else {

            return $this->fixturesForAllTeams($request);
            // request is for all clubs
        }
    }

        /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function data(Request $request)
    {
        return $this->fixturesDataForAllTeams($request);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function testdata(Request $request)
    {
        //sleep(5);
        return $this->fixturesTestDataForAllTeams($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $team = Team::find($id);

        if ($team) {
            return response()->json([
                'status' => 'success',
                'data' => ['club' => $team]
            ]);
        }

        return response()->json(
            [
                'code' => 404,
                'message' => 'Record not found',
                'description' => 'Team not found with that id'
            ],
            404
        );
    }

    public function fixturesForAllTeams(Request $request)
    {
        $perPage = 1000;
        $page = $request->input('page', 1);
        $offset = ($page - 1) * $perPage;

        if (is_null($page) || $page <= 0) {
            return $this->errorResponse(
                404,
                'Page not valid',
                'Please give a valid page'
            );
        }

        //$cacheTag = "fixtures_all_{$page}_{$offset}_{$perPage}";

        // Get lsit of matchday/competition dates
        $matchDays = Match::select(DB::raw('date(kickoff) as kickoff_date_iso'))
            ->addSelect('competition.title as competition_title')
            ->addSelect('competition.id as competition_id')
            ->leftJoin('competition', 'match.competition_id', '=', 'competition.id')
            ->groupBy(DB::raw('kickoff_date_iso'), 'match.competition_id')
            ->orderBy('kickoff_date_iso', 'asc')
            ->get();

        // Slice the array for pagination
        $total = $matchDays->count();
        $matchDaysPaged = $matchDays->slice($offset, $perPage);

        $allFixtures = collect([]);

        $count = 0;
        foreach ($matchDaysPaged as $matchDay) {
            if ($count > 100000) {
                continue;
            }
            $count++;

            $matchDayFixtures = collect([
                'kickoff_date' => $matchDay->kickoff_date_iso,
                'competition_id' => $matchDay->competition_id,
                'competition_title' => $matchDay->competition_title
            ]);

            // Get the actual fixtures for a matchday/competition
            $matches = Match::where('competition_id', '=', $matchDay->competition_id)
            ->with('homeTeam', 'awayTeam', 'broadcasters', 'competition')
            ->where(DB::raw('date(kickoff)'), '=', $matchDay->kickoff_date_iso)
            ->orderBy('kickoff', 'asc')
            ->get();

            // add the matches data to the match day object
            $matchDayFixtures->put('fixtures_total', $matches->count());
            $matchDayFixtures->put('fixtures', $matches);

            $allFixtures[] = $matchDayFixtures;
        };

        $paginatorOptions = [
            'path' => url()->current()
        ];

        // Create a paginator to nicely render the page links
        $paginator = new LengthAwarePaginator(
            $allFixtures,
            $total,
            $perPage,
            $page,
            $paginatorOptions
        );

        // Convert to array to add additional values
        $data = $paginator->toArray();
        $nextPage = null;

        if (isset($data['next_page_url'])  && !empty($data['next_page_url'])) {

            $nextPageUrl = $data['next_page_url'];
            $nextPageQueryParams = parse_url($nextPageUrl);

            if ($nextPageQueryParams && isset($nextPageQueryParams["query"])) {
                $nextPageQuery = $nextPageQueryParams["query"];
                parse_str($nextPageQuery, $output);
                $nextPage = $output['page'];
            }

            $data['next_page'] = $nextPage;
        }

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    public function fixturesForTeam(Request $request)
    {
        //sleep(5);
        //sleep(5);
        $teamAlias = null;
        $perPage = 1000;
        $allowedQueryParams = ['club'];

        $params = $request->all();
        $teamAlias = $request->input('club');
        $page = intval($request->input('page'));

        $team = Team::where('title_normalised', $teamAlias)->first();

        if (!$team) {
            return $this->errorResponse(
                404,
                'Record not found',
                'Team not found with that id'
            );
        }

        if (is_null($page) || $page <= 0) {
            return $this->errorResponse(
                404,
                'Page not valid',
                'Please give a valid page'
            );
        }

        $matches = Match::where('home_id', '=', $team->id)
            ->with('homeTeam', 'awayTeam', 'broadcasters', 'competition')
            ->orWhere('away_id', '=', $team->id)
            ->orderBy('kickoff', 'asc')
            ->simplePaginate($perPage);

        $path = http_build_query($params);

        $params = array_intersect_key($params, array_flip($allowedQueryParams));
        $matches->appends($params);

        // Convert to array to add additional values
        $data = $matches->toArray();
        $data['next_page'] = null;

        if (isset($data['next_page_url'])  && !empty($data['next_page_url'])) {
            $nextPageUrl = $data['next_page_url'];
            $nextPageQueryParams = parse_url($nextPageUrl);

            if ($nextPageQueryParams && isset($nextPageQueryParams["query"])) {
                $nextPageQuery = $nextPageQueryParams["query"];
                parse_str($nextPageQuery, $output);
                $nextPage = $output['page'];
            }
            $data['next_page'] = $nextPage;
        }

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    public function fixturesDataForAllTeams(Request $request)
    {
        //sleep(5);
        //sleep(5);
        $perPage = 1000;
        $allowedQueryParams = ['club'];

        $params = $request->all();
        $page = intval($request->input('page'));

        if (is_null($page) || $page <= 0) {
            return $this->errorResponse(
                404,
                'Page not valid',
                'Please give a valid page'
            );
        }

        $matches = Match::where('id', '>', '1')
            ->with('homeTeam', 'awayTeam', 'broadcasters', 'competition')
            //->orWhere('away_id', '=', $team->id)
            ->orderBy('kickoff', 'asc')
            ->simplePaginate($perPage);

        $path = http_build_query($params);

        $params = array_intersect_key($params, array_flip($allowedQueryParams));
        $matches->appends($params);

        // Convert to array to add additional values
        $data = $matches->toArray();
        $data['next_page'] = null;

        if (isset($data['next_page_url'])  && !empty($data['next_page_url'])) {
            $nextPageUrl = $data['next_page_url'];
            $nextPageQueryParams = parse_url($nextPageUrl);

            if ($nextPageQueryParams && isset($nextPageQueryParams["query"])) {
                $nextPageQuery = $nextPageQueryParams["query"];
                parse_str($nextPageQuery, $output);
                $nextPage = $output['page'];
            }
            $data['next_page'] = $nextPage;
        }

        $lastUpdated = new \DateTime;
        $data['data_last_updated'] = $lastUpdated->format('Y-m-d H:i:s');

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    public function fixturesTestDataForAllTeams(Request $request)
    {
        $testHomeTeams = ['Hull', 'Chelsea', 'Wolves', 'Watford'];
        $testAwayTeams = ['Spurs', 'Arsenal', 'Liverpool', 'Swansea'];

        shuffle($testHomeTeams);
        shuffle($testAwayTeams);

        $kickoffDate = new \DateTime();
        $data = [
            'data' => [[
                "id" => 991919,
                "home_id" => 21,
                "away_id" => 22,
                "kickoff" => $kickoffDate->format('Y-m-d H:i:s'),
                "competition_id" => 2,
                "broadcasters_flat" => "",
                "kickoff_date" => "Wed 7th Dec",
                "kickoff_time" => "19:45",
                "home_team" => [
                    "id" => 21,
                    "title" => $testHomeTeams[0],
                    "image" => null,
                    "premier_league" => 0,
                    "background_color" => null,
                    "text_color" => null,
                    "title_normalised" => $testHomeTeams[0]
                ],

                "away_team" => [
                    "id" => 22,
                    "title" => $testAwayTeams[0],
                    "image" => null,
                    "premier_league" => 0,
                    "background_color" => null,
                    "text_color" => null,
                    "title_normalised" => $testAwayTeams[0]
                ]
            ]]
        ];

        $lastUpdated = new \DateTime;
        $data['data_last_updated'] = $lastUpdated->format('Y-m-d H:i:s');

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    public function fixturesAsArray($matches)
    {
        $fixtures = [];

        foreach ($matches as $match) {
            $fixture = [];

            $kickoffDate = new \DateTime($match->kickoff);

            $fixture['home']['team']['id'] = $match->homeTeam->id;
            $fixture['home']['team']['title'] = $match->homeTeam->title;
            $fixture['away']['team']['id'] = $match->awayTeam->id;
            $fixture['away']['team']['title'] = $match->awayTeam->title;

            $fixture['competition']['id'] = $match->competition->id;
            $fixture['competition']['title'] = $match->competition->title;

            $fixture['kickoff']['time'] = $kickoffDate->format('H:i');
            $fixture['kickoff']['date'] = $kickoffDate->format('D jS M');

            $broadcastersConcat = '';

            foreach ($match->broadcasters as $broadcaster) {
                $broadcasterItem = [];
                $broadcasterItem['id'] = $broadcaster->id;
                $broadcasterItem['title'] = $broadcaster->title;
                $fixture['broadcasters'][] = $broadcasterItem;

                $separator = $broadcastersConcat == '' ? '' : ', ';
                $broadcastersConcat .= $separator . $broadcaster->title ;
            }

            if (!empty($broadcastersConcat)) {
                $fixture['broadcasters_concat'] = $broadcastersConcat;
            }

            $fixtures[] = $fixture;
        }
        return $fixtures;
    }
}
