@extends('admin.layouts.app')

@section('title', 'Create Template')
@section('page_title', 'Create Message Template')

@section('content')

<div class="max-w-4xl mx-auto">
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('admin.templates.index') }}" class="p-2 bg-surface-light hover:bg-slate-700 rounded-lg transition">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <div>
            <p class="text-slate-400 text-sm">Back to templates</p>
        </div>
    </div>

    <form action="{{ route('admin.templates.store') }}" method="POST" class="space-y-6" x-data="templateEditor()">
        @csrf

        <div class="bg-surface-dark rounded-2xl border border-slate-800 p-6">
            <h2 class="text-lg font-semibold mb-6">Template Information</h2>
            
            <div class="space-y-6">
                {{-- Name --}}
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Template Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full px-4 py-3 bg-surface-light border border-slate-700 rounded-xl focus:border-primary focus:outline-none"
                           placeholder="e.g., Welcome Email">
                    @error('name')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Category --}}
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Category</label>
                    <select name="category" required
                            class="w-full px-4 py-3 bg-surface-light border border-slate-700 rounded-xl focus:border-primary focus:outline-none">
                        @foreach($categories as $key => $label)
                            <option value="{{ $key }}" {{ old('category') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('category')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Description --}}
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Description (Optional)</label>
                    <input type="text" name="description" value="{{ old('description') }}"
                           class="w-full px-4 py-3 bg-surface-light border border-slate-700 rounded-xl focus:border-primary focus:outline-none"
                           placeholder="Brief description of when this template is used">
                    @error('description')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Content Editor --}}
            <div class="lg:col-span-2 bg-surface-dark rounded-2xl border border-slate-800 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold">Template Content</h2>
                    <div class="flex gap-2">
                        <button type="button" @click="insertVariable('@{{ name }}')" class="px-2 py-1 bg-surface-light hover:bg-slate-700 rounded text-xs transition">
                            + @{{ name }}
                        </button>
                        <button type="button" @click="insertVariable('@{{ order_id }}')" class="px-2 py-1 bg-surface-light hover:bg-slate-700 rounded text-xs transition">
                            + @{{ order_id }}
                        </button>
                    </div>
                </div>
                
                <textarea name="content" x-ref="content" x-model="content" @input="updatePreview()" required
                          rows="12"
                          class="w-full px-4 py-3 bg-surface-light border border-slate-700 rounded-xl focus:border-primary focus:outline-none font-mono text-sm"
                          placeholder="Enter your message template here...

Use @{{ variable }} syntax for dynamic content.

Example:
Hello @{{ name }},

Your order @{{ order_id }} has been confirmed.

