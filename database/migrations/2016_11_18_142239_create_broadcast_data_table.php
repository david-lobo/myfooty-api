<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBroadcastDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('broadcast_data', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('fixture_id');
            $table->string('name', 250)->nullable()->default(null);
            $table->string('image', 250)->nullable()->default(null);
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
        Schema::dropIfExists('broadcast_data');
        Schema::enableForeignKeyConstraints();
    }
}
