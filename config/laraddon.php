<?php

return [
    /**
     * Generate Api Routes
     * 
     * default: false
     */
    'api_routes' => env('API_ROUTES', false),

    /**
     * Addons path
     * 
     * default: ./addons
     */
    'addons_path' => env('ADDONS_PATH', base_path('addons')),
];