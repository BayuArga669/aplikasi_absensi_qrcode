<?php
// database/migrations/2024_01_01_000001_create_users_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', ['admin', 'superior', 'employee'])->default('employee');
            $table->string('employee_id', 50)->unique()->nullable();
            $table->string('position', 100)->nullable();
            $table->string('department', 100)->nullable();
            $table->foreignId('superior_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('phone', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
            
            $table->index(['role', 'is_active']);
            $table->index('superior_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
