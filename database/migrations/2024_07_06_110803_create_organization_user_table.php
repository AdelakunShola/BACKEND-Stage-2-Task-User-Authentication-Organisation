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
        $table->uuid('user_id');
        $table->uuid('organization_id');
        $table->foreign('user_id')->references('userId')->on('users')->onDelete('cascade');
        $table->foreign('organization_id')->references('orgId')->on('organizations')->onDelete('cascade');
        $table->timestamps();
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
