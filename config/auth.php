<?php

/**
 * auth.php - Cấu hình authentication
 */

return [
    'session_lifetime' => 120, // minutes
    'session_name' => 'business_session',
    'password_hash_algo' => PASSWORD_BCRYPT,
    'password_min_length' => 8,
    
    // TODO: Thêm cấu hình token, remember me, etc.
];
