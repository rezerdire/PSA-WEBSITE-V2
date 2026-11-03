<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hero_slides', function (Blueprint $table): void {
            $table->id();
            $table->string('eyebrow')->nullable();
            $table->string('title');
            $table->string('highlight')->nullable();
            $table->text('description')->nullable();
            $table->json('details')->nullable();
            $table->string('image_path');
            $table->string('image_alt');
            $table->string('primary_label')->nullable();
            $table->string('primary_url', 2048)->nullable();
            $table->string('secondary_label')->nullable();
            $table->string('secondary_url', 2048)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        $slides = [
            [
                'eyebrow' => 'Upcoming Event',
                'title' => 'PSA 58th',
                'highlight' => 'Annual Convention',
                'description' => 'Beyond the Operating Room: Innovation, Safety, Excellence.',
                'details' => [
                    ['label' => 'Location', 'value' => 'Marriott Grand Ballroom, Pasay City'],
                    ['label' => 'Date', 'value' => 'November 25–27, 2026'],
                ],
                'source' => 'annual-convention/annualposter.png',
                'image_path' => 'hero-slides/annual-convention.png',
                'image_alt' => 'PSA 58th Annual Convention poster',
                'primary_label' => 'Check the Registration Rates',
                'primary_url' => '/CME/Annual-Convention/Poster',
                'secondary_label' => null,
                'secondary_url' => null,
                'sort_order' => 1,
            ],
            [
                'eyebrow' => 'Philippines Anesthesia Crisis Competition 2026',
                'title' => 'Anesthesia',
                'highlight' => 'Sim Wars Trilogy',
                'description' => 'Get ready for an exciting battle of knowledge, skills, and teamwork as the PSA Sim Wars Trilogy Competition brings the community together.',
                'details' => [
                    ['label' => 'Date', 'value' => 'November 27, 2026'],
                    ['label' => 'Venue', 'value' => 'Marriott Ballroom, Newport, Pasay City'],
                ],
                'source' => 'images/simwars/simwars_oct_2026.png',
                'image_path' => 'hero-slides/simwars.png',
                'image_alt' => 'PSA Sim Wars Trilogy Competition poster',
                'primary_label' => null,
                'primary_url' => null,
                'secondary_label' => null,
                'secondary_url' => null,
                'sort_order' => 2,
            ],
            [
                'eyebrow' => 'Share Your Discoveries',
                'title' => 'Research',
                'highlight' => 'Forum 2026',
                'description' => 'Share your discoveries, exchange ideas, and showcase your work to the anesthesia community.',
                'details' => [
                    ['label' => 'Submission deadline', 'value' => 'August 20, 2026'],
                ],
                'source' => 'images/researchforum2026.jpg',
                'image_path' => 'hero-slides/research-forum.jpg',
                'image_alt' => 'PSA Research Forum 2026 call for abstracts',
                'primary_label' => 'Submit Your Abstract',
                'primary_url' => 'https://compose.mail.yahoo.com/?to=psaresearchforum@gmail.com',
                'secondary_label' => 'Download Guidelines',
                'secondary_url' => '/Research_Forum_Guidelines.pdf',
                'sort_order' => 3,
            ],
            [
                'eyebrow' => 'Philippine Society of Anesthesiologists',
                'title' => 'Philippine Society',
                'highlight' => 'of Anesthesiologists',
                'description' => 'Promoting safe and quality anesthesia care across the nation through excellence, education, innovation, and patient safety.',
                'details' => [
                    ['label' => 'Years of service', 'value' => '70+'],
                    ['label' => 'Members nationwide', 'value' => '6,000+'],
                    ['label' => 'Regional chapters', 'value' => '32'],
                ],
                'source' => 'images/stethoscopebg.png',
                'image_path' => 'hero-slides/psa-main.png',
                'image_alt' => 'Medical illustration representing the Philippine Society of Anesthesiologists',
                'primary_label' => 'Explore Our Events',
                'primary_url' => '#recent-events',
                'secondary_label' => 'Download Membership Form',
                'secondary_url' => '/Membership%20Form.pdf',
                'sort_order' => 4,
            ],
        ];

        $now = now();

        foreach ($slides as $slide) {
            $source = public_path($slide['source']);
            $destination = public_path('uploads/' . $slide['image_path']);

            if (is_file($source) && ! is_file($destination)) {
                File::ensureDirectoryExists(dirname($destination));
                File::copy($source, $destination);
            }

            DB::table('hero_slides')->insert([
                'eyebrow' => $slide['eyebrow'],
                'title' => $slide['title'],
                'highlight' => $slide['highlight'],
                'description' => $slide['description'],
                'details' => json_encode($slide['details'], JSON_THROW_ON_ERROR),
                'image_path' => $slide['image_path'],
                'image_alt' => $slide['image_alt'],
                'primary_label' => $slide['primary_label'],
                'primary_url' => $slide['primary_url'],
                'secondary_label' => $slide['secondary_label'],
                'secondary_url' => $slide['secondary_url'],
                'sort_order' => $slide['sort_order'],
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('hero_slides');
    }
};
