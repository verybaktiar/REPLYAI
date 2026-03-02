{{--
    Example Usage of Contact Detail Panel Component
    
    This file demonstrates how to integrate the Contact Detail Panel
    into your chat interface.
--}}

{{-- Include the contact detail panel component --}}
<x-chat.contact-detail-panel 
    :contact-type="'whatsapp'" 
    :contact-id="null"
    :show-panel="false" />

{{-- Integration with existing WhatsApp Inbox --}}
<script>
// In your existing whatsappInbox() Alpine.js component, add this method:

function whatsappInbox() {
    return {
        // ... existing properties ...
        
        showDetailsPanel: false,
        
        // ... existing methods ...
        
        // Method to open contact panel
        openContactPanel() {
            if (!this.activeChat) return;
            
            // Dispatch event to open contact panel
            window.dispatchEvent(new CustomEvent('contact-panel-open', {
                detail: {
                    type: 'whatsapp',
                    id: this.activeChat.phone_number
                }
            }));
        },
        
        // Updated selectChat method to also fetch contact details
        async selectChat(chat) {
            if (this.activeChat?.phone_number === chat.phone_number) return;
            this.activeChat = chat;
            this.messages = [];
            this.clearFile();
            await this.fetchMessages(chat.phone_number);
            this.scrollToBottom();
            
            // Open contact panel if it was previously open
            if (this.showDetailsPanel) {
                this.openContactPanel();
            }
        }
    }
}
</script>

{{-- Add this button to your chat header to toggle the contact panel --}}
<template x-if="activeChat">
    <button 
        @click="openContactPanel()" 
        class="p-2 rounded-lg transition-colors hover:bg-gray-700 text-gray-400"
        title="View Contact Details"
    >
        <span class="material-symbols-outlined">person</span>
    </button>
</template>

{{-- 
    Alternative: Direct Alpine.js integration without event dispatching:
    
    You can also directly control the panel by passing x-data between components
    or using Alpine.js $store for global state management.
--}}

{{-- Example: Using Alpine.js Store --}}
<script>
document.addEventListener('alpine:init', () => {
    Alpine.store('contactPanel', {
        isOpen: false,
        contactType: null,
        contactId: null,
        
        open(type, id) {
            this.contactType = type;
            this.contactId = id;
            this.isOpen = true;
        },
        
        close() {
            this.isOpen = false;
            this.contactType = null;
            this.contactId = null;
        }
    });
});
</script>

{{-- Then in your component --}}
<x-chat.contact-detail-panel 
    x-data="{ 
        isOpen: $store.contactPanel.isOpen,
        contactType: $store.contactPanel.contactType,
        contactId: $store.contactPanel.contactId
    }"
    @contact-panel-closed.window="$store.contactPanel.close()" />
