<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Name of the file
            $table->unsignedBigInteger('size'); // Size of the file in bytes
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Relationship to User model
            $table->timestamps(); // created_at and updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};