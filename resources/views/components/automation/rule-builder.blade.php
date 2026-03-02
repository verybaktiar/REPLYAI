@props(['type' => 'keyword', 'keywords' => [], 'matchType' => 'contains'])

<div class="bg-surface-dark rounded-xl p-5 border border-surface-lighter" x-data="ruleBuilder()">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-bold text-white flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">rule</span>
            Rule Builder
        </h3>
        <span class="text-xs text-text-secondary bg-[#111722] px-2 py-1 rounded">{{ $type }}</span>
    </div>

    @if($type === 'keyword')
    <div class="space-y-4">
        <!-- Match Type Selector -->
        <div>
            <label class="block text-sm font-medium text-gray-200 mb-2">Tipe Pencocokan</label>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                @foreach(['contains' => 'Contains', 'exact' => 'Exact', 'starts_with' => 'Starts With', 'regex' => 'Regex'] as $value => $label)
                <label class="cursor-pointer">
                    <input type="radio" name="match_type" value="{{ $value }}" 
                           {{ $matchType === $value ? 'checked' : '' }}
                           class="sr-only peer"
                           x-model="matchType">
                    <div class="px-3 py-2 rounded-lg bg-[#111722] border border-surface-lighter text-text-secondary text-xs text-center peer-checked:bg-primary peer-checked:border-primary peer-checked:text-white transition-colors">
                        {{ $label }}
                    </div>
                </label>
                @endforeach
            </div>
        </div>

        <!-- Keywords Input -->
        <div>
            <label class="block text-sm font-medium text-gray-200 mb-2">Kata Kunci</label>
            <div class="flex gap-2 mb-2">
                <input type="text" 
                       x-model="newKeyword" 
                       @keydown.enter.prevent="addKeyword"
                       class="flex-1 rounded-lg border border-surface-lighter bg-[#111722] px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-primary placeholder-text-secondary/50"
                       placeholder="Tambah kata kunci...">
                <button type="button" @click="addKeyword" 
                        class="px-4 py-2 bg-primary hover:bg-blue-600 rounded-lg text-white text-sm font-medium transition-colors">
                    <span class="material-symbols-outlined">add</span>
                </button>
            </div>
            
            <!-- Keyword Tags with Match Indicator -->
            <div class="flex flex-wrap gap-2 mt-3" id="keyword-tags">
                @foreach($keywords as $keyword)
                <span class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-500/10 text-blue-400 text-sm rounded-full group">
                    <span class="text-xs opacity-50" x-text="matchType === 'contains' ? '∋' : (matchType === 'exact' ? '=' : (matchType === 'starts_with' ? '^' : '.*'))"></span>
                    {{ $keyword }}
                    <button type="button" onclick="this.parentElement.remove()" 
                            class="hover:text-red-400 transition-colors">
                        <span class="material-symbols-outlined text-[16px]">close</span>
                    </button>
                    <input type="hidden" name="keywords[]" value="{{ $keyword }}">
                </span>
                @endforeach
            </div>
        </div>

        <!-- Test Area -->
        <div class="border-t border-surface-lighter pt-4 mt-4">
            <label class="block text-sm font-medium text-gray-200 mb-2">Test Rule</label>
            <div class="flex gap-2">
                <input type="text" 
                       x-model="testInput"
                       @input="testRule"
                       class="flex-1 rounded-lg border border-surface-lighter bg-[#111722] px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-primary placeholder-text-secondary/50"
                       placeholder="Ketik pesan untuk test...">
            </div>
            <div x-show="testResult !== null" 
                 :class="testResult ? 'text-green-400' : 'text-red-400'"
                 class="mt-2 text-sm flex items-center gap-2">
                <span class="material-symbols-outlined" x-text="testResult ? 'check_circle' : 'cancel'"></span>
                <span x-text="testResult ? 'Rule cocok!' : 'Rule tidak cocok'"></span>
            </div>
        </div>
    </div>
    @endif

    @if($type === 'away')
    <div class="space-y-4">
        <!-- Time Range Visual -->
        <div>
            <label class="block text-sm font-medium text-gray-200 mb-3">Jam Non-Aktif</label>
            <div class="relative h-12 bg-[#111722] rounded-lg overflow-hidden">
                <!-- Timeline -->
                <div class="absolute inset-0 flex">
                    @for($i = 0; $i < 24; $i++)
                    <div class="flex-1 border-r border-surface-lighter/30 relative">
                        @if($i % 6 === 0)
                        <span class="absolute bottom-0 left-1 text-[10px] text-text-secondary">{{ $i }}:00</span>
                        @endif
                    </div>
                    @endfor
                </div>
                <!-- Active Range Indicator -->
                <div class="absolute top-2 bottom-2 bg-orange-500/30 rounded"
                     style="left: 70.83%; width: 37.5%;"></div>
            </div>
            <p class="text-xs text-text-secondary mt-2">
                <span class="inline-block w-3 h-3 bg-orange-500/30 rounded mr-1"></span>
                Waktu away message aktif
            </p>
        </div>

        <!-- Days Selector Visual -->
        <div>
            <label class="block text-sm font-medium text-gray-200 mb-3">Hari Aktif</label>
            <div class="flex gap-1">
                @foreach(['S', 'M', 'T', 'W', 'T', 'F', 'S'] as $i => $day)
                <div class="flex-1 aspect-square rounded-lg flex items-center justify-center text-sm font-bold
                    {{ in_array($i, [0, 6]) ? 'bg-surface-lighter text-text-secondary' : 'bg-orange-500/20 text-orange-400 border border-orange-500/30' }}">
                    {{ $day }}
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    @if($type === 'follow_up')
    <div class="space-y-4">
        <!-- Delay Timeline -->
        <div>
            <label class="block text-sm font-medium text-gray-200 mb-3">Timeline Follow-up</label>
            <div class="relative">
                <!-- Timeline Bar -->
                <div class="h-2 bg-[#111722] rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-primary to-purple-500 w-1/3"></div>
                </div>
                
                <!-- Markers -->
                <div class="flex justify-between mt-2 text-xs text-text-secondary">
                    <span>Pesan Masuk</span>
                    <span class="text-purple-400 font-bold">Follow-up Sent</span>
                    <span>24h</span>
                </div>
            </div>
        </div>

        <!-- Condition Preview -->
        <div class="bg-[#111722] rounded-lg p-4">
            <p class="text-sm text-text-secondary mb-2">Kondisi Trigger:</p>
            <ul class="space-y-2 text-sm">
                <li class="flex items-center gap-2 text-gray-300">
                    <span class="material-symbols-outlined text-green-500 text-[18px]">check</span>
                    Tidak ada balasan dari kontak selama <span class="text-purple-400 font-bold">X jam</span>
                </li>
                <li class="flex items-center gap-2 text-gray-300">
                    <span class="material-symbols-outlined text-green-500 text-[18px]">check</span>
                    Chat masih dalam status aktif
                </li>
                <li class="flex items-center gap-2 text-gray-300">
                    <span class="material-symbols-outlined text-green-500 text-[18px]">check</span>
                    Follow-up sebelumnya sudah terjawab
                </li>
            </ul>
        </div>
    </div>
    @endif

    @if($type === 'welcome')
    <div class="space-y-4">
        <!-- Trigger Preview -->
        <div class="bg-[#111722] rounded-lg p-4">
            <p class="text-sm text-text-secondary mb-3">Trigger Event:</p>
            <div class="flex items-center gap-3">
                <div class="size-10 rounded-full bg-green-500/10 flex items-center justify-center">
                    <span class="material-symbols-outlined text-green-500">chat</span>
                </div>
                <div>
                    <p class="font-medium text-white">Kontak Baru</p>
                    <p class="text-xs text-text-secondary">Pesan pertama dari kontak yang belum pernah chat</p>
                </div>
            </div>
        </div>

        <!-- Behavior Settings -->
        <div>
            <label class="block text-sm font-medium text-gray-200 mb-3">Pengaturan</label>
            <div class="space-y-2">
                <label class="flex items-center gap-3 p-3 bg-[#111722] rounded-lg cursor-pointer">
                    <input type="checkbox" checked class="rounded bg-surface-dark border-surface-lighter text-primary focus:ring-primary">
                    <span class="text-sm text-gray-200">Kirim hanya sekali per kontak</span>
                </label>
                <label class="flex items-center gap-3 p-3 bg-[#111722] rounded-lg cursor-pointer">
                    <input type="checkbox" class="rounded bg-surface-dark border-surface-lighter text-primary focus:ring-primary">
                    <span class="text-sm text-gray-200">Tunda jika agent sedang online</span>
                </label>
            </div>
        </div>
    </div>
    @endif
