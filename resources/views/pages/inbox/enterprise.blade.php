<x-enterprise-layout title="Enterprise Inbox">
    <!-- MIDDLE COLUMN: MASTER CHAT LIST -->
    <x-master-chat-list>
        <!-- Chat Item Components with dummy data -->
        <x-chat-item 
            name="Baktiar" 
            message="Gue mau beli paket pro, ready?" 
            time="12:45" 
            active="true" 
            unread="true" 
        />
        <x-chat-item 
            name="Alice Watson" 
            message="Halo, gimana cara setting auto reply?" 
            time="Kemarin" 
        />
        <x-chat-item 
            name="Sakinah Mart" 
            message="Terima kasih, chatbot sangat membantu!" 
            time="2 Jan" 
        />
        <x-chat-item 
            name="Siti Aminah" 
            message="Apakah ada diskon untuk UMKM?" 
            time="1 Jan" 
        />
        <x-chat-item 
            name="Budi Santoso" 
            message="Mau tanya cara integrasi WA Blast." 
            time="30 Des" 
        />
        
        @for ($i = 0; $i < 10; $i++)
            <x-chat-item 
                name="Customer #{{ $i + 1 }}" 
                message="Pesan otomatis dari pelanggan untuk tes scroll..." 
                time="Des" 
            />
        @endfor
    </x-master-chat-list>

    <!-- RIGHT COLUMN: MESSAGE DETAIL AREA -->
    <!-- activeView is handled by Alpine.js in enterprise layout -->
    <x-message-detail name="Baktiar" avatar="B">
        <!-- Message Bubbles with professional spacing and alignment -->
        <x-message-bubble 
            type="received" 
            text="Halo ReplyAI! Gue mau tanya dong." 
            time="12:40" 
        />
        
        <x-message-bubble 
            type="sent" 
            text="Halo Baktiar! Tentu, ada yang bisa kami bantu? Kami siap melayani pertanyaan Anda hari ini." 
            time="12:42" 
        />
        
        <x-message-bubble 
            type="received" 
            text="Gue mau beli paket pro, apa aja kelebihannya dibanding paket basic?" 
            time="12:43" 
        />
        
        <x-message-bubble 
            type="sent" 
            text="Paket Pro memberikan Anda akses ke AI Advanced, 10.000 pesan per bulan, integrasi WhatsApp API resmi, dan fitur broadcast terjadwal." 
            time="12:44" 
        />
        
        <x-message-bubble 
            type="received" 
            text="Sangat menarik. Gue mau beli paket pro sekarang, apakah slotnya ready?" 
            time="12:45" 
        />

        @for ($i = 0; $i < 5; $i++)
            <x-message-bubble 
                type="received" 
                text="Tes scroll detail area ke-{{ $i + 1 }}..." 
                time="12:46" 
            />
        @endfor
    </x-message-detail>
</x-enterprise-layout>
