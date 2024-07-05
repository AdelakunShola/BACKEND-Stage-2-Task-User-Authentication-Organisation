<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->string('userId')->primary()->unique(); // Unique user ID as a string
            $table->string('firstName')->nullable(false);
            $table->string('lastName')->nullable(false); 
            $table->string('email')->unique()->nullable(false); 
            $table->string('password')->nullable(false);
            $table->string('phone')->nullable(); 
            $table->rememberToken(); // Remember token for authentication
            $table->timestamps(); // Timestamps for created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
