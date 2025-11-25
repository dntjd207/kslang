<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('words', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('word_korean');
            $table->string('word_english')->nullable();
            $table->integer('level')->default(1);
            $table->string('meaning')->nullable();
            $table->text('etymology')->nullable();
            $table->string('example_kr')->nullable();
            $table->string('example_en')->nullable();
            $table->string('audio_filename')->nullable();
            $table->text('tags')->nullable();
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
        Schema::dropIfExists('words');
    }
}
