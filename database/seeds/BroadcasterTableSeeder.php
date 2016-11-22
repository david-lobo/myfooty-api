<?php

use Illuminate\Database\Seeder;
use App\Models\Broadcaster;

class BroadcasterTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('broadcaster')->delete();

        $sky = Broadcaster::create(array(
            'title' => 'UK - SKY SPORTS',
            'title_normalised' => 'sky_sports' 
        ));

        $bt = Broadcaster::create(array(
            'title' => 'UK - BT SPORT',
            'title_normalised' => 'bt_sport' 
        ));
    }
}
