<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_section_id')->constrained()->cascadeOnDelete();
            $table->date('session_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('topic')->nullable();
            $table->string('status', 20)->default('scheduled')->index();
            $table->unsignedTinyInteger('late_after_minutes')->default(15);

            // Rotating self check-in credentials, only populated while open.
            $table->string('qr_token', 64)->nullable()->unique();
            $table->timestamp('qr_expires_at')->nullable();
            $table->string('checkin_code', 6)->nullable()->index();

            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // A section cannot meet twice at the same moment.
            $table->unique(['class_section_id', 'session_date', 'start_time'], 'sessions_section_date_time_unique');
            $table->index(['session_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_sessions');
    }
};
