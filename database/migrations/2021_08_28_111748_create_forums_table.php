<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateForumsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('forums', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('image');
            $table->string('title_ar');
            $table->string('title_en');
            $table->string('desc_ar');
            $table->string('desc_en');
            $table->bigInteger('cat_id')->unsigned();
            $table->foreign('cat_id')->references('id')->on('forum_categories')->onDelete('restrict');
            $table->bigInteger('city_id')->unsigned();
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('restrict');
            $table->enum('status',['show','hide'])->default('show');
            $table->enum('deleted',['0','1'])->default('0');
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
        Schema::dropIfExists('forums');
    }
}
