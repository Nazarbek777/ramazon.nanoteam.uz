<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Ball qaysi guruhda qo'yilganini saqlaymiz — bitta guruhdagi ball
 * o'quvchining boshqa guruhidagi natijasiga ta'sir qilmasligi uchun.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parvoz_grades', function (Blueprint $table) {
            $table->foreignId('parvoz_group_id')->nullable()->after('parvoz_student_id')
                ->constrained('parvoz_groups')->nullOnDelete();

            $table->index(['parvoz_group_id', 'parvoz_student_id']);
        });

        // Eski ballarni o'quvchining birinchi guruhiga bog'laymiz
        $pairs = DB::table('parvoz_group_student')
            ->orderBy('id')
            ->get(['parvoz_student_id', 'parvoz_group_id']);

        $firstGroup = [];
        foreach ($pairs as $p) {
            $firstGroup[$p->parvoz_student_id] ??= $p->parvoz_group_id;
        }

        foreach ($firstGroup as $studentId => $groupId) {
            DB::table('parvoz_grades')
                ->where('parvoz_student_id', $studentId)
                ->whereNull('parvoz_group_id')
                ->update(['parvoz_group_id' => $groupId]);
        }
    }

    public function down(): void
    {
        Schema::table('parvoz_grades', function (Blueprint $table) {
            $table->dropForeign(['parvoz_group_id']);
            $table->dropIndex(['parvoz_group_id', 'parvoz_student_id']);
            $table->dropColumn('parvoz_group_id');
        });
    }
};
