<?php
session_start();
require_once '../config/config.php';
require_once './routing.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

$currentPage = isset($_GET['page']) ? $_GET['page'] : 'home';
$validPages = ['home', 'user', 'berita', 'kategori', 'komentar'];

if (!in_array($currentPage, $validPages)) {
    $currentPage = 'home';
}

// Get dashboard statistics for home page
if ($currentPage === 'home') {
    try {
        $usersCount = $conn->query("SELECT COUNT(*) as count FROM users")->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
        $newsCount = $conn->query("SELECT COUNT(*) as count FROM berita")->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
        $categoriesCount = $conn->query("SELECT COUNT(*) as count FROM kategori")->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
        $commentsCount = $conn->query("SELECT COUNT(*) as count FROM komentar")->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
    } catch (Exception $e) {
        $usersCount = 0;
        $newsCount = 0;
        $categoriesCount = 0;
        $commentsCount = 0;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="flex h-screen bg-gray-50">
        <!-- Sidebar -->
        <div id="sidebar" class="fixed lg:relative inset-y-0 left-0 z-50 w-64 bg-gradient-to-b from-blue-600 to-blue-800 text-white transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out">
            <!-- Logo/Header -->
            <div class="h-20 flex items-center justify-between px-6 border-b border-blue-700">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center">
                        <i class="fas fa-newspaper text-blue-600 text-lg"></i>
                    </div>
                    <h1 class="text-xl font-bold">News</h1>
                </div>
                <button id="closeSidebar" class="lg:hidden text-white hover:bg-blue-700 p-2 rounded">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- User Info -->
            <div class="px-6 py-4 border-b border-blue-700">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-400 rounded-full flex items-center justify-center">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="text-sm">
                        <p class="font-semibold"><?= htmlspecialchars($_SESSION['username']) ?></p>
                        <p class="text-blue-200 text-xs">Administrator</p>
                    </div>
                </div>
            </div>

            <!-- Navigation Menu -->
            <nav class="px-4 py-6 space-y-2">
                <!-- Dashboard Link -->
                <a href="?page=home" class="flex items-center gap-3 px-4 py-3 rounded-lg <?= $currentPage === 'home' ? 'bg-blue-700 text-white' : 'text-blue-100 hover:bg-blue-700 hover:text-white' ?> font-medium transition-all">
                    <i class="fas fa-th-large w-5"></i>
                    <span>Dashboard</span>
                </a>

                <!-- Users Management -->
                <div class="pt-2">
                    <p class="px-4 py-2 text-blue-200 text-xs font-bold uppercase tracking-wider">Kelola Data</p>
                    
                    <a href="?page=user" class="flex items-center gap-3 px-4 py-2.5 rounded-lg <?= $currentPage === 'user' ? 'bg-blue-700 text-white' : 'text-blue-100 hover:bg-blue-700 hover:text-white' ?> transition-colors">
                        <i class="fas fa-users w-5"></i>
                        <span>Pengguna</span>
                    </a>

                    <a href="?page=berita" class="flex items-center gap-3 px-4 py-2.5 rounded-lg <?= $currentPage === 'berita' ? 'bg-blue-700 text-white' : 'text-blue-100 hover:bg-blue-700 hover:text-white' ?> transition-colors">
                        <i class="fas fa-newspaper w-5"></i>
                        <span>Berita</span>
                    </a>

                    <a href="?page=kategori" class="flex items-center gap-3 px-4 py-2.5 rounded-lg <?= $currentPage === 'kategori' ? 'bg-blue-700 text-white' : 'text-blue-100 hover:bg-blue-700 hover:text-white' ?> transition-colors">
                        <i class="fas fa-folder w-5"></i>
                        <span>Kategori</span>
                    </a>

                    <a href="?page=komentar" class="flex items-center gap-3 px-4 py-2.5 rounded-lg <?= $currentPage === 'komentar' ? 'bg-blue-700 text-white' : 'text-blue-100 hover:bg-blue-700 hover:text-white' ?> transition-colors">
                        <i class="fas fa-comments w-5"></i>
                        <span>Komentar</span>
                    </a>
                </div>

                <!-- Settings -->
                <div class="pt-6 border-t border-blue-700">
                    <a href="../auth/ganti_password.php" class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-blue-700 transition-colors text-blue-100 hover:text-white">
                        <i class="fas fa-lock w-5"></i>
                        <span>Ganti Password</span>
                    </a>

                    <a href="../auth/logout.php" class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-red-600 transition-colors text-blue-100 hover:text-white">
                        <i class="fas fa-sign-out-alt w-5"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </nav>
        </div>

        <!-- Overlay (Mobile) -->
        <div id="sidebarOverlay" class="hidden fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden"></div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Bar -->
            <header class="h-20 bg-white border-b border-gray-200 flex items-center justify-between px-4 md:px-8">
                <button id="toggleSidebar" class="lg:hidden text-gray-600 hover:text-gray-900 p-2 rounded hover:bg-gray-100">
                    <i class="fas fa-bars text-xl"></i>
                </button>

                <h2 class="text-2xl font-bold text-gray-800 hidden md:block">Dashboard</h2>

                <div class="flex items-center gap-4">
                    <div class="hidden sm:flex items-center text-sm text-gray-600">
                        <i class="fas fa-clock mr-2"></i>
                        <span id="currentTime"></span>
                    </div>
                    <button class="relative text-gray-600 hover:text-gray-900 p-2 rounded hover:bg-gray-100">
                        <i class="fas fa-bell text-lg"></i>
                        <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                    </button>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-auto">
                <div class="p-4 md:p-8">
                    <?php
                    // Load module
                    if ($currentPage === 'home') {
                        require_once 'dashboard_main.php';
                    } elseif ($currentPage === 'user') {
                        require_once 'user/r.php';
                    } elseif ($currentPage === 'berita') {
                        require_once 'berita/r.php';
                    } elseif ($currentPage === 'kategori') {
                        require_once 'kategori/r.php';
                    } elseif ($currentPage === 'komentar') {
                        require_once 'komentar/r.php';
                    } else {
                        echo '<p class="text-red-500">Modul tidak ditemukan</p>';
                    }
                    ?>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Sidebar toggle
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const toggleBtn = document.getElementById('toggleSidebar');
        const closeBtn = document.getElementById('closeSidebar');

        toggleBtn.addEventListener('click', () => {
            sidebar.classList.remove('-translate-x-full');
            sidebarOverlay.classList.remove('hidden');
        });

        closeBtn.addEventListener('click', () => {
            sidebar.classList.add('-translate-x-full');
            sidebarOverlay.classList.add('hidden');
        });

        sidebarOverlay.addEventListener('click', () => {
            sidebar.classList.add('-translate-x-full');
            sidebarOverlay.classList.add('hidden');
        });

        // Current time
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID', { 
                hour: '2-digit', 
                minute: '2-digit',
                second: '2-digit'
            });
            document.getElementById('currentTime').textContent = timeString;
        }

        updateTime();
        setInterval(updateTime, 1000);

        // Close sidebar when clicking on a link (mobile)
        document.querySelectorAll('#sidebar a').forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth < 1024) {
                    sidebar.classList.add('-translate-x-full');
                    sidebarOverlay.classList.add('hidden');
                }
            });
        });
    </script>
</body>
</html>