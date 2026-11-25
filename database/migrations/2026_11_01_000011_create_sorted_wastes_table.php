<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sorted_wastes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('sorted_date');
            $table->decimal('organic_weight', 8, 2);
            $table->decimal('inorganic_weight', 8, 2);
            $table->decimal('residue_weight', 8, 2);
            $table->timestamps();

            $table->unique(['classroom_id', 'sorted_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sorted_wastes');
    }
};
