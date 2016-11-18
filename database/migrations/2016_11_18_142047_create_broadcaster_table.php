<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBroadcasterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('broadcaster', function (Blueprint $table) {
            $table->increments('id');

            $table->string('title', 250);
            $table->string('title_normalised', 250);

            $table->unique('title_normalised');

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
        Schema::dropIfExists('broadcaster');
        Schema::enableForeignKeyConstraints();
    }
}
