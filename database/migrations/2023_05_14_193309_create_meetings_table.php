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
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('project_id')->nullable();
            $table->foreign('project_id')
                   ->references('id')
                   ->on('projects')
                   ->onDelete('cascade')
                   ->onUpdate('cascade');

            $table->unsignedBigInteger('sprint_id')->nullable();
            $table->foreign('sprint_id')
                  ->references('id')
                  ->on('sprints')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->char('type' , 30);
            $table->longText('description');
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meetings');
    }
};
