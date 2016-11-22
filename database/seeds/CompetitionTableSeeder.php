<?php

use Illuminate\Database\Seeder;
use App\Models\Competition;

class CompetitionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('competition')->delete();

        $pl = Competition::create(array(
            'title' => 'Premier League',
            'priority' => 1,
            'title_normalised' => 'premier_league' 
        ));

       $ucl = Competition::create(array(
            'title' => 'Champions League',
            'priority' => 1,
            'title_normalised' => 'champions_league' 
        ));
    }
}
