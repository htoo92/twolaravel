<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChangeLimitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('change_limits', function (Blueprint $table) {
            $table->id();
            $table->integer('limit_amount');
            $table->foreignId('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('is_offButton')->default('0');
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
        Schema::dropIfExists('change_limits');
    }
}
