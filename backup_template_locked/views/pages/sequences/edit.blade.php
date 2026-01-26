<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>REPLYAI - {{ $title }}</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&amp;display=swap" rel="stylesheet"/>
    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <!-- Theme Configuration -->
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#135bec",
                        "background-light": "#f6f6f8",
                        "background-dark": "#101622",
                        "surface-dark": "#1a2230", 
                    },
                    fontFamily: {
                        "display": ["Inter", "sans-serif"]
                    },
                },
            },
        }
    </script>
    <style>
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #101622; }
        ::-webkit-scrollbar-thumb { background: #282e39; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #374151; }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-white font-display overflow-hidden antialiased">
<div class="flex h-screen w-full">
    <!-- Sidebar Navigation -->
    @include('components.sidebar')

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-full overflow-hidden relative">
        <!-- Top Header -->
        <header class="h-16 flex items-center justify-between px-6 lg:px-8 border-b border-slate-800 bg-white/50 dark:bg-background-dark/50 backdrop-blur-sm sticky top-0 z-20">
            <div class="flex items-center gap-3">
                <a href="{{ route('sequences.index') }}" class="p-2 -ml-2 text-slate-600 dark:text-slate-400 hover:text-primary transition-colors">
                    <span class="material-symbols-outlined">arrow_back</span>
                </a>
                <div>
                    <h1 class="font-bold text-lg dark:text-white">{{ $title }}</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Edit sequence "{{ $sequence->name }}"</p>
                </div>
            </div>
            <span class="px-3 py-1.5 rounded-full text-xs font-medium {{ $sequence->is_active ? 'bg-green-500/10 text-green-500 border border-green-500/20' : 'bg-slate-500/10 text-slate-500 border border-slate-500/20' }}">
                {{ $sequence->is_active ? 'Aktif' : 'Nonaktif' }}
            </span>
        </header>

        <!-- Scrollable Content -->
        <div class="flex-1 overflow-y-auto p-6 lg:p-8 scroll-smooth">
            <div class="max-w-3xl mx-auto">
                <form action="{{ route('sequences.update', $sequence) }}" method="POST" id="sequence-form">
                    @csrf
                    @method('PUT')
                    
                    <!-- Basic Info Card -->
                    <div class="bg-surface-dark rounded-xl border border-slate-800 overflow-hidden shadow-sm mb-6">
                        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700/50">
                            <h2 class="font-semibold dark:text-white text-slate-900 flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary">info</span>
                                Informasi Dasar
                            </h2>
                        </div>
                        <div class="p-6 space-y-5">
                            <!-- Name -->
                            <div>
                                <label class="block text-sm font-medium dark:text-white text-slate-900 mb-2">Nama Sequence <span class="text-red-500">*</span></label>
                                <input type="text" name="name" required value="{{ old('name', $sequence->name) }}"
                                       class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 dark:text-white text-slate-900 placeholder-slate-400 dark:placeholder-slate-500 focus:ring-2 focus:ring-primary/50 focus:border-primary/50"
                                       placeholder="Contoh: Welcome Series untuk Pasien Baru"/>
                                @error('name')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div>
                                <label class="block text-sm font-medium dark:text-white text-slate-900 mb-2">Deskripsi (Opsional)</label>
                                <textarea name="description" rows="2"
                                          class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 dark:text-white text-slate-900 placeholder-slate-400 dark:placeholder-slate-500 focus:ring-2 focus:ring-primary/50 focus:border-primary/50"
                                          placeholder="Deskripsi singkat tentang sequence ini...">{{ old('description', $sequence->description) }}</textarea>
                            </div>

                            <!-- Trigger Type & Platform -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium dark:text-white text-slate-900 mb-2">Trigger <span class="text-red-500">*</span></label>
                                    <select name="trigger_type" id="trigger-type"
                                            class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 dark:text-white text-slate-900 focus:ring-2 focus:ring-primary/50 focus:border-primary/50">
                                        @foreach($triggerTypes as $value => $label)
                                            <option value="{{ $value }}" {{ old('trigger_type', $sequence->trigger_type) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <p class="text-xs text-slate-500 mt-1">Kapan sequence ini dimulai</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium dark:text-white text-slate-900 mb-2">Platform <span class="text-red-500">*</span></label>
                                    <select name="platform"
                                            class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 dark:text-white text-slate-900 focus:ring-2 focus:ring-primary/50 focus:border-primary/50">
                                        @foreach($platforms as $value => $label)
                                            <option value="{{ $value }}" {{ old('platform', $sequence->platform) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Trigger Value (for keyword/tag) -->
                            <div id="trigger-value-container" class="{{ in_array($sequence->trigger_type, ['keyword', 'tag_added']) ? '' : 'hidden' }}">
                                <label class="block text-sm font-medium dark:text-white text-slate-900 mb-2" id="trigger-value-label">
                                    {{ $sequence->trigger_type === 'keyword' ? 'Keyword' : 'Nama Tag' }}
                                </label>
                                <input type="text" name="trigger_value" value="{{ old('trigger_value', $sequence->trigger_value) }}"
                                       class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 dark:text-white text-slate-900 placeholder-slate-400 dark:placeholder-slate-500 focus:ring-2 focus:ring-primary/50 focus:border-primary/50"
                                       placeholder="Pisahkan dengan koma untuk beberapa keyword"/>
                            </div>

                            <!-- Is Active -->
                            <div class="flex items-center gap-3">
                                <input type="checkbox" name="is_active" value="1" {{ $sequence->is_active ? 'checked' : '' }}
                                       class="rounded bg-slate-50 dark:bg-slate-800 border-slate-700 text-primary focus:ring-primary">
                                <label class="text-sm dark:text-white text-slate-900">Aktifkan sequence ini</label>
                            </div>
                        </div>
                    </div>

                    <!-- Steps Card -->
                    <div class="bg-surface-dark rounded-xl border border-slate-800 overflow-hidden shadow-sm mb-6">
                        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700/50 flex items-center justify-between">
                            <h2 class="font-semibold dark:text-white text-slate-900 flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary">format_list_numbered</span>
                                Langkah-langkah Pesan
                            </h2>
                            <button type="button" onclick="addStep()" 
                                    class="flex items-center gap-1 px-3 py-1.5 bg-primary/10 text-primary rounded-lg text-xs font-medium hover:bg-primary/20 transition-colors">
                                <span class="material-symbols-outlined" style="font-size: 16px;">add</span>
                                Tambah Langkah
                            </button>
                        </div>
                        <div class="p-6">
                            <div id="steps-container" class="space-y-4">
                                <!-- Steps will be populated from existing data -->
                            </div>
                            <p class="text-xs text-slate-500 mt-4 flex items-center gap-1">
                                <span class="material-symbols-outlined" style="font-size: 14px;">info</span>
                                Setiap langkah akan dikirim sesuai delay yang ditentukan setelah langkah sebelumnya
                            </p>
                        </div>
                    </div>

                    <!-- Stats -->
                    <div class="bg-surface-dark rounded-xl border border-slate-800 overflow-hidden shadow-sm mb-6">
                        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700/50">
                            <h2 class="font-semibold dark:text-white text-slate-900 flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary">analytics</span>
                                Statistik
                            </h2>
                        </div>
                        <div class="p-6 grid grid-cols-3 gap-4 text-center">
                            <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-lg">
                                <p class="text-2xl font-bold text-primary">{{ $sequence->total_enrolled }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Total Terdaftar</p>
                            </div>
                            <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-lg">
                                <p class="text-2xl font-bold text-amber-500">{{ $sequence->active_enrollments_count }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Sedang Aktif</p>
                            </div>
                            <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-lg">
                                <p class="text-2xl font-bold text-green-500">{{ $sequence->total_completed }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Selesai</p>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-between">
                        <form action="{{ route('sequences.destroy', $sequence) }}" method="POST" onsubmit="return confirm('Hapus sequence ini? Semua enrollment juga akan dihapus.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="flex items-center gap-2 px-4 py-2.5 text-red-500 hover:text-red-600 transition-colors">
                                <span class="material-symbols-outlined" style="font-size: 18px;">delete</span>
                                Hapus Sequence
                            </button>
                        </form>
                        
                        <div class="flex gap-3">
                            <a href="{{ route('sequences.index') }}" 
                               class="px-4 py-2.5 text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white transition-colors">
                                Batal
                            </a>
                            <button type="submit" 
                                    class="flex items-center gap-2 px-6 py-2.5 bg-primary hover:bg-blue-600 text-white rounded-lg font-medium transition-colors shadow-lg shadow-blue-900/20">
                                <span class="material-symbols-outlined" style="font-size: 18px;">save</span>
                                Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<template id="step-template">
    <div class="step-item bg-slate-50 dark:bg-slate-800/50 rounded-lg border border-slate-700/50 p-4" data-step-index="" data-step-id="">
        <input type="hidden" name="steps[INDEX][id]" value="">
        <div class="flex items-start gap-4">
            <div class="flex flex-col items-center gap-1 pt-2">
                <div class="size-8 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-sm step-number">1</div>
                <div class="w-0.5 h-full bg-slate-200 dark:bg-slate-700 step-line hidden"></div>
            </div>
            <div class="flex-1 space-y-3">
                <!-- Delay -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium dark:text-white text-slate-900 mb-1">Delay</label>
                        <select name="steps[INDEX][delay_type]" 
                                class="delay-type-select w-full bg-white dark:bg-slate-700 border border-slate-600 rounded-lg px-3 py-2 text-sm dark:text-white text-slate-900 focus:ring-2 focus:ring-primary/50 focus:border-primary/50">
                            @foreach($delayTypes as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium dark:text-white text-slate-900 mb-1">Nilai Delay</label>
                        <input type="number" name="steps[INDEX][delay_value]" value="0" min="0"
                               class="delay-value-input w-full bg-white dark:bg-slate-700 border border-slate-600 rounded-lg px-3 py-2 text-sm dark:text-white text-slate-900 focus:ring-2 focus:ring-primary/50 focus:border-primary/50"/>
                    </div>
                </div>
                <!-- Message -->
                <div>
                    <label class="block text-xs font-medium dark:text-white text-slate-900 mb-1">Isi Pesan <span class="text-red-500">*</span></label>
                    <textarea name="steps[INDEX][message_content]" rows="3" required
                              class="message-content-textarea w-full bg-white dark:bg-slate-700 border border-slate-600 rounded-lg px-3 py-2 text-sm dark:text-white text-slate-900 focus:ring-2 focus:ring-primary/50 focus:border-primary/50"
                              placeholder="Tulis pesan yang akan dikirim..."></textarea>
                </div>
            </div>
            <button type="button" onclick="removeStep(this)" 
                    class="p-2 text-slate-400 hover:text-red-500 hover:bg-red-500/10 rounded-lg transition-colors step-remove hidden">
                <span class="material-symbols-outlined" style="font-size: 18px;">delete</span>
            </button>
        </div>
    </div>
</template>

<script>
    let stepIndex = 0;
    const existingSteps = @json($sequence->steps);

    function addStep(data = null) {
        const container = document.getElementById('steps-container');
        const template = document.getElementById('step-template');
        const clone = template.content.cloneNode(true);
        const stepItem = clone.querySelector('.step-item');
        
        // Update index
        stepItem.dataset.stepIndex = stepIndex;
        stepItem.innerHTML = stepItem.innerHTML.replace(/INDEX/g, stepIndex);
        
        // Fill data if editing
        if (data) {
            stepItem.dataset.stepId = data.id;
            stepItem.querySelector('input[name*="[id]"]').value = data.id;
            stepItem.querySelector('.delay-type-select').value = data.delay_type;
            stepItem.querySelector('.delay-value-input').value = data.delay_value;
            stepItem.querySelector('.message-content-textarea').value = data.message_content;
        }
        
        // Update step number
        stepItem.querySelector('.step-number').textContent = stepIndex + 1;
        
        container.appendChild(stepItem);
        stepIndex++;
        
        updateStepUI();
    }

    function removeStep(button) {
        const stepItem = button.closest('.step-item');
        stepItem.remove();
        updateStepUI();
    }

    function updateStepUI() {
        const steps = document.querySelectorAll('.step-item');
        steps.forEach((step, index) => {
            // Update step number
            step.querySelector('.step-number').textContent = index + 1;
            
            // Show/hide remove button (only if more than 1 step)
            const removeBtn = step.querySelector('.step-remove');
            if (steps.length > 1) {
                removeBtn.classList.remove('hidden');
            } else {
                removeBtn.classList.add('hidden');
            }
            
            // Show/hide line connector
            const line = step.querySelector('.step-line');
            if (index < steps.length - 1) {
                line.classList.remove('hidden');
            } else {
                line.classList.add('hidden');
            }
        });
    }

    // Toggle trigger value field
    document.getElementById('trigger-type').addEventListener('change', function() {
        const container = document.getElementById('trigger-value-container');
        const label = document.getElementById('trigger-value-label');
        
        if (this.value === 'keyword') {
            container.classList.remove('hidden');
            label.textContent = 'Keyword';
        } else if (this.value === 'tag_added') {
            container.classList.remove('hidden');
            label.textContent = 'Nama Tag';
        } else {
            container.classList.add('hidden');
        }
    });

    // Initialize with existing steps
    document.addEventListener('DOMContentLoaded', function() {
        if (existingSteps.length > 0) {
            existingSteps.forEach(step => addStep(step));
        } else {
            addStep();
        }
    });
</script>

</body>
</html>
