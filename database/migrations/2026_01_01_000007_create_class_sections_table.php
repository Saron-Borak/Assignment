<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lecturer_id')->constrained()->restrictOnDelete();
            $table->string('section_code', 10);
            $table->string('room', 50)->nullable();
            $table->unsignedSmallInteger('capacity')->default(40);
            $table->timestamps();

            // One section letter per course per semester.
            $table->unique(['course_id', 'semester_id', 'section_code'], 'sections_course_semester_code_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_sections');
    }
};
