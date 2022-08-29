<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNumbersCollectionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('number_types', function (Blueprint $table) {
            $table->id();
            $table->string('number_types');
            $table->timestamps();
        });
        Schema::create('numbers', function (Blueprint $table) {
            $table->id();
            $table->string('number');
            $table->foreignId('number_type_id')->references('id')->on('number_types')->onDelete('cascade');
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
        Schema::dropIfExists('numbers_collection');
    }
}
