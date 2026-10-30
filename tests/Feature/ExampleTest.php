<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

beforeEach(function (): void {
    Schema::dropIfExists('gallery_images');
    Schema::create('gallery_images', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('gallery_category_id');
        $table->string('path');
        $table->string('thumb_path')->nullable();
        $table->string('large_path')->nullable();
        $table->unsignedInteger('width')->nullable();
        $table->unsignedInteger('height')->nullable();
        $table->unsignedInteger('order')->default(0);
        $table->timestamps();
    });
});

test('the application returns a successful response', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});
