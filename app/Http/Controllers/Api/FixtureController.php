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
        $perPage = 10;
        $page = $request->input('page', 1);
        $offset = ($page - 1) * $perPage;

        //$cacheTag = "fixtures_all_{$page}_{$offset}_{$perPage}";

        // Get lsit of matchday/competition dates
        $matchDays = Match::select(DB::raw('date(kickoff) as kickoff_date'))
            ->addSelect(DB::raw('date(kickoff) as kickoff_date'))
            ->addSelect('competition.title as competition_title')
            ->addSelect('competition.id as competition_id')
            ->leftJoin('competition', 'match.competition_id', '=', 'competition.id')
            ->groupBy(DB::raw('kickoff_date'), 'match.competition_id')
            ->orderBy('kickoff_date', 'asc')
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
                'kickoff_date' => $matchDay->kickoff_date,
                'competition_id' => $matchDay->competition_id,
                'competition_title' => $matchDay->competition_title
            ]);

            // Get the actual fixtures for a matchday/competition
            $matches = Match::where('competition_id', '=', $matchDay->competition_id)
            ->with('homeTeam', 'awayTeam', 'broadcasters', 'competition')
            ->where(DB::raw('date(kickoff)'), '=', $matchDay->kickoff_date)
            ->orderBy('kickoff', 'asc')
            ->get();

            // add the matches data to the match day object
            $matchDayFixtures->put('match_total', $matches->count());
            $matchDayFixtures->put('matches', $matches);

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

        return response()->json([
            'status' => 'success',
            'data' => ['fixtures' => $paginator]
        ]);
    }

    public function fixturesForTeam(Request $request)
    {
        $teamAlias = null;
        $perPage = 10;
        $allowedQueryParams = ['club'];

        $params = $request->all();
        $teamAlias = $request->input('club');

        $team = Team::where('title_normalised', $teamAlias)->first();

        if (!$team) {
            return $this->errorResponse(
                404,
                'Record not found',
                'Team not found with that id'
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

        return response()->json([
            'status' => 'success',
            'fixtures' => $matches
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
