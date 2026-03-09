<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('live_streams', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('video_url')->nullable();
            $blueprint->string('stream_key')->nullable();
            $blueprint->boolean('is_active')->default(false);
            $blueprint->integer('pid')->nullable();
            $blueprint->timestamps();
        });

        // Insert a default record
        \Illuminate\Support\Facades\DB::table('live_streams')->insert([
            'video_url' => 'public/video/live.mp4',
            'stream_key' => '',
            'is_active' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('live_streams');
    }
};
