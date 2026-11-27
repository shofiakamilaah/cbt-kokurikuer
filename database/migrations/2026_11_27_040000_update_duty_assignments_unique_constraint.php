<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambahkan index terpisah pada user_id agar foreign key tidak memblokir dropUnique
        Schema::table('duty_assignments', function (Blueprint $table) {
            $table->index('user_id', 'duty_assignments_user_id_idx');
        });

        // 2. Drop unique constraint lama (user_id, assignment_date)
        Schema::table('duty_assignments', function (Blueprint $table) {
            $table->dropUnique('duty_assignments_user_id_assignment_date_unique');
            $table->unique(['user_id', 'assignment_date', 'task_type']);
        });
    }

    public function down(): void
    {
        Schema::table('duty_assignments', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'assignment_date', 'task_type']);
            $table->unique(['user_id', 'assignment_date']);
            $table->dropIndex('duty_assignments_user_id_idx');
        });
    }
};
