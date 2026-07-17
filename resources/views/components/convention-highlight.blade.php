<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

<section id="highlight" class="py-15 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">


        <!-- Section Header -->
        <div class="text-center mb-16">
            <div class="flex items-center justify-center gap-3 mb-3">
                <span class="w-10 h-1 bg-blue-600 rounded-full"></span>
                    <p class="text-xs font-bold uppercase tracking-widest text-blue-600">
                        Convention Highlights
                    </p>
            </div>

            <h2 class="font-serif text-4xl lg:text-5xl text-slate-900">
                Midyear Convention Highlights
            </h2>
        </div>

    {{-- YouTube Video Embed --}}
    <div class="aspect-video w-full">
        <iframe
            class="w-full h-full rounded-lg shadow"
            src="https://www.youtube.com/embed/UU6AmoBhmDI?start=358"
            title="YouTube video player"
            frameborder="0"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
            referrerpolicy="strict-origin-when-cross-origin"
            allowfullscreen>
        </iframe>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-10 px-4">

    {{-- Facebook Post Embed 1 --}}
    <div class="fb-post-wrapper flex justify-center bg-white rounded-xl shadow-md p-3 overflow-hidden transition-all duration-300 ease-in-out hover:-translate-y-2 hover:shadow-2xl">
        <iframe
            src="https://www.facebook.com/plugins/post.php?href={{ urlencode('https://www.facebook.com/permalink.php?story_fbid=122167194212901887&id=61577056622167') }}&show_text=true&width=340"
            width="340"
            height="720"
            style="border:none;overflow:hidden;max-width:100%;"
            scrolling="no"
            frameborder="0"
            allowfullscreen="true"
            allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share">
        </iframe>
    </div>

    {{-- Facebook Post Embed 2 --}}
    <div class="fb-post-wrapper flex justify-center bg-white rounded-xl shadow-md p-3 overflow-hidden transition-all duration-300 ease-in-out hover:-translate-y-2 hover:shadow-2xl">
        <iframe
            src="https://www.facebook.com/plugins/post.php?href={{ urlencode('https://www.facebook.com/permalink.php?story_fbid=122167233824901887&id=61577056622167') }}&show_text=true&width=340"
            width="340"
            height="720"
            style="border:none;overflow:hidden;max-width:100%;"
            scrolling="no"
            frameborder="0"
            allowfullscreen="true"
            allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share">
        </iframe>
    </div>

    {{-- Facebook Post Embed 3 --}}
    <div class="fb-post-wrapper flex justify-center bg-white rounded-xl shadow-md p-3 overflow-hidden transition-all duration-300 ease-in-out hover:-translate-y-2 hover:shadow-2xl">
        <iframe
            src="https://www.facebook.com/plugins/post.php?href={{ urlencode('https://www.facebook.com/permalink.php?story_fbid=122167349732901887&id=61577056622167') }}&show_text=true&width=340"
            width="340"
            height="720"
            style="border:none;overflow:hidden;max-width:100%;"
            scrolling="no"
            frameborder="0"
            allowfullscreen="true"
            allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share">
        </iframe>
    </div>
</div>


</div>
</section>