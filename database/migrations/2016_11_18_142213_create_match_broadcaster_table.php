<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMatchBroadcasterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('match_broadcaster', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('match_id');
            $table->unsignedInteger('broadcaster_id');
            $table->timestamps();

            $table->unique(['match_id', 'broadcaster_id']);
            $table->index('match_id', 'match_idx');
            $table->index('broadcaster_id');

            $table->foreign('broadcaster_id')
                  ->references('id')->on('broadcaster')
                  ->onDelete('cascade');

            $table->foreign('match_id')
                  ->references('id')->on('match')
                  ->onDelete('cascade');
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
        Schema::dropIfExists('match_broadcaster');
        Schema::enableForeignKeyConstraints();
    }
}
