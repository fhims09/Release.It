<?php
// Pengaturan AI untuk Rilis. Biarkan api_key kosong untuk memakai chatbot kata kunci saja.
return [
    'provider' => 'gemini',                  // 'gemini' atau 'claude'
    'api_key'  => '',                        // tempel API key di sini (JANGAN di-upload ke GitHub)
    'model'    => 'gemini-3.5-flash-lite',   // Gemini: salin ID model dari Google AI Studio.
                                             // Claude: misalnya 'claude-haiku-5-5'
];
