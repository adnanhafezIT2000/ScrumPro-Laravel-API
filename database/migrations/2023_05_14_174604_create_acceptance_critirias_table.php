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
        Schema::create('acceptance_critirias', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('story_id')->nullable();
            $table->foreign('story_id')
                  ->references('id')
                  ->on('stories')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->longText('description');
            
            $table->timestamps();
         });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acceptance_critirias');
    }
};
