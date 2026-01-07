<?php
session_start();
require_once '../config/config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

// Get statistics
try {
    $db = getConnection();
    
    $usersCount = $db->query("SELECT COUNT(*) as total FROM users")->fetch(PDO::FETCH_ASSOC)['total'];
    $newsCount = $db->query("SELECT COUNT(*) as total FROM news")->fetch(PDO::FETCH_ASSOC)['total'];
    $categoriesCount = $db->query("SELECT COUNT(*) as total FROM categories")->fetch(PDO::FETCH_ASSOC)['total'];
    $commentsCount = $db->query("SELECT COUNT(*) as total FROM comments")->fetch(PDO::FETCH_ASSOC)['total'];
    
} catch (PDOException $e) {
    $usersCount = $newsCount = $categoriesCount = $commentsCount = 0;
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
        <div id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-gradient-to-b from-blue-600 to-blue-800 text-white transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out">
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
                <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-blue-700 text-white font-medium transition-all">
                    <i class="fas fa-th-large w-5"></i>
                    <span>Dashboard</span>
                </a>

                <!-- Users Management -->
                <div class="pt-2">
                    <p class="px-4 py-2 text-blue-200 text-xs font-bold uppercase tracking-wider">Kelola Data</p>
                    
                    <a href="../admin/user/r.php" class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-blue-700 transition-colors text-blue-100 hover:text-white">
                        <i class="fas fa-users w-5"></i>
                        <span>Pengguna</span>
                    </a>

                    <a href="../admin/berita/r.php" class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-blue-700 transition-colors text-blue-100 hover:text-white">
                        <i class="fas fa-newspaper w-5"></i>
                        <span>Berita</span>
                    </a>

                    <a href="../admin/kategori/r.php" class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-blue-700 transition-colors text-blue-100 hover:text-white">
                        <i class="fas fa-folder w-5"></i>
                        <span>Kategori</span>
                    </a>

                    <a href="../admin/komentar/r.php" class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-blue-700 transition-colors text-blue-100 hover:text-white">
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
                    <!-- Welcome Section -->
                    <div class="mb-8">
                        <h1 class="text-3xl font-bold text-gray-800">Selamat Datang, <?= htmlspecialchars($_SESSION['username']) ?></h1>
                        <p class="text-gray-600 mt-1">Kelola aplikasi berita Anda dari sini</p>
                    </div>

                    <!-- Statistics Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                        <!-- Users Card -->
                        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500 hover:shadow-lg transition-shadow">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-600 text-sm font-medium">Total Pengguna</p>
                                    <p class="text-3xl font-bold text-gray-800 mt-2"><?= $usersCount ?></p>
                                </div>
                                <div class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-users text-2xl text-blue-500"></i>
                                </div>
                            </div>
                            <a href="../admin/user/r.php" class="text-blue-500 text-sm font-medium mt-4 inline-flex items-center gap-1 hover:gap-2 transition-all">
                                Lihat Detail <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>

                        <!-- News Card -->
                        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500 hover:shadow-lg transition-shadow">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-600 text-sm font-medium">Total Berita</p>
                                    <p class="text-3xl font-bold text-gray-800 mt-2"><?= $newsCount ?></p>
                                </div>
                                <div class="w-14 h-14 bg-green-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-newspaper text-2xl text-green-500"></i>
                                </div>
                            </div>
                            <a href="../admin/berita/r.php" class="text-green-500 text-sm font-medium mt-4 inline-flex items-center gap-1 hover:gap-2 transition-all">
                                Lihat Detail <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>

                        <!-- Categories Card -->
                        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500 hover:shadow-lg transition-shadow">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-600 text-sm font-medium">Total Kategori</p>
                                    <p class="text-3xl font-bold text-gray-800 mt-2"><?= $categoriesCount ?></p>
                                </div>
                                <div class="w-14 h-14 bg-purple-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-folder text-2xl text-purple-500"></i>
                                </div>
                            </div>
                            <a href="../admin/kategori/r.php" class="text-purple-500 text-sm font-medium mt-4 inline-flex items-center gap-1 hover:gap-2 transition-all">
                                Lihat Detail <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>

                        <!-- Comments Card -->
                        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-orange-500 hover:shadow-lg transition-shadow">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-600 text-sm font-medium">Total Komentar</p>
                                    <p class="text-3xl font-bold text-gray-800 mt-2"><?= $commentsCount ?></p>
                                </div>
                                <div class="w-14 h-14 bg-orange-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-comments text-2xl text-orange-500"></i>
                                </div>
                            </div>
                            <a href="../admin/komentar/r.php" class="text-orange-500 text-sm font-medium mt-4 inline-flex items-center gap-1 hover:gap-2 transition-all">
                                Lihat Detail <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-6">Aksi Cepat</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <a href="../admin/berita/c.php" class="flex items-center gap-3 p-4 bg-gradient-to-br from-green-50 to-green-100 rounded-lg border border-green-200 hover:border-green-400 transition-all hover:shadow-md">
                                <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center text-white">
                                    <i class="fas fa-plus"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800">Tambah Berita</p>
                                    <p class="text-xs text-gray-600">Buat berita baru</p>
                                </div>
                            </a>

                            <a href="../admin/kategori/c.php" class="flex items-center gap-3 p-4 bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg border border-blue-200 hover:border-blue-400 transition-all hover:shadow-md">
                                <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center text-white">
                                    <i class="fas fa-plus"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800">Tambah Kategori</p>
                                    <p class="text-xs text-gray-600">Kategori baru</p>
                                </div>
                            </a>

                            <a href="../admin/user/c.php" class="flex items-center gap-3 p-4 bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg border border-purple-200 hover:border-purple-400 transition-all hover:shadow-md">
                                <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center text-white">
                                    <i class="fas fa-plus"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800">Tambah Pengguna</p>
                                    <p class="text-xs text-gray-600">Pengguna baru</p>
                                </div>
                            </a>

                            <a href="../auth/logout.php" class="flex items-center gap-3 p-4 bg-gradient-to-br from-red-50 to-red-100 rounded-lg border border-red-200 hover:border-red-400 transition-all hover:shadow-md">
                                <div class="w-10 h-10 bg-red-500 rounded-lg flex items-center justify-center text-white">
                                    <i class="fas fa-sign-out-alt"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800">Logout</p>
                                    <p class="text-xs text-gray-600">Keluar aplikasi</p>
                                </div>
                            </a>
                        </div>
                    </div>
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