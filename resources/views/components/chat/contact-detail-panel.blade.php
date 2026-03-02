{{--
    Contact Detail Panel Component
    
    Usage:
    <x-chat.contact-detail-panel 
        :contact-type="'whatsapp'" 
        :contact-id="$phoneNumber"
        :show-panel="true" />
    
    Props:
    - contactType: string ('whatsapp', 'instagram', 'web')
    - contactId: string
    - showPanel: boolean (default: false)
--}}
@props([
    'contactType' => 'whatsapp',
    'contactId' => null,
    'showPanel' => false,
])

<div 
    x-data="contactPanel({{ json_encode($contactId) }}, {{ json_encode($contactType) }}, {{ json_encode($showPanel) }})"
    x-show="isOpen"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="translate-x-full opacity-0"
    x-transition:enter-end="translate-x-0 opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="translate-x-0 opacity-100"
    x-transition:leave-end="translate-x-full opacity-0"
    x-cloak
    class="fixed inset-y-0 right-0 w-full sm:w-[400px] bg-gray-900 border-l border-gray-800 shadow-2xl z-50 flex flex-col"
    @contact-panel-open.window="openPanel($event.detail)"
    @keydown.escape.window="closePanel()"
>
    <!-- Header -->
    <div class="h-16 flex items-center justify-between px-4 border-b border-gray-800 bg-gray-900/95 backdrop-blur shrink-0">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center">
                <span class="material-symbols-outlined text-white text-sm">person</span>
            </div>
            <span class="font-semibold text-white">Contact Details</span>
        </div>
        <button 
            @click="closePanel()" 
            class="p-2 hover:bg-gray-800 rounded-lg text-gray-400 hover:text-white transition-colors"
        >
            <span class="material-symbols-outlined">close</span>
        </button>
    </div>

    <!-- Content -->
    <div class="flex-1 overflow-y-auto custom-scrollbar" x-ref="content">
        <!-- Loading State -->
        <div x-show="isLoading" class="flex items-center justify-center py-12">
            <div class="flex flex-col items-center gap-3">
                <span class="material-symbols-outlined animate-spin text-2xl text-blue-500">sync</span>
                <span class="text-sm text-gray-500">Loading contact details...</span>
            </div>
        </div>

        <!-- Error State -->
        <div x-show="error" class="p-4">
            <div class="bg-red-500/10 border border-red-500/20 rounded-lg p-4">
                <div class="flex items-center gap-2 text-red-400">
                    <span class="material-symbols-outlined">error</span>
                    <span class="text-sm" x-text="error"></span>
                </div>
                <button @click="fetchContactDetails()" class="mt-2 text-xs text-red-400 hover:text-red-300 underline">
                    Try again
                </button>
            </div>
        </div>

        <!-- Contact Data -->
        <div x-show="!isLoading && !error && contact" class="pb-6">
            
            <!-- Profile Header -->
            <div class="p-6 text-center border-b border-gray-800">
                <div class="relative inline-block">
                    <div class="w-20 h-20 rounded-full bg-gradient-to-br from-gray-700 to-gray-600 flex items-center justify-center text-2xl font-bold text-white border-4 border-gray-800 shadow-lg">
                        <img x-show="contact.avatar" :src="contact.avatar" class="w-full h-full rounded-full object-cover">
                        <span x-show="!contact.avatar" x-text="getInitials(contact.display_name || contact.name || 'Unknown')"></span>
                    </div>
                    <div class="absolute -bottom-1 -right-1">
                        <div class="w-6 h-6 rounded-full flex items-center justify-center border-2 border-gray-900"
                             :class="{
                                 'bg-green-500': contact.status === 'online' || contact.status === 'active',
                                 'bg-yellow-500': contact.status === 'away' || contact.status === 'idle',
                                 'bg-gray-500': contact.status === 'offline' || contact.status === 'closed',
                                 'bg-blue-500': contact.status === 'bot_active',
                                 'bg-amber-500': contact.status === 'agent_handling'
                             }">
                            <span class="material-symbols-outlined text-[10px] text-white" x-text="getPlatformIcon()"></span>
                        </div>
                    </div>
                </div>
                <h3 class="mt-3 font-bold text-white text-lg" x-text="contact.display_name || contact.name || 'Unknown Contact'"></h3>
                <div class="flex items-center justify-center gap-2 mt-1">
                    <span class="text-sm text-gray-400" x-text="contact.identifier || contact.phone_number || contact.visitor_id"></span>
                </div>
                <div class="flex items-center justify-center gap-2 mt-3">
                    <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-gray-800 border border-gray-700 text-gray-300 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[12px]" x-text="getPlatformIcon()"></span>
                        <span x-text="getPlatformLabel()"></span>
                    </span>
                    <span 
                        class="px-2.5 py-1 rounded-full text-xs font-medium border flex items-center gap-1.5"
                        :class="getStatusClass()"
                    >
                        <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                        <span x-text="formatStatus(contact.status)"></span>
                    </span>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="px-4 py-3 border-b border-gray-800">
                <div class="grid grid-cols-4 gap-2">
                    <button @click="copyIdentifier()" class="flex flex-col items-center gap-1 p-2 rounded-lg hover:bg-gray-800 transition-colors group">
                        <span class="material-symbols-outlined text-gray-400 group-hover:text-blue-400">content_copy</span>
                        <span class="text-[10px] text-gray-500 group-hover:text-gray-300">Copy</span>
                    </button>
                    <button @click="startVoiceCall()" class="flex flex-col items-center gap-1 p-2 rounded-lg hover:bg-gray-800 transition-colors group">
                        <span class="material-symbols-outlined text-gray-400 group-hover:text-green-400">call</span>
                        <span class="text-[10px] text-gray-500 group-hover:text-gray-300">Call</span>
                    </button>
                    <button @click="sendTemplate()" class="flex flex-col items-center gap-1 p-2 rounded-lg hover:bg-gray-800 transition-colors group">
                        <span class="material-symbols-outlined text-gray-400 group-hover:text-purple-400">description</span>
                        <span class="text-[10px] text-gray-500 group-hover:text-gray-300">Template</span>
                    </button>
                    <button @click="showBlockModal = true" class="flex flex-col items-center gap-1 p-2 rounded-lg hover:bg-gray-800 transition-colors group">
                        <span class="material-symbols-outlined text-gray-400 group-hover:text-red-400">block</span>
                        <span class="text-[10px] text-gray-500 group-hover:text-gray-300">Block</span>
                    </button>
                </div>
            </div>

            <!-- Tab Navigation -->
            <div class="px-4 border-b border-gray-800">
                <div class="flex gap-1">
                    <button 
                        @click="activeTab = 'info'" 
                        class="flex-1 py-3 text-xs font-medium border-b-2 transition-colors flex items-center justify-center gap-1.5"
                        :class="activeTab === 'info' ? 'border-blue-500 text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-300'"
                    >
                        <span class="material-symbols-outlined text-sm">info</span>
                        Info
                    </button>
                    <button 
                        @click="activeTab = 'notes'" 
                        class="flex-1 py-3 text-xs font-medium border-b-2 transition-colors flex items-center justify-center gap-1.5"
                        :class="activeTab === 'notes' ? 'border-blue-500 text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-300'"
                    >
                        <span class="material-symbols-outlined text-sm">sticky_note_2</span>
                        Notes
                        <span x-show="notes.length > 0" class="bg-gray-800 text-gray-400 text-[10px] px-1.5 py-0.5 rounded-full" x-text="notes.length"></span>
                    </button>
                    <button 
                        @click="activeTab = 'activity'" 
                        class="flex-1 py-3 text-xs font-medium border-b-2 transition-colors flex items-center justify-center gap-1.5"
                        :class="activeTab === 'activity' ? 'border-blue-500 text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-300'"
                    >
                        <span class="material-symbols-outlined text-sm">history</span>
                        Activity
                    </button>
                </div>
            </div>

            <!-- Info Tab -->
            <div x-show="activeTab === 'info'" class="p-4 space-y-6">
                
                <!-- Contact Information -->
                <div class="space-y-3">
                    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm">contact_page</span>
                        Contact Information
                    </h4>
                    <div class="space-y-2">
                        <template x-if="contact.phone_number">
                            <div class="flex items-center justify-between p-3 bg-gray-800/50 rounded-lg group hover:bg-gray-800 transition-colors">
                                <div class="flex items-center gap-3">
                                    <span class="material-symbols-outlined text-gray-500">phone</span>
                                    <div>
                                        <p class="text-[10px] text-gray-500 uppercase">Phone</p>
                                        <p class="text-sm text-white" x-text="contact.phone_number"></p>
                                    </div>
                                </div>
                                <button @click="copyToClipboard(contact.phone_number)" class="opacity-0 group-hover:opacity-100 p-1.5 hover:bg-gray-700 rounded text-gray-400 hover:text-white transition-all">
                                    <span class="material-symbols-outlined text-sm">content_copy</span>
                                </button>
                            </div>
                        </template>
                        
                        <template x-if="contact.ig_username || contact.username">
                            <div class="flex items-center justify-between p-3 bg-gray-800/50 rounded-lg group hover:bg-gray-800 transition-colors">
                                <div class="flex items-center gap-3">
                                    <span class="material-symbols-outlined text-gray-500">alternate_email</span>
                                    <div>
                                        <p class="text-[10px] text-gray-500 uppercase">Username</p>
                                        <p class="text-sm text-white" x-text="contact.ig_username || contact.username"></p>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <template x-if="contact.visitor_email || contact.email">
                            <div class="flex items-center justify-between p-3 bg-gray-800/50 rounded-lg group hover:bg-gray-800 transition-colors">
                                <div class="flex items-center gap-3">
                                    <span class="material-symbols-outlined text-gray-500">email</span>
                                    <div>
                                        <p class="text-[10px] text-gray-500 uppercase">Email</p>
                                        <p class="text-sm text-white" x-text="contact.visitor_email || contact.email"></p>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <template x-if="contact.ip_address || contact.visitor_ip">
                            <div class="flex items-center justify-between p-3 bg-gray-800/50 rounded-lg group hover:bg-gray-800 transition-colors">
                                <div class="flex items-center gap-3">
                                    <span class="material-symbols-outlined text-gray-500">network_ping</span>
                                    <div>
                                        <p class="text-[10px] text-gray-500 uppercase">IP Address</p>
                                        <p class="text-sm text-white" x-text="contact.ip_address || contact.visitor_ip"></p>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <div class="flex items-center justify-between p-3 bg-gray-800/50 rounded-lg">
                            <div class="flex items-center gap-3">
                                <span class="material-symbols-outlined text-gray-500">schedule</span>
                                <div>
                                    <p class="text-[10px] text-gray-500 uppercase">First Seen</p>
                                    <p class="text-sm text-white" x-text="formatDate(contact.created_at)"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Custom Fields -->
                <div class="space-y-3" x-show="customFields.length > 0">
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm">tune</span>
                            Custom Fields
                        </h4>
                        <button @click="editingCustomFields = !editingCustomFields" class="text-xs text-blue-400 hover:text-blue-300">
                            <span x-text="editingCustomFields ? 'Done' : 'Edit'"></span>
                        </button>
                    </div>
                    <div class="space-y-2">
                        <template x-for="field in customFields" :key="field.id">
                            <div class="p-3 bg-gray-800/50 rounded-lg">
                                <div class="flex items-center justify-between mb-1">
                                    <p class="text-[10px] text-gray-500 uppercase" x-text="field.label || field.name"></p>
                                    <span x-show="field.is_required" class="text-[10px] text-red-400">*</span>
                                </div>
                                
                                <!-- View Mode -->
                                <div x-show="!editingCustomFields" class="text-sm text-white" x-text="formatFieldValue(field)"></div>
                                
                                <!-- Edit Mode -->
                                <div x-show="editingCustomFields" class="mt-1">
                                    <template x-if="field.type === 'text' || field.type === 'email' || field.type === 'phone' || field.type === 'url'">
                                        <input 
                                            type="text" 
                                            x-model="field.value"
                                            @blur="updateCustomField(field)"
                                            class="w-full bg-gray-700 border border-gray-600 rounded px-2 py-1 text-sm text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                        >
                                    </template>
                                    <template x-if="field.type === 'number'">
                                        <input 
                                            type="number" 
                                            x-model="field.value"
                                            @blur="updateCustomField(field)"
                                            class="w-full bg-gray-700 border border-gray-600 rounded px-2 py-1 text-sm text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                        >
                                    </template>
                                    <template x-if="field.type === 'textarea'">
                                        <textarea 
                                            x-model="field.value"
                                            @blur="updateCustomField(field)"
                                            rows="2"
                                            class="w-full bg-gray-700 border border-gray-600 rounded px-2 py-1 text-sm text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 resize-none"
                                        ></textarea>
                                    </template>
                                    <template x-if="field.type === 'select' && field.options">
                                        <select 
                                            x-model="field.value"
                                            @change="updateCustomField(field)"
                                            class="w-full bg-gray-700 border border-gray-600 rounded px-2 py-1 text-sm text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                        >
                                            <option value="">Select...</option>
                                            <template x-for="option in field.options" :key="option">
                                                <option :value="option" x-text="option"></option>
                                            </template>
                                        </select>
                                    </template>
                                    <template x-if="field.type === 'date'">
                                        <input 
                                            type="date" 
                                            x-model="field.value"
                                            @blur="updateCustomField(field)"
                                            class="w-full bg-gray-700 border border-gray-600 rounded px-2 py-1 text-sm text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                        >
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Tags -->
                <div class="space-y-3">
                    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm">label</span>
                        Tags
                    </h4>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="tag in contactTags" :key="tag.id">
                            <span 
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium border transition-all"
                                :style="'background-color: ' + (tag.color ? tag.color + '20' : '#3b82f620') + '; border-color: ' + (tag.color || '#3b82f6') + '40; color: ' + (tag.color || '#60a5fa')"
                            >
                                <span x-text="tag.name"></span>
                                <button 
                                    @click="removeTag(tag.id)" 
                                    class="hover:opacity-70 ml-1"
                                >
                                    <span class="material-symbols-outlined text-[12px]">close</span>
                                </button>
                            </span>
                        </template>
                        <button 
                            @click="showTagSelector = true"
                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-800 border border-gray-700 text-gray-400 hover:text-white hover:border-gray-600 transition-all"
                        >
                            <span class="material-symbols-outlined text-[12px]">add</span>
                            Add Tag
                        </button>
                    </div>
                </div>
            </div>

            <!-- Notes Tab -->
            <div x-show="activeTab === 'notes'" class="p-4 space-y-4">
                <!-- Add Note Form -->
                <div class="space-y-3">
                    <div class="flex gap-2">
                        <select x-model="newNoteCategory" class="bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-xs text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                            <option value="general">General</option>
                            <option value="follow_up">Follow-up</option>
                            <option value="complaint">Complaint</option>
                            <option value="feedback">Feedback</option>
                            <option value="private">Private</option>
                        </select>
                        <div class="flex-1 relative">
                            <textarea 
                                x-model="newNoteContent"
                                placeholder="Add a note about this contact..."
                                rows="3"
                                class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white placeholder-gray-500 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 resize-none"
                            ></textarea>
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button 
                            @click="addNote()" 
                            :disabled="!newNoteContent.trim() || isAddingNote"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-500 disabled:opacity-50 disabled:cursor-not-allowed text-white text-xs font-medium rounded-lg transition-colors flex items-center gap-2"
                        >
                            <span x-show="isAddingNote" class="material-symbols-outlined animate-spin text-sm">sync</span>
                            <span x-show="!isAddingNote" class="material-symbols-outlined text-sm">add</span>
                            Add Note
                        </button>
                    </div>
                </div>

                <!-- Notes List -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider">All Notes</h4>
                        <div class="flex items-center gap-2">
                            <select x-model="notesFilter" class="bg-gray-800 border border-gray-700 rounded px-2 py-1 text-[10px] text-gray-300">
                                <option value="all">All Categories</option>
                                <option value="general">General</option>
                                <option value="follow_up">Follow-up</option>
                                <option value="complaint">Complaint</option>
                                <option value="feedback">Feedback</option>
                                <option value="private">Private</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-3 max-h-[400px] overflow-y-auto custom-scrollbar pr-1">
                        <template x-if="filteredNotes.length === 0">
                            <div class="text-center py-8">
                                <span class="material-symbols-outlined text-4xl text-gray-700 mb-2">sticky_note_2</span>
                                <p class="text-sm text-gray-500">No notes yet</p>
                                <p class="text-xs text-gray-600 mt-1">Add a note to keep track of important information</p>
                            </div>
                        </template>

                        <template x-for="note in filteredNotes" :key="note.id">
                            <div class="bg-gray-800/50 rounded-lg p-4 border border-gray-800 hover:border-gray-700 transition-colors group">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-2">
                                            <span 
                                                class="px-1.5 py-0.5 rounded text-[10px] font-medium uppercase"
                                                :class="getNoteCategoryClass(note.category)"
                                                x-text="note.category || 'general'"
                                            ></span>
                                            <span class="text-[10px] text-gray-500" x-text="formatDateTime(note.created_at)"></span>
                                        </div>
                                        <p class="text-sm text-gray-300 whitespace-pre-wrap" x-text="note.content"></p>
                                        <div class="mt-2 flex items-center gap-2">
                                            <div class="w-5 h-5 rounded-full bg-gray-700 flex items-center justify-center text-[10px] text-gray-400">
                                                <span x-text="getInitials(note.author?.name || 'System')"></span>
                                            </div>
                                            <span class="text-[10px] text-gray-500" x-text="note.author?.name || 'System'"></span>
                                        </div>
                                    </div>
                                    <button 
                                        @click="deleteNote(note.id)"
                                        class="opacity-0 group-hover:opacity-100 p-1.5 hover:bg-red-500/20 hover:text-red-400 rounded text-gray-500 transition-all"
                                        :disabled="isDeletingNote === note.id"
                                    >
                                        <span x-show="isDeletingNote !== note.id" class="material-symbols-outlined text-sm">delete</span>
                                        <span x-show="isDeletingNote === note.id" class="material-symbols-outlined animate-spin text-sm">sync</span>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Activity Tab -->
            <div x-show="activeTab === 'activity'" class="p-4 space-y-4">
                <!-- Stats Cards -->
                <div class="grid grid-cols-3 gap-3">
                    <div class="bg-gray-800/50 rounded-lg p-3 text-center border border-gray-800">
                        <span class="material-symbols-outlined text-2xl text-blue-400 mb-1">chat</span>
                        <p class="text-lg font-bold text-white" x-text="activityStats.messageCount || 0"></p>
                        <p class="text-[10px] text-gray-500 uppercase">Messages</p>
                    </div>
                    <div class="bg-gray-800/50 rounded-lg p-3 text-center border border-gray-800">
                        <span class="material-symbols-outlined text-2xl text-green-400 mb-1">schedule</span>
                        <p class="text-lg font-bold text-white" x-text="activityStats.conversationCount || 0"></p>
                        <p class="text-[10px] text-gray-500 uppercase">Sessions</p>
                    </div>
                    <div class="bg-gray-800/50 rounded-lg p-3 text-center border border-gray-800">
                        <span class="material-symbols-outlined text-2xl text-purple-400 mb-1">response</span>
                        <p class="text-lg font-bold text-white" x-text="activityStats.avgResponseTime || '-'"></p>
                        <p class="text-[10px] text-gray-500 uppercase">Avg Response</p>
                    </div>
                </div>

                <!-- Activity Timeline -->
                <div class="space-y-3">
                    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm">timeline</span>
                        Recent Activity
                    </h4>
                    <div class="space-y-0">
                        <template x-for="(activity, index) in activityLog" :key="index">
                            <div class="flex gap-3 relative">
                                <div class="flex flex-col items-center">
                                    <div 
                                        class="w-8 h-8 rounded-full flex items-center justify-center border-2 border-gray-900"
                                        :class="getActivityIconClass(activity.type)"
                                    >
                                        <span class="material-symbols-outlined text-xs" x-text="getActivityIcon(activity.type)"></span>
                                    </div>
                                    <div x-show="index < activityLog.length - 1" class="w-0.5 h-full bg-gray-800 my-1"></div>
                                </div>
                                <div class="flex-1 pb-4">
                                    <p class="text-sm text-gray-300" x-text="activity.description"></p>
                                    <p class="text-xs text-gray-500 mt-0.5" x-text="formatDateTime(activity.timestamp)"></p>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tag Selector Modal -->
    <div 
        x-show="showTagSelector" 
        x-transition
        class="absolute inset-0 bg-gray-900/95 backdrop-blur-sm z-50 flex items-center justify-center p-4"
        @click.self="showTagSelector = false"
    >
        <div class="bg-gray-800 rounded-xl border border-gray-700 shadow-2xl w-full max-w-sm overflow-hidden">
            <div class="p-4 border-b border-gray-700 flex items-center justify-between">
                <h3 class="font-semibold text-white">Select Tags</h3>
                <button @click="showTagSelector = false" class="text-gray-400 hover:text-white">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="p-4 max-h-[300px] overflow-y-auto custom-scrollbar">
                <div class="space-y-2">
                    <template x-for="tag in availableTags" :key="tag.id">
                        <button 
                            @click="toggleTag(tag.id)"
                            class="w-full flex items-center justify-between p-3 rounded-lg border transition-all"
                            :class="isTagSelected(tag.id) ? 'bg-blue-500/10 border-blue-500/50' : 'bg-gray-800 border-gray-700 hover:border-gray-600'"
                        >
                            <div class="flex items-center gap-3">
                                <span 
                                    class="w-3 h-3 rounded-full"
                                    :style="'background-color: ' + (tag.color || '#3b82f6')"
                                ></span>
                                <span class="text-sm text-white" x-text="tag.name"></span>
                            </div>
                            <span 
                                x-show="isTagSelected(tag.id)"
                                class="material-symbols-outlined text-blue-400"
                            >check</span>
                        </button>
                    </template>
                    <template x-if="availableTags.length === 0">
                        <div class="text-center py-6 text-gray-500">
                            <p class="text-sm">No tags available</p>
                            <p class="text-xs mt-1">Create tags in settings</p>
                        </div>
                    </template>
                </div>
            </div>
            <div class="p-4 border-t border-gray-700 flex justify-end">
                <button @click="showTagSelector = false" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white text-sm font-medium rounded-lg transition-colors">
                    Done
                </button>
            </div>
        </div>
    </div>

    <!-- Block Contact Modal -->
    <div 
        x-show="showBlockModal" 
        x-transition
        class="absolute inset-0 bg-gray-900/95 backdrop-blur-sm z-50 flex items-center justify-center p-4"
        @click.self="showBlockModal = false"
    >
        <div class="bg-gray-800 rounded-xl border border-gray-700 shadow-2xl w-full max-w-sm overflow-hidden">
            <div class="p-4 border-b border-gray-700">
                <div class="flex items-center gap-3 text-red-400">
                    <span class="material-symbols-outlined text-2xl">block</span>
                    <h3 class="font-semibold text-white">Block Contact</h3>
                </div>
            </div>
            <div class="p-4">
                <p class="text-sm text-gray-300">Are you sure you want to block this contact? They will no longer be able to send you messages.</p>
                <div class="mt-4 p-3 bg-gray-900 rounded-lg">
                    <p class="text-xs text-gray-500">This action can be reversed from the blocked contacts settings.</p>
                </div>
            </div>
            <div class="p-4 border-t border-gray-700 flex gap-3">
                <button @click="showBlockModal = false" class="flex-1 px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white text-sm font-medium rounded-lg transition-colors">
                    Cancel
                </button>
                <button 
                    @click="blockContact()" 
                    :disabled="isBlocking"
                    class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-500 disabled:opacity-50 text-white text-sm font-medium rounded-lg transition-colors flex items-center justify-center gap-2"
                >
                    <span x-show="isBlocking" class="material-symbols-outlined animate-spin text-sm">sync</span>
                    <span x-text="isBlocking ? 'Blocking...' : 'Block'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div 
        x-show="toast.show"
        x-transition
        class="absolute bottom-4 left-4 right-4"
    >
        <div 
            class="px-4 py-3 rounded-lg shadow-lg flex items-center gap-3"
            :class="toast.type === 'success' ? 'bg-green-600 text-white' : 'bg-red-600 text-white'"
        >
            <span class="material-symbols-outlined text-sm" x-text="toast.type === 'success' ? 'check_circle' : 'error'"></span>
            <span class="text-sm" x-text="toast.message"></span>
        </div>
    </div>
