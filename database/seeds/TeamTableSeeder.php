<?php

use Illuminate\Database\Seeder;
use App\Models\Team;

class TeamTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	DB::table('team')->delete();

        $arsenal = Team::create(array(
            'title' => 'Arsenal',
            'alias' => 'Arsenal',
            'image' => 'arsenal',
            'premier_league' => 1,
            'title_normalised' => 'arsenal' 
        ));

        $burnley = Team::create(array(
            'title' => 'Burnley',
            'alias' => 'Burnley',
            'image' => 'burnley',
            'premier_league' => 1,
            'title_normalised' => 'burnley' 
        ));

        $chelsea = Team::create(array(
            'title' => 'Chelsea',
            'alias' => 'Chelsea',
            'image' => 'chelsea',
            'premier_league' => 1,
            'title_normalised' => 'chelsea' 
        ));

        $mancity = Team::create(array(
            'title' => 'Manchester City',
            'alias' => 'Man City',
            'image' => 'mancity',
            'premier_league' => 1,
            'title_normalised' => 'manchester_city' 
        ));

        $this->command->info('The Teams are added');
    }
}
