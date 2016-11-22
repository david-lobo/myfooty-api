<?php

use Illuminate\Database\Seeder;
use App\Models\Team;
use App\Models\Competition;
use App\Models\Match;
use App\Models\Broadcaster;

class MatchBroadcasterTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('match_broadcaster')->delete();

        $matches = Match::all();

        $bt = Broadcaster::where('title_normalised', 'bt_sport')->first();
        $sky = Broadcaster::where('title_normalised', 'sky_sports')->first();

        foreach ($matches as $match) {
            echo $match->kickoff. ' ';
            echo $match->homeTeam->title . ' ';
            echo $match->awayTeam->title . ' ';
            echo $match->competition->title . ' ';

            $match->broadcasters()->attach($sky->id);
        }
    }
}