</div>

<script>
function ruleBuilder() {
    return {
        matchType: '{{ $matchType }}',
        newKeyword: '',
        testInput: '',
        testResult: null,
        keywords: @json($keywords),

        addKeyword() {
            if (this.newKeyword.trim()) {
                const tagContainer = document.getElementById('keyword-tags');
                const span = document.createElement('span');
                span.className = 'inline-flex items-center gap-1 px-3 py-1.5 bg-blue-500/10 text-blue-400 text-sm rounded-full';
                span.innerHTML = `
                    <span class="text-xs opacity-50">${this.getMatchSymbol()}</span>
                    ${this.newKeyword.trim()}
                    <button type="button" onclick="this.parentElement.remove()" class="hover:text-red-400 transition-colors">
                        <span class="material-symbols-outlined text-[16px]">close</span>
                    </button>
                    <input type="hidden" name="keywords[]" value="${this.newKeyword.trim().toLowerCase()}">
                `;
                tagContainer.appendChild(span);
                this.keywords.push(this.newKeyword.trim().toLowerCase());
                this.newKeyword = '';
            }
        },

        getMatchSymbol() {
            const symbols = {
                'contains': '∋',
                'exact': '=',
                'starts_with': '^',
                'regex': '.*'
            };
            return symbols[this.matchType] || '∋';
        },

        testRule() {
            if (!this.testInput.trim()) {
                this.testResult = null;
                return;
            }

            const input = this.testInput.toLowerCase();
            this.testResult = this.keywords.some(keyword => {
                const k = keyword.toLowerCase();
                switch(this.matchType) {
                    case 'exact': return input === k;
                    case 'starts_with': return input.startsWith(k);
                    case 'regex': return new RegExp(k).test(input);
                    default: return input.includes(k);
                }
            });
        }
    }
}
</script>
