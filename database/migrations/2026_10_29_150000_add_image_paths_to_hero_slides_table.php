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
            $table->json('image_paths')->nullable()->after('image_path');
        });

        DB::table('hero_slides')
            ->select(['id', 'image_path'])
            ->orderBy('id')
            ->get()
            ->each(function (object $slide): void {
                $paths = filled($slide->image_path) ? [$slide->image_path] : [];

                DB::table('hero_slides')
                    ->where('id', $slide->id)
                    ->update(['image_paths' => json_encode($paths, JSON_THROW_ON_ERROR)]);
            });
    }

    public function down(): void
    {
        Schema::table('hero_slides', function (Blueprint $table): void {
            $table->dropColumn('image_paths');
        });
    }
};
