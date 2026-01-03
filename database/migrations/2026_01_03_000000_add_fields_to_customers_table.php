<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // No additional fields needed - customers table already has all required columns
    }

    public function down(): void
    {
        // No action needed
    }
};
