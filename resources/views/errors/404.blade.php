@extends('layouts.app')

@section('title', 'Page Not Found | Philippine Society of Anesthesiologists')

@section('content')
<section class="px-4 pb-24 pt-32 text-center" aria-labelledby="error-title">
    <div class="mx-auto max-w-2xl">
        <p class="font-heading text-8xl font-bold leading-none text-blue-600 sm:text-9xl" aria-hidden="true">404</p>
        <h1 id="error-title" class="mt-6 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
            Page not found
        </h1>
        <p class="mt-3 text-slate-500">
            Sorry, the page you are looking for does not exist.
        </p>
        <a href="{{ route('home') }}" class="mt-8 inline-flex rounded-lg bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition-colors hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2">
            Return home
        </a>
    </div>
</section>
@endsection
