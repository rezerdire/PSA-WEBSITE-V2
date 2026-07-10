<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>

@section('title', 'Philippine Society of Anesthesiologists')
@extends('layouts.app')
@section('content')
<section class="mt-10 py-12 px-4"
{{-- Steps function --}}
    x-data="{
        currentStep: 'name',
        submitted: false,
        steps: { name: 'Member Name', directory: 'Office Directory', id: 'ID Card Info', benefits: 'Beneficiary', payment: 'Payment' },

        get stepKeys()    { return Object.keys(this.steps); },
        get stepNumber()    { return this.stepKeys.indexOf(this.currentStep) + 1; },
        get totalSteps()  { return this.stepKeys.length; },
        get progressPct() { return (this.stepNumber / this.totalSteps) * 100; },

        isDone(key)   { return this.stepKeys.indexOf(key) + 1 < this.stepNumber; },
        isActive(key) { return key === this.currentStep; },
        canGoTo(key)  { return this.stepKeys.indexOf(key) + 1 <= this.stepNumber; },

        nextStep() { const i = this.stepKeys.indexOf(this.currentStep); if (i < this.totalSteps - 1) this.currentStep = this.stepKeys[i + 1]; },
        prevStep() { const i = this.stepKeys.indexOf(this.currentStep); if (i > 0) this.currentStep = this.stepKeys[i - 1]; },
        submit()    { this.submitted = true; },
        resetForm() { this.currentStep = 'name'; this.submitted = false; }
    }">

    <div class="max-w-3xl mx-auto">

        {{-- Header --}}
        <div class="text-center mb-10">
            <div class="flex items-center justify-center gap-3 mb-3">
                <img src="{{ asset('Images/PSA_LOGO.png') }}" alt="PSA Logo" class="h-14 w-14 object-contain" onerror="this.style.display='none'">
                <div class="text-left">
                    <p class="text-xs font-semibold tracking-widest text-blue-600 uppercase">Philippine Society of Anesthesiologists</p>
                    <h1 class="text-2xl font-bold text-gray-900 leading-tight">Membership Registration</h1>
                </div>
            </div>
            <p class="text-sm text-slate-500 mt-1">Please fill in all required fields.</p>
        </div>

        {{-- Success Screen --}}
        <div x-show="submitted" x-cloak class="bg-white rounded-2xl border border-slate-200 shadow-sm p-12 text-center">
            <div class="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-5">
                <svg class="w-8 h-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h2 class="text-xl font-bold text-gray-900 mb-2">Registration Submitted!</h2>
            <p class="text-slate-500 text-sm mb-8">Your membership application has been received. We will be in touch with you shortly.</p>
            <button @click="resetForm" class="inline-flex items-center gap-2 bg-blue-600 text-white text-sm font-semibold px-6 py-2.5 rounded-lg hover:bg-blue-700 transition-colors">
                Submit Another Registration
            </button>
        </div>

        {{-- Multi-step Form --}}
        <div x-show="!submitted" x-cloak>
            <x-membership.step-progress />
            <x-membership.form-card>
                {{-- Content --}}
                <x-membership.step-name />
                <x-membership.step-id />
                <x-membership.step-benefits />
                <x-membership.step-directory />
                <x-membership.step-payment />
            </x-membership.form-card>   
                
        </div>

    </div>
</section>
@endsection