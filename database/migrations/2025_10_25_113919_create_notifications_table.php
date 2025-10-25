<?php

// database/migrations/2024_01_01_000008_create_notifications_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('message');
            $table->enum('type', ['late_arrival', 'leave_request', 'leave_approved', 'leave_rejected', 'general']);
            $table->boolean('is_read')->default(false);
            $table->unsignedBigInteger('related_id')->nullable()->comment('ID of related record');
            $table->timestamps();
            
            $table->index(['user_id', 'is_read']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
