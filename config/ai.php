<?php

return [
    'min_confidence' => env('AI_MIN_CONFIDENCE', 0),
    'ai_cooldown_minutes' => env('AI_COOLDOWN_MINUTES', 0),
    'default_fallback_enabled' => env('DEFAULT_FALLBACK_ENABLED', true),
'default_fallback_text' => env('DEFAULT_FALLBACK_TEXT', 'Terima kasih, pertanyaan Anda akan kami teruskan ke CS. Mohon tunggu sebentar ya.'),

];
