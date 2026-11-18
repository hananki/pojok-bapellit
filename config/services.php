<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Groq API (Chatbot AI)
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk Chatbot AI pakai Groq (OpenAI-compatible).
    | Dapetin API key di: https://console.groq.com/keys
    |
    */

    'groq' => [
        'keys' => array_filter([
            env('GROQ_API_KEY'),
            env('GROQ_API_KEY_2'),
            env('GROQ_API_KEY_3'),
        ]),
        'url' => env('GROQ_API_URL', 'https://api.groq.com/openai/v1'),
        'model' => env('GROQ_MODEL', 'llama-3.3-70b-versatile'),
    ],

];