<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parvoz_teachers', function (Blueprint $table) {
            $table->string('access_code', 10)->nullable()->unique()->after('telegram_id');
        });

        // Mavjud o'qituvchilarga kod berish
        $ids = DB::table('parvoz_teachers')->whereNull('access_code')->pluck('id');
        foreach ($ids as $id) {
            do {
                $code = (string) random_int(100000, 999999);
            } while (DB::table('parvoz_teachers')->where('access_code', $code)->exists());

            DB::table('parvoz_teachers')->where('id', $id)->update(['access_code' => $code]);
        }
    }

    public function down(): void
    {
        Schema::table('parvoz_teachers', function (Blueprint $table) {
            $table->dropColumn('access_code');
        });
    }
};
