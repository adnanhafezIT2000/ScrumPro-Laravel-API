<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')
                   ->references('id')
                   ->on('users')
                   ->onDelete('cascade')
                   ->onUpdate('cascade');

            $table->unsignedBigInteger('story_id')->nullable();
            $table->foreign('story_id')
                    ->references('id')
                    ->on('stories')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');

            $table->unsignedBigInteger('category_id')->nullable();
            $table->foreign('category_id')
                    ->references('id')
                    ->on('categories')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');

            $table->unsignedBigInteger('skill_id')->nullable();
            $table->foreign('skill_id')
                    ->references('id')
                    ->on('skills')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');

            $table->string('title');
            $table->longText('description');
            $table->char('status' , 30)->default('to do');
            $table->char('priority' , 30);
            $table->tinyInteger('level');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
