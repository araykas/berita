<?php
session_start();

// Hapus semua data session
$_SESSION = [];

// Hapus cookie session
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Hancurkan session
session_destroy();

// Jika AJAX request, return JSON
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'message' => 'Logout berhasil']);
    exit;
}

// Jika regular request, redirect dengan delay
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - Aplikasi</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-red-500 to-pink-600 min-h-screen flex items-center justify-center p-3 sm:p-4 md:p-6">

<div class="w-full max-w-xs sm:max-w-sm text-center">
    <div class="bg-white rounded-lg sm:rounded-xl shadow-lg sm:shadow-2xl p-5 sm:p-6 md:p-8">
        <!-- Loading Animation -->
        <div class="mb-6 sm:mb-8">
            <div class="inline-flex items-center justify-center w-16 sm:w-20 h-16 sm:h-20 bg-red-100 rounded-full mb-3 sm:mb-4">
                <svg class="w-8 sm:w-10 h-8 sm:h-10 text-red-500 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16.5c3.846 0 7.526 1.415 10.293 3.75M15 19H9m6-6a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
            </div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-800 mt-3 sm:mt-4">Sedang Logout...</h1>
            <p class="text-gray-600 text-xs sm:text-sm mt-1 sm:mt-2">Terima kasih telah menggunakan aplikasi kami</p>
        </div>
        
        <!-- Progress Bar -->
        <div class="mt-6 sm:mt-8 bg-gray-200 rounded-full h-2 overflow-hidden">
            <div class="bg-gradient-to-r from-red-500 to-pink-500 h-full animate-pulse" style="width: 100%;"></div>
        </div>
        
        <p class="text-gray-500 text-xs mt-3 sm:mt-4">Anda akan dialihkan dalam beberapa detik...</p>
    </div>
</div>

<script>
// Redirect ke halaman login setelah 2 detik
setTimeout(function() {
    window.location.href = '/public/index.php';
}, 2000);
</script>

</body>
</html>
