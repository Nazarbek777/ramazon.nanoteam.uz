<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * O'quvchi bir nechta guruhga tegishli bo'lishi mumkin —
 * bitta parvoz_group_id ustuni o'rniga bog'lovchi jadval.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parvoz_group_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parvoz_group_id')->constrained('parvoz_groups')->cascadeOnDelete();
            $table->foreignId('parvoz_student_id')->constrained('parvoz_students')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['parvoz_group_id', 'parvoz_student_id']);
        });

        // Mavjud biriktirishlarni ko'chiramiz
        if (Schema::hasColumn('parvoz_students', 'parvoz_group_id')) {
            $rows = DB::table('parvoz_students')
                ->whereNotNull('parvoz_group_id')
                ->get(['id', 'parvoz_group_id']);

            $now = now();
            $insert = [];

            foreach ($rows as $row) {
                $insert[] = [
                    'parvoz_group_id'   => $row->parvoz_group_id,
                    'parvoz_student_id' => $row->id,
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ];
            }

            if ($insert) {
                DB::table('parvoz_group_student')->insert($insert);
            }

            Schema::table('parvoz_students', function (Blueprint $table) {
                $table->dropForeign(['parvoz_group_id']);
                $table->dropColumn('parvoz_group_id');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('parvoz_students', 'parvoz_group_id')) {
            Schema::table('parvoz_students', function (Blueprint $table) {
                $table->foreignId('parvoz_group_id')->nullable()->after('id')
                    ->constrained('parvoz_groups')->nullOnDelete();
            });

            // Har bir o'quvchining birinchi guruhini qaytaramiz
            $pairs = DB::table('parvoz_group_student')->orderBy('id')->get();
            $seen = [];

            foreach ($pairs as $p) {
                if (isset($seen[$p->parvoz_student_id])) continue;
                $seen[$p->parvoz_student_id] = true;

                DB::table('parvoz_students')->where('id', $p->parvoz_student_id)
                    ->update(['parvoz_group_id' => $p->parvoz_group_id]);
            }
        }

        Schema::dropIfExists('parvoz_group_student');
    }
};