Thank you,
@{{ app_name }}"
                >{{ old('content') }}</textarea>
                @error('content')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror

                {{-- Variable Hints --}}
                <div class="mt-4 p-4 bg-surface-light/50 rounded-xl">
                    <p class="text-sm text-slate-400 mb-2">Detected Variables:</p>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="varName in detectedVariables" :key="varName">
                            <span class="px-2 py-1 bg-surface-light rounded text-xs text-primary font-mono" x-text="'{{' + varName + '}}'"></span>
                        </template>
                        <span x-show="detectedVariables.length === 0" class="text-sm text-slate-600">No variables detected</span>
                    </div>
                </div>

                {{-- Additional Variables --}}
                <div class="mt-4">
                    <label class="block text-sm font-medium text-slate-300 mb-2">Additional Variables (comma-separated)</label>
                    <input type="text" name="variables" value="{{ old('variables') }}"
                           class="w-full px-4 py-2 bg-surface-light border border-slate-700 rounded-lg text-sm focus:border-primary focus:outline-none"
                           placeholder="custom_var1, custom_var2">
                    <p class="mt-1 text-xs text-slate-500">Add variables not detected in content</p>
                </div>
            </div>

            {{-- Preview & Variables --}}
            <div class="space-y-6">
                {{-- Live Preview --}}
                <div class="bg-surface-dark rounded-2xl border border-slate-800 p-6">
                    <h3 class="font-semibold mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">preview</span>
                        Live Preview
                    </h3>
                    <div class="p-4 bg-surface-light rounded-xl text-sm whitespace-pre-wrap min-h-[150px]" x-html="preview"></div>
                    <p class="mt-2 text-xs text-slate-500">Sample data is used for preview</p>
                </div>

                {{-- Available Variables --}}
                <div class="bg-surface-dark rounded-2xl border border-slate-800 p-6">
                    <h3 class="font-semibold mb-4">Available Variables</h3>
                    <div class="space-y-4 text-sm">
                        <div>
                            <p class="text-xs text-slate-500 mb-2">User</p>
                            <div class="space-y-1">
                                @foreach($availableVariables['user'] as $var => $desc)
                                    <button type="button" @click="insertVariable('{{ $var }}')" class="w-full text-left px-2 py-1.5 hover:bg-surface-light rounded transition flex items-center justify-between group">
                                        <code class="text-primary">{{ $var }}</code>
                                        <span class="material-symbols-outlined text-xs opacity-0 group-hover:opacity-100 text-slate-500">add</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 mb-2">Order</p>
                            <div class="space-y-1">
                                @foreach($availableVariables['order'] as $var => $desc)
                                    <button type="button" @click="insertVariable('{{ $var }}')" class="w-full text-left px-2 py-1.5 hover:bg-surface-light rounded transition flex items-center justify-between group">
                                        <code class="text-primary">{{ $var }}</code>
                                        <span class="material-symbols-outlined text-xs opacity-0 group-hover:opacity-100 text-slate-500">add</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 mb-2">System</p>
                            <div class="space-y-1">
                                @foreach($availableVariables['system'] as $var => $desc)
                                    <button type="button" @click="insertVariable('{{ $var }}')" class="w-full text-left px-2 py-1.5 hover:bg-surface-light rounded transition flex items-center justify-between group">
                                        <code class="text-primary">{{ $var }}</code>
                                        <span class="material-symbols-outlined text-xs opacity-0 group-hover:opacity-100 text-slate-500">add</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Settings --}}
        <div class="bg-surface-dark rounded-2xl border border-slate-800 p-6">
            <h2 class="text-lg font-semibold mb-4">Settings</h2>
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" checked class="w-5 h-5 rounded border-slate-600 text-primary focus:ring-primary bg-surface-light">
                <span class="text-slate-300">Active</span>
            </label>
            <p class="text-sm text-slate-500 mt-2 ml-8">Inactive templates won't be available for use</p>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-4">
            <a href="{{ route('admin.templates.index') }}" class="px-6 py-3 bg-surface-light hover:bg-slate-700 rounded-xl font-semibold transition">
                Cancel
            </a>
            <button type="submit" class="px-6 py-3 bg-primary hover:bg-primary/90 rounded-xl font-semibold transition">
                Create Template
            </button>
        </div>
    </form>
</div>

<script>
function templateEditor() {
    return {
        content: '{{ old('content', '') }}',
        preview: '',
        detectedVariables: [],
        
        sampleData: {
            name: 'John Doe',
            email: 'john@example.com',
            phone: '+62 812-3456-7890',
            order_id: 'INV-2024-001',
            amount: 'Rp 150.000',
            plan_name: 'Pro Plan',
            expiry_date: '2024-12-31',
            app_name: 'REPLYAI',
            support_email: 'support@replyai.id',
            current_date: new Date().toLocaleDateString('id-ID'),
            current_time: new Date().toLocaleTimeString('id-ID'),
        },

        init() {
            this.updatePreview();
        },

        updatePreview() {
            // Detect variables
            const matches = this.content.match(/\{\{([^}]+)\}\}/g) || [];
            this.detectedVariables = [...new Set(matches.map(m => m.slice(2, -2)))];

            // Generate preview
            let preview = this.content;
            for (const [key, value] of Object.entries(this.sampleData)) {
                const regex = new RegExp('{{' + key + '}}', 'g');
                preview = preview.replace(regex, value);
            }
            // Highlight remaining variables
            preview = preview.replace(/\{\{([^}]+)\}\}/g, '<span class="text-yellow-400">[$1]</span>');
            
            this.preview = preview || '<span class="text-slate-500 italic">Start typing to see preview...</span>';
        },

        insertVariable(variable) {
            const textarea = this.$refs.content;
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            
            this.content = this.content.substring(0, start) + variable + this.content.substring(end);
            
            this.$nextTick(() => {
                textarea.focus();
                const newCursor = start + variable.length;
                textarea.setSelectionRange(newCursor, newCursor);
                this.updatePreview();
            });
        }
    }
}
</script>

@endsection
