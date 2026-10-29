<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hero_slides', function (Blueprint $table): void {
            $table->string('image_path')->nullable()->change();
            $table->string('image_alt')->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('hero_slides')
            ->whereNull('image_path')
            ->update(['image_path' => '']);

        DB::table('hero_slides')
            ->whereNull('image_alt')
            ->update(['image_alt' => '']);

        Schema::table('hero_slides', function (Blueprint $table): void {
            $table->string('image_path')->nullable(false)->change();
            $table->string('image_alt')->nullable(false)->change();
        });
    }
};
