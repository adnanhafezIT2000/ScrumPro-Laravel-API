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
        Schema::create('dependences', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('task_id')->nullable();
            $table->foreign('task_id')
                   ->references('id')
                   ->on('tasks')
                   ->onDelete('cascade')
                   ->onUpdate('cascade');

            $table->unsignedBigInteger('blocking_by')->nullable();
            $table->foreign('blocking_by')
                    ->references('id')
                    ->on('tasks')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dependences');
    }
};
