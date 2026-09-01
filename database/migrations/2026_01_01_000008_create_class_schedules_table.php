<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_section_id')->constrained()->cascadeOnDelete();
            // ISO-8601 day numbering: 1 = Monday ... 7 = Sunday.
            $table->unsignedTinyInteger('day_of_week');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('room', 50)->nullable();
            $table->timestamps();

            $table->unique(['class_section_id', 'day_of_week', 'start_time'], 'schedules_section_day_time_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_schedules');
    }
};
