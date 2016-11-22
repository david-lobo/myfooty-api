<?php

use Illuminate\Database\Seeder;
use App\Models\Team;
use App\Models\Competition;
use App\Models\Match;

class MatchTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	DB::table('match')->delete();

        $arsenal = Team::where('title_normalised', 'arsenal')->first();
        $burnley = Team::where('title_normalised', 'burnley')->first();
        $competition = Competition::where('title_normalised', 'premier_league')->first();

        $match = Match::create(array(
            'home_id' => $arsenal->id,
            'away_id' => $burnley->id,
            'competition_id' => $competition->id,
            'kickoff' => '2016-11-07 15:07:09' 
        ));

        $chelsea = Team::where('title_normalised', 'chelsea')->first();
        $mancity = Team::where('title_normalised', 'manchester_city')->first();
        $competition = Competition::where('title_normalised', 'premier_league')->first();

        $match = Match::create(array(
            'home_id' => $chelsea->id,
            'away_id' => $mancity->id,
            'competition_id' => $competition->id,
            'kickoff' => '2016-11-08 12:00:00' 
        ));

        $this->command->info('The Matches are added');
    }
}
