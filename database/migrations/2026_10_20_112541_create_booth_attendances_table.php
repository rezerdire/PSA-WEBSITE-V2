<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('booth_attendances', function (Blueprint $table) {
            $table->id('attendance_id');

            $table->string('member_id_no');
            $table->unsignedBigInteger('booth_id');

            $table->timestamp('visited_at')->useCurrent();

            $table->timestamps();

            $table->foreign('member_id_no')
                ->references('member_id_no')
                ->on('members')
                ->cascadeOnDelete();

            $table->foreign('booth_id')
                ->references('booth_id')
                ->on('booths')
                ->cascadeOnDelete();

            $table->unique(
                ['member_id_no', 'booth_id'],
                'unique_member_booth_attendance'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booth_attendances');
    }
};
