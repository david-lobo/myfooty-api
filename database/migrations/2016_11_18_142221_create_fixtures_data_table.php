<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFixturesDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fixtures_data', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('fixture_id')->nullable()->default(null);
            $table->string('competition', 250)->nullable()->default(null);
            $table->string('kickoff', 250)->nullable()->default(null);
            $table->string('ground', 250)->nullable()->default(null);
            $table->string('home', 250)->nullable()->default(null);
            $table->string('away', 250)->nullable()->default(null);
            $table->string('file', 250)->nullable()->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('fixtures_data');
        Schema::enableForeignKeyConstraints();
    }
}
