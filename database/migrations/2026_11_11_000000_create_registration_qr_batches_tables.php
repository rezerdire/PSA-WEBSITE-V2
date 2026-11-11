<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registration_qr_batches', function (Blueprint $table): void {
            $table->id();
            $table->string('status')->default('queued')->index();
            $table->unsignedInteger('requested_limit');
            $table->unsignedInteger('total_count')->default(0);
            $table->unsignedInteger('processed_count')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->unsignedInteger('skipped_count')->default(0);
            $table->unsignedInteger('not_selected_count')->default(0);
            $table->foreignId('current_registration_id')->nullable()->constrained('registrations')->nullOnDelete();
            $table->string('current_name')->nullable();
            $table->string('current_email')->nullable();
            $table->string('current_status')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('registration_qr_batch_recipients', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('batch_id')->constrained('registration_qr_batches')->cascadeOnDelete();
            $table->foreignId('registration_id')->constrained('registrations')->cascadeOnDelete();
            $table->unsignedInteger('position');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('status')->default('pending')->index();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();

            $table->unique(['batch_id', 'registration_id']);
            $table->index(['registration_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_qr_batch_recipients');
        Schema::dropIfExists('registration_qr_batches');
    }
};
