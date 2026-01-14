@extends('layouts.dark')

@section('title', 'Test Business Settings')

@section('content')
<div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
    <h1 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Test Business Settings</h1>
    
    <div class="space-y-4">
        <p class="text-gray-700 dark:text-gray-300">Profile exists: {{ $profile ? 'Yes' : 'No' }}</p>
        
        @if($profile)
            <p class="text-gray-700 dark:text-gray-300">Business Name: {{ $profile->business_name ?? 'N/A' }}</p>
            <p class="text-gray-700 dark:text-gray-300">Business Type: {{ $profile->business_type ?? 'N/A' }}</p>
        @endif
        
        <p class="text-gray-700 dark:text-gray-300">Industries count: {{count($industries ?? []) }}</p>
        
        @if(isset($industries) && count($industries) > 0)
            <div class="mt-4">
                <h3 class="font-semibold mb-2">Industries:</h3>
                <ul class="list-disc pl-5 space-y-1">
                    @foreach($industries as $key => $industry)
                        <li class="text-gray-600 dark:text-gray-400">{{ $key }}: {{ $industry['label'] ?? 'N/A' }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</div>
@endsection
