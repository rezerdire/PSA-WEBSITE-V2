<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unregistered_psa_send_batches', function (Blueprint $table): void {
            $table->id();
            $table->string('status')->default('queued')->index();
            $table->unsignedInteger('requested_limit');
            $table->unsignedInteger('total_count')->default(0);
            $table->unsignedInteger('processed_count')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->foreignId('current_recipient_id')->nullable()->constrained('unregistered_psa_recipients')->nullOnDelete();
            $table->string('current_psa_id')->nullable();
            $table->string('current_name')->nullable();
            $table->string('current_email')->nullable();
            $table->string('current_status')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('unregistered_psa_send_batch_recipients', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('batch_id')->constrained('unregistered_psa_send_batches')->cascadeOnDelete();
            $table->foreignId('recipient_id')->nullable()->constrained('unregistered_psa_recipients')->nullOnDelete();
            $table->unsignedInteger('position');
            $table->string('psa_id');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('chapter')->nullable();
            $table->string('status')->default('pending')->index();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();

            $table->unique(['batch_id', 'recipient_id']);
            $table->index(['batch_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unregistered_psa_send_batch_recipients');
        Schema::dropIfExists('unregistered_psa_send_batches');
    }
};
