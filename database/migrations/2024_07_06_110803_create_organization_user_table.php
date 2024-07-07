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
       // Ensure the userId column in users table is of type string
       Schema::table('users', function (Blueprint $table) {
        $table->string('userId')->change();
    });

    Schema::create('organization_user', function (Blueprint $table) {
        $table->id();
        $table->string('user_id');
        $table->string('organization_id');
        $table->timestamps();

        $table->foreign('user_id')->references('userId')->on('users')->onDelete('cascade');
        $table->foreign('organization_id')->references('orgId')->on('organizations')->onDelete('cascade');

        // Add indexes for better performance
        $table->index('user_id');
        $table->index('organization_id');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_user');
    }
};
