<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBetnumbersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('betnumbers', function (Blueprint $table) {
            $table->id();
            $table->string('number');
            // $table->string('number_type');
            // $table->foreignId('member_id')->references('id')->on('members')->onDelete('cascade');
            $table->foreignId('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->integer('amount');
            $table->integer('over_amount');
            // $table->foreignId('order')->references('id')->on('orders')->onDelete('cascade');
            $table->integer('final_amount')->default(0);
            $table->integer('return_amount')->default(0);
            $table->boolean('is_over')->default(false);
            $table->boolean('to_leader')->default(false);
            $table->boolean('to_supervisor')->default(false);
            $table->integer('accept_amount')->default(0);
            $table->integer('hight_level_accept_amount_supervisor')->default(0);
            $table->integer('off_return_amount')->default(0);
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
        Schema::dropIfExists('betnumbers');
    }
}
