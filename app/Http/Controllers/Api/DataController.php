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

class DataController extends Controller
{
    public function fixtures(Request $request)
    {
        //sleep(3);
        $matches = Match::where('id', '>', '1')
            ->with('homeTeam', 'awayTeam', 'broadcasters', 'competition')
            //->orWhere('away_id', '=', $team->id)
            ->orderBy('kickoff', 'asc')
            ->limit(1000)
            ->get();

        //$data = $matches->toArray();
        //$lastUpdated = new \DateTime;
        //$data['data_last_updated'] = $lastUpdated->format('Y-m-d H:i:s');
        $teams = Team::orderBy('title', 'asc')->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'fixtures' => $matches,
                'teams' => $teams
            ]
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
