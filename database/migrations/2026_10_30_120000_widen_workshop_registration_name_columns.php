<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workshop_registrations', function (Blueprint $table): void {
            // Keep deployed databases aligned with the application's max:255 validation.
            $table->string('mem_last_name', 255)->change();
            $table->string('mem_first_name', 255)->change();
            $table->string('mem_middle_name', 255)->change();
        });
    }

    public function down(): void
    {
        // Do not shrink these columns: deployed databases may already contain names
        // that would be truncated by an older schema.
    }
};
