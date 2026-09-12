<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Bot (Contest moduli uslubida — token + webhook)
        Schema::create('parvoz_bots', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->nullable();
            $table->string('token');
            $table->string('webhook_secret')->nullable();
            $table->boolean('webhook_set')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Guruhlar
        Schema::create('parvoz_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Fanlar
        Schema::create('parvoz_subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // O'qituvchilar
        Schema::create('parvoz_teachers', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('phone')->nullable()->index();
            $table->string('telegram_id')->nullable()->index();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // O'qituvchi ↔ guruh (bir o'qituvchi bir nechta guruhga)
        Schema::create('parvoz_group_teacher', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parvoz_group_id')->constrained('parvoz_groups')->cascadeOnDelete();
            $table->foreignId('parvoz_teacher_id')->constrained('parvoz_teachers')->cascadeOnDelete();
            $table->unique(['parvoz_group_id', 'parvoz_teacher_id'], 'parvoz_group_teacher_unique');
        });

        // O'quvchilar
        Schema::create('parvoz_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parvoz_group_id')->nullable()->constrained('parvoz_groups')->nullOnDelete();
            $table->string('full_name');
            $table->string('phone')->nullable()->index();
            $table->string('telegram_id')->nullable()->index();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Baholar (ball — o'qituvchi xohlagan sonni kiritadi)
        Schema::create('parvoz_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parvoz_student_id')->constrained('parvoz_students')->cascadeOnDelete();
            $table->foreignId('parvoz_teacher_id')->nullable()->constrained('parvoz_teachers')->nullOnDelete();
            $table->foreignId('parvoz_subject_id')->nullable()->constrained('parvoz_subjects')->nullOnDelete();
            $table->decimal('score', 8, 2);
            $table->decimal('max_score', 8, 2)->nullable();
            $table->string('comment')->nullable();
            $table->timestamp('graded_at')->nullable();
            $table->timestamps();
            $table->index(['parvoz_student_id', 'graded_at']);
        });

        // Bot dialog holati (ball kiritish sehrgari uchun)
        Schema::create('parvoz_states', function (Blueprint $table) {
            $table->id();
            $table->string('telegram_id')->unique();
            $table->string('state')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parvoz_states');
        Schema::dropIfExists('parvoz_grades');
        Schema::dropIfExists('parvoz_students');
        Schema::dropIfExists('parvoz_group_teacher');
        Schema::dropIfExists('parvoz_teachers');
        Schema::dropIfExists('parvoz_subjects');
        Schema::dropIfExists('parvoz_groups');
        Schema::dropIfExists('parvoz_bots');
    }
};
