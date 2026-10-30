<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workshops', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->string('session')->nullable();
            $table->string('venue')->nullable();
            $table->unsignedInteger('capacity')->default(60);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now = now();

        DB::table('workshops')->insert([
            [
                'code' => 'RA',
                'name' => 'Regional Anesthesia Workshop (RA)',
                'session' => '9:00 AM – 12:00 Noon (Workshop Session)',
                'venue' => 'Executive 5',
                'capacity' => 60,
                'sort_order' => 10,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'POCUS',
                'name' => 'POCUS',
                'session' => '8:30 AM – 12:00 Noon',
                'venue' => 'Executive 5',
                'capacity' => 60,
                'sort_order' => 20,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'AIRWAY',
                'name' => 'Airway',
                'session' => '1:30 – 4:30 PM',
                'venue' => 'Executive 5',
                'capacity' => 60,
                'sort_order' => 30,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('workshops');
    }
};
