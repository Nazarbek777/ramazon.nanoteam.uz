<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Interactions\Schema;
use Illuminate\Support\Facades\Schema as FacadesSchema;

return new class extends Migration
{
    public function up(): void
    {
        FacadesSchema::table('contests', function (Blueprint $table) {
            $table->text('referral_text')->nullable()->after('referral_points');
            $table->string('referral_button_text')->nullable()->after('referral_text');
        });
    }

    public function down(): void
    {
        FacadesSchema::table('contests', function (Blueprint $table) {
            $table->dropColumn(['referral_text', 'referral_button_text']);
        });
    }
};
