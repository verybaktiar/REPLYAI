@extends('admin.layouts.app')

@section('title', 'QA Testing Dashboard')
@section('page-title', 'QA Testing')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white">Quality Assurance Testing</h2>
                <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">
                    Lakukan testing sistematis untuk memastikan kualitas aplikasi
                </p>
            </div>
            <div class="flex gap-2">
                <form action="{{ route('admin.qa.reset') }}" method="POST" onsubmit="return confirm('Reset semua hasil test?')">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg text-sm font-medium">
                        Reset Semua Hasil
                    </button>
                </form>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">
        {{ session('success') }}
    </div>
    @endif

    <!-- Test Scenarios by Category -->
    @foreach($testScenarios as $categoryKey => $category)
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-blue-600 to-blue-700">
            <h3 class="text-lg font-bold text-white">{{ $category['name'] }}</h3>
            <p class="text-blue-100 text-sm">{{ $category['description'] }}</p>
        </div>
        
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            @foreach($category['tests'] as $test)
            @php
                $lastResult = $testResults->where('scenario_id', $test['id'])->first();
            @endphp
            <div class="p-6" x-data="{ expanded: false, status: '{{ $lastResult?->status ?? '' }}', notes: '' }">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-3">
                            <!-- Status Badge -->
                            @if($lastResult)
                                @if($lastResult->status === 'pass')
                                <span class="inline-flex items-center gap-1 px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    PASS
                                </span>
                                @elseif($lastResult->status === 'fail')
                                <span class="inline-flex items-center gap-1 px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs font-medium">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                    FAIL
                                </span>
                                @else
                                <span class="inline-flex items-center gap-1 px-2 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-medium">
                                    SKIP
                                </span>
                                @endif
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-1 bg-gray-100 text-gray-500 rounded-full text-xs font-medium">
                                    BELUM TEST
                                </span>
                            @endif
                            
                            <h4 class="font-semibold text-gray-800 dark:text-white">
                                {{ $test['id'] }}: {{ $test['name'] }}
                            </h4>
                        </div>
                        
                        @if($lastResult)
                        <p class="text-xs text-gray-500 mt-1">
                            Terakhir ditest: {{ $lastResult->tested_at->format('d M Y H:i') }} oleh {{ $lastResult->tested_by }}
                            @if($lastResult->notes)
                            - <span class="italic">{{ Str::limit($lastResult->notes, 50) }}</span>
                            @endif
                        </p>
                        @endif
                    </div>
                    
                    <button @click="expanded = !expanded" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition">
                        <svg class="w-5 h-5 text-gray-500 transition-transform" :class="{ 'rotate-180': expanded }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                </div>
                
                <!-- Expanded Content -->
                <div x-show="expanded" x-collapse class="mt-4">
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 mb-4">
                        <h5 class="font-medium text-gray-700 dark:text-gray-300 mb-2">Langkah-langkah:</h5>
                        <ol class="list-decimal list-inside text-sm text-gray-600 dark:text-gray-400 space-y-1">
                            @foreach($test['steps'] as $step)
                            <li>{{ preg_replace('/^\d+\.\s*/', '', $step) }}</li>
                            @endforeach
                        </ol>
                        
                        <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-600">
                            <h5 class="font-medium text-gray-700 dark:text-gray-300 mb-1">Expected Result:</h5>
                            <p class="text-sm text-green-600 dark:text-green-400">✓ {{ $test['expected'] }}</p>
                        </div>
                    </div>
                    
                    <!-- Submit Result Form -->
                    <form action="{{ route('admin.qa.save-result') }}" method="POST" class="flex flex-wrap items-end gap-3">
                        @csrf
                        <input type="hidden" name="scenario_id" value="{{ $test['id'] }}">
                        
                        <div class="flex gap-2">
                            <label class="cursor-pointer">
                                <input type="radio" name="status" value="pass" x-model="status" class="sr-only peer">
                                <span class="inline-flex items-center gap-1 px-4 py-2 rounded-lg border-2 border-gray-200 peer-checked:border-green-500 peer-checked:bg-green-50 peer-checked:text-green-700 text-sm font-medium transition">
                                    ✓ Pass
                                </span>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="status" value="fail" x-model="status" class="sr-only peer">
                                <span class="inline-flex items-center gap-1 px-4 py-2 rounded-lg border-2 border-gray-200 peer-checked:border-red-500 peer-checked:bg-red-50 peer-checked:text-red-700 text-sm font-medium transition">
                                    ✗ Fail
                                </span>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="status" value="skip" x-model="status" class="sr-only peer">
                                <span class="inline-flex items-center gap-1 px-4 py-2 rounded-lg border-2 border-gray-200 peer-checked:border-yellow-500 peer-checked:bg-yellow-50 peer-checked:text-yellow-700 text-sm font-medium transition">
                                    ⊘ Skip
                                </span>
                            </label>
                        </div>
                        
                        <input type="text" name="notes" placeholder="Catatan (opsional)" x-model="notes"
                               class="flex-1 min-w-[200px] px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm dark:bg-gray-700 dark:text-white">
                        
                        <button type="submit" :disabled="!status"
                                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white rounded-lg text-sm font-medium transition">
                            Simpan Hasil
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach

    <!-- Recent Test Results -->
    @if($testResults->count() > 0)
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white">Riwayat Test Terakhir</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Scenario</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Notes</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Tester</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($testResults->take(20) as $result)
                    <tr>
                        <td class="px-4 py-3 text-sm font-mono text-gray-700 dark:text-gray-300">{{ $result->scenario_id }}</td>
                        <td class="px-4 py-3">
                            @if($result->status === 'pass')
                            <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs font-medium">PASS</span>
                            @elseif($result->status === 'fail')
                            <span class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs font-medium">FAIL</span>
                            @else
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded text-xs font-medium">SKIP</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ Str::limit($result->notes, 40) ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $result->tested_by }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $result->tested_at->format('d M H:i') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>
@endsection
