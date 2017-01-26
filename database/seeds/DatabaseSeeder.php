<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);

        //$this->call(TeamTableSeeder::class);
         Model::unguard();

        DB::table('users')->delete();

        $users = array(
                ['device_id' => '68753A44-4D6F-1226-9C60-0050E4C00064'],
                ['device_id' => '78753A45-4J6F-5226-9C60-5040Y4C02066'],
                ['device_id' => '98753A46-4H6F-6226-9C60-8030H4B04068'],
                ['device_id' => '48753A47-4T6F-4226-9C60-1020G4A03069']
        );

        // Loop through each user above and create the record for them in the database
        foreach ($users as $user)
        {
            User::create($user);
        }

        Model::reguard();
    }
}