</div>

<script>
function contactPanel(initialContactId = null, initialContactType = 'whatsapp', initiallyOpen = false) {
    return {
        // State
        isOpen: initiallyOpen,
        isLoading: false,
        error: null,
        contactId: initialContactId,
        contactType: initialContactType,
        contact: null,
        customFields: [],
        contactTags: [],
        availableTags: [],
        notes: [],
        activityLog: [],
        activityStats: {},
        
        // UI State
        activeTab: 'info',
        editingCustomFields: false,
        showTagSelector: false,
        showBlockModal: false,
        newNoteContent: '',
        newNoteCategory: 'general',
        notesFilter: 'all',
        isAddingNote: false,
        isDeletingNote: null,
        isBlocking: false,
        
        // Toast
        toast: {
            show: false,
            type: 'success',
            message: ''
        },

        // Computed
        get filteredNotes() {
            if (this.notesFilter === 'all') return this.notes;
            return this.notes.filter(n => (n.category || 'general') === this.notesFilter);
        },

        init() {
            if (this.isOpen && this.contactId) {
                this.fetchContactDetails();
            }
            this.fetchAvailableTags();
            
            // Listen for events
            window.addEventListener('contact-panel-open', (e) => {
                this.openPanel(e.detail);
            });
        },

        openPanel(data) {
            this.contactId = data.id || data.contactId;
            this.contactType = data.type || data.contactType || 'whatsapp';
            this.isOpen = true;
            this.activeTab = 'info';
            this.fetchContactDetails();
        },

        closePanel() {
            this.isOpen = false;
            this.$dispatch('contact-panel-closed');
        },

        async fetchContactDetails() {
            if (!this.contactId) return;
            
            this.isLoading = true;
            this.error = null;
            
            try {
                const response = await fetch(`/api/contacts/${this.contactType}/${this.contactId}/details`);
                const data = await response.json();
                
                if (data.success) {
                    this.contact = data.contact;
                    this.customFields = data.customFields || [];
                    this.contactTags = data.tags || [];
                    this.notes = data.notes || [];
                    this.activityLog = data.activityLog || [];
                    this.activityStats = data.activityStats || {};
                } else {
                    this.error = data.message || 'Failed to load contact details';
                }
            } catch (err) {
                this.error = 'Network error. Please try again.';
                console.error('Error fetching contact details:', err);
            } finally {
                this.isLoading = false;
            }
        },

        async fetchAvailableTags() {
            try {
                const response = await fetch('/api/tags');
                this.availableTags = await response.json();
            } catch (err) {
                console.error('Error fetching tags:', err);
            }
        },

        async updateCustomField(field) {
            try {
                const response = await fetch(`/api/contacts/${this.contactType}/${this.contactId}/custom-fields`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                    },
                    body: JSON.stringify({
                        field_id: field.id,
                        value: field.value
                    })
                });
                
                const data = await response.json();
                if (data.success) {
                    this.showToast('Field updated successfully', 'success');
                } else {
                    this.showToast(data.message || 'Failed to update field', 'error');
                }
            } catch (err) {
                this.showToast('Error updating field', 'error');
                console.error('Error updating custom field:', err);
            }
        },

        async addNote() {
            if (!this.newNoteContent.trim()) return;
            
            this.isAddingNote = true;
            
            try {
                const response = await fetch(`/api/contacts/${this.contactType}/${this.contactId}/notes`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                    },
                    body: JSON.stringify({
                        content: this.newNoteContent,
                        category: this.newNoteCategory
                    })
                });
                
                const data = await response.json();
                if (data.success) {
                    this.notes.unshift(data.note);
                    this.newNoteContent = '';
                    this.newNoteCategory = 'general';
                    this.showToast('Note added successfully', 'success');
                } else {
                    this.showToast(data.message || 'Failed to add note', 'error');
                }
            } catch (err) {
                this.showToast('Error adding note', 'error');
                console.error('Error adding note:', err);
            } finally {
                this.isAddingNote = false;
            }
        },

        async deleteNote(noteId) {
            if (!confirm('Are you sure you want to delete this note?')) return;
            
            this.isDeletingNote = noteId;
            
            try {
                const response = await fetch(`/api/notes/${noteId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                    }
                });
                
                const data = await response.json();
                if (data.success) {
                    this.notes = this.notes.filter(n => n.id !== noteId);
                    this.showToast('Note deleted successfully', 'success');
                } else {
                    this.showToast(data.message || 'Failed to delete note', 'error');
                }
            } catch (err) {
                this.showToast('Error deleting note', 'error');
                console.error('Error deleting note:', err);
            } finally {
                this.isDeletingNote = null;
            }
        },

        isTagSelected(tagId) {
            return this.contactTags.some(t => t.id === tagId);
        },

        async toggleTag(tagId) {
            if (this.isTagSelected(tagId)) {
                await this.removeTag(tagId);
            } else {
                await this.addTag(tagId);
            }
        },

        async addTag(tagId) {
            try {
                const response = await fetch(`/api/contacts/${this.contactType}/${this.contactId}/tags`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                    },
                    body: JSON.stringify({
                        tag_ids: [...this.contactTags.map(t => t.id), tagId]
                    })
                });
                
                const data = await response.json();
                if (data.success) {
                    this.contactTags = data.tags;
                    this.showToast('Tag added', 'success');
                } else {
                    this.showToast(data.message || 'Failed to add tag', 'error');
                }
            } catch (err) {
                this.showToast('Error adding tag', 'error');
                console.error('Error adding tag:', err);
            }
        },

        async removeTag(tagId) {
            try {
                const response = await fetch(`/api/contacts/${this.contactType}/${this.contactId}/tags`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                    },
                    body: JSON.stringify({
                        tag_ids: this.contactTags.filter(t => t.id !== tagId).map(t => t.id)
                    })
                });
                
                const data = await response.json();
                if (data.success) {
                    this.contactTags = data.tags;
                    this.showToast('Tag removed', 'success');
                } else {
                    this.showToast(data.message || 'Failed to remove tag', 'error');
                }
            } catch (err) {
                this.showToast('Error removing tag', 'error');
                console.error('Error removing tag:', err);
            }
        },

        async blockContact() {
            this.isBlocking = true;
            
            try {
                const response = await fetch(`/api/contacts/${this.contactType}/${this.contactId}/block`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                    }
                });
                
                const data = await response.json();
                if (data.success) {
                    this.showToast('Contact blocked successfully', 'success');
                    this.showBlockModal = false;
                    this.closePanel();
                } else {
                    this.showToast(data.message || 'Failed to block contact', 'error');
                }
            } catch (err) {
                this.showToast('Error blocking contact', 'error');
                console.error('Error blocking contact:', err);
            } finally {
                this.isBlocking = false;
            }
        },

        // Helpers
        getInitials(name) {
            if (!name) return '?';
            return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
        },

        getPlatformIcon() {
            const icons = {
                'whatsapp': 'chat',
                'instagram': 'photo_camera',
                'web': 'language'
            };
            return icons[this.contactType] || 'person';
        },

        getPlatformLabel() {
            const labels = {
                'whatsapp': 'WhatsApp',
                'instagram': 'Instagram',
                'web': 'Web Chat'
            };
            return labels[this.contactType] || 'Unknown';
        },

        formatStatus(status) {
            if (!status) return 'Unknown';
            return status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        },

        getStatusClass() {
            const classes = {
                'online': 'bg-green-500/20 text-green-400 border-green-500/30',
                'active': 'bg-green-500/20 text-green-400 border-green-500/30',
                'away': 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30',
                'idle': 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30',
                'offline': 'bg-gray-500/20 text-gray-400 border-gray-500/30',
                'closed': 'bg-gray-500/20 text-gray-400 border-gray-500/30',
                'bot_active': 'bg-blue-500/20 text-blue-400 border-blue-500/30',
                'agent_handling': 'bg-amber-500/20 text-amber-400 border-amber-500/30'
            };
            return classes[this.contact?.status] || 'bg-gray-500/20 text-gray-400 border-gray-500/30';
        },

        formatDate(date) {
            if (!date) return '-';
            const d = new Date(date);
            return d.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric' 
            });
        },

        formatDateTime(date) {
            if (!date) return '-';
            const d = new Date(date);
            const now = new Date();
            const diff = now - d;
            
            // Less than 24 hours ago
            if (diff < 24 * 60 * 60 * 1000) {
                if (diff < 60 * 1000) return 'Just now';
                if (diff < 60 * 60 * 1000) return Math.floor(diff / (60 * 1000)) + ' min ago';
                return Math.floor(diff / (60 * 60 * 1000)) + ' hours ago';
            }
            
            return d.toLocaleDateString('en-US', { 
                month: 'short', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        },

        formatFieldValue(field) {
            if (!field.value) return '-';
            if (field.type === 'date') return this.formatDate(field.value);
            if (field.type === 'checkbox') return field.value ? 'Yes' : 'No';
            return field.value;
        },

        getNoteCategoryClass(category) {
            const classes = {
                'general': 'bg-blue-500/20 text-blue-400',
                'follow_up': 'bg-green-500/20 text-green-400',
                'complaint': 'bg-red-500/20 text-red-400',
                'feedback': 'bg-purple-500/20 text-purple-400',
                'private': 'bg-gray-500/20 text-gray-400'
            };
            return classes[category] || classes['general'];
        },

        getActivityIcon(type) {
            const icons = {
                'message': 'chat',
                'call': 'call',
                'note': 'sticky_note_2',
                'tag': 'label',
                'status': 'swap_horiz',
                'block': 'block'
            };
            return icons[type] || 'circle';
        },

        getActivityIconClass(type) {
            const classes = {
                'message': 'bg-blue-500 text-white',
                'call': 'bg-green-500 text-white',
                'note': 'bg-yellow-500 text-white',
                'tag': 'bg-purple-500 text-white',
                'status': 'bg-gray-500 text-white',
                'block': 'bg-red-500 text-white'
            };
            return classes[type] || 'bg-gray-600 text-white';
        },

        copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                this.showToast('Copied to clipboard', 'success');
            });
        },

        copyIdentifier() {
            const identifier = this.contact?.phone_number || this.contact?.visitor_id || this.contact?.identifier;
            if (identifier) this.copyToClipboard(identifier);
        },

        startVoiceCall() {
            this.showToast('Voice call feature coming soon', 'success');
        },

        sendTemplate() {
            this.$dispatch('open-template-selector', { contact: this.contact });
            this.closePanel();
        },

        showToast(message, type = 'success') {
            this.toast = { show: true, type, message };
            setTimeout(() => this.toast.show = false, 3000);
        }
    }
}
</script>
