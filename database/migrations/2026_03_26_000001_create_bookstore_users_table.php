<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookstore_users', function (Blueprint $text) {
            $text->id();
            $text->string('name');
            $text->string('email')->unique();
            $text->timestamp('email_verified_at')->nullable();
            $text->string('password');
            $text->rememberToken();
            $text->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookstore_users');
    }
};
