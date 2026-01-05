<?php
session_start();
require_once '../config/config.php';

$isLoggedIn = isset($_SESSION['user_id']);
$username = $_SESSION['username'] ?? '';
$db = getConnection();

// Ambil kategori dari database
$categories = [];
try {
    $stmt = $db->query("SELECT id, name FROM categories ORDER BY name");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $categories = [];
}

// Ambil berita terbaru (untuk hero dan featured)
$news = [];
$featuredNews = null;
try {
    // Ambil 1 berita terbaru untuk featured
    $stmt = $db->query("SELECT id, title, excerpt, content, image, category_id, author_id, created_at FROM news ORDER BY created_at DESC LIMIT 1");
    $featuredNews = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Ambil 9 berita untuk grid
    $stmt = $db->query("SELECT id, title, excerpt, image, category_id, created_at FROM news ORDER BY created_at DESC LIMIT 9");
    $news = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $news = [];
    $featuredNews = null;
}

// Helper function untuk format tanggal
function formatDate($date) {
    return date('d M Y', strtotime($date));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Berita - Aplikasi Berita Terkini</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">

<!-- ==================== HEADER & NAVIGATION ==================== -->
<header class="sticky top-0 z-50 bg-white shadow-md">
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16 sm:h-20">
            <!-- Logo -->
            <div class="flex items-center gap-2">
                <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                    <i class="fas fa-newspaper text-white text-lg"></i>
                </div>
                <a href="index.php"><h1 class="text-lg sm:text-xl font-bold text-gray-800 hidden sm:block">BeritaHub</h1></a>
            </div>

            <!-- Search Bar - Desktop -->
            <div class="hidden md:flex flex-1 mx-8">
                <div class="w-full relative">
                    <input type="text" placeholder="Cari berita..." class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                    <button class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-blue-500">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>

            <!-- Menu Navigation -->
            <div class="flex items-center gap-2 sm:gap-4">
                <!-- Mobile Menu Button -->
                <button onclick="toggleMobileMenu()" class="md:hidden text-gray-600 hover:text-blue-500 transition-colors">
                    <i class="fas fa-bars text-xl"></i>
                </button>

                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center gap-6">
                    <!-- Home -->
                    <a href="index.php" class="text-gray-700 hover:text-blue-500 font-medium transition-colors">
                        <i class="fas fa-home mr-1"></i>Home
                    </a>

                    <!-- Categories Dropdown -->
                    <div class="relative group">
                        <button class="text-gray-700 hover:text-blue-500 font-medium transition-colors flex items-center gap-1">
                            <i class="fas fa-list mr-1"></i>Kategori
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                        <div class="absolute left-0 mt-0 w-48 bg-white shadow-lg rounded-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 py-2">
                            <?php if (!empty($categories)): ?>
                                <?php foreach ($categories as $cat): ?>
                                    <a href="kategori.php?id=<?= $cat['id'] ?>" class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-500 transition-colors">
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="px-4 py-2 text-gray-500 text-sm">Tidak ada kategori</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Login/Account Dropdown -->
                    <?php if (!$isLoggedIn): ?>
                        <a href="../auth/login.php" class="px-4 py-2 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-lg hover:shadow-lg transition-shadow">
                            <i class="fas fa-sign-in-alt mr-1"></i>Login
                        </a>
                    <?php else: ?>
                        <div class="relative group">
                            <button class="flex items-center gap-2 px-4 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                                <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white text-sm font-bold">
                                    <?= strtoupper(substr($username, 0, 1)) ?>
                                </div>
                                <span class="text-gray-700 font-medium"><?= htmlspecialchars($username) ?></span>
                                <i class="fas fa-chevron-down text-xs text-gray-600"></i>
                            </button>
                            <div class="absolute right-0 mt-0 w-48 bg-white shadow-lg rounded-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 py-2">
                                <a href="profil.php" class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-500 transition-colors">
                                    <i class="fas fa-user mr-2"></i>Profil Saya
                                </a>
                                <a href="riwayat_baca.php" class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-500 transition-colors">
                                    <i class="fas fa-history mr-2"></i>Riwayat Baca
                                </a>
                                <a href="riwayat_komentar.php" class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-500 transition-colors">
                                    <i class="fas fa-comments mr-2"></i>Riwayat Komentar
                                </a>
                                <hr class="my-2">
                                <a href="../auth/logout.php" class="block px-4 py-2 text-red-600 hover:bg-red-50 transition-colors">
                                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobileMenu" class="hidden md:hidden pb-4 border-t border-gray-200">
            <!-- Search Bar Mobile -->
            <div class="py-3">
                <div class="relative">
                    <input type="text" placeholder="Cari berita..." class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                    <button class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-blue-500">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>

            <!-- Mobile Menu Items -->
            <a href="index.php" class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-500 transition-colors rounded">
                <i class="fas fa-home mr-2"></i>Home
            </a>
            
            <button onclick="toggleCategoryMenu()" class="w-full text-left px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-500 transition-colors rounded flex items-center justify-between">
                <span><i class="fas fa-list mr-2"></i>Kategori</span>
                <i class="fas fa-chevron-down text-xs"></i>
            </button>
            <div id="mobileCategoryMenu" class="hidden pl-4">
                <?php if (!empty($categories)): ?>
                    <?php foreach ($categories as $cat): ?>
                        <a href="kategori.php?id=<?= $cat['id'] ?>" class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-500 transition-colors rounded text-sm">
                            <?= htmlspecialchars($cat['name']) ?>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <?php if (!$isLoggedIn): ?>
                <a href="../auth/login.php" class="block px-4 py-2 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-lg mt-2 text-center hover:shadow-lg transition-shadow">
                    <i class="fas fa-sign-in-alt mr-1"></i>Login
                </a>
            <?php else: ?>
                <div class="mt-3 border-t border-gray-200 pt-3">
                    <div class="px-4 py-2 flex items-center gap-2 mb-2">
                        <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white text-sm font-bold">
                            <?= strtoupper(substr($username, 0, 1)) ?>
                        </div>
                        <span class="font-medium text-gray-700"><?= htmlspecialchars($username) ?></span>
                    </div>
                    <a href="profil.php" class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-500 transition-colors rounded text-sm">
                        <i class="fas fa-user mr-2"></i>Profil Saya
                    </a>
                    <a href="riwayat_baca.php" class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-500 transition-colors rounded text-sm">
                        <i class="fas fa-history mr-2"></i>Riwayat Baca
                    </a>
                    <a href="riwayat_komentar.php" class="block px-4 py-2 text-gray-700 hover:bg-blue-50 hover:text-blue-500 transition-colors rounded text-sm">
                        <i class="fas fa-comments mr-2"></i>Riwayat Komentar
                    </a>
                    <a href="../auth/logout.php" class="block px-4 py-2 text-red-600 hover:bg-red-50 transition-colors rounded text-sm mt-2 border-t border-gray-200 pt-2">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </nav>
</header>

<!-- ==================== MAIN CONTENT ==================== -->
<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
    
    <!-- Featured News Section -->
    <?php if ($featuredNews): ?>
    <section class="mb-12">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 rounded-lg overflow-hidden shadow-lg hover:shadow-xl transition-shadow">
            <!-- Featured Image -->
            <div class="md:col-span-2 h-64 sm:h-80 md:h-96 overflow-hidden bg-gray-200">
                <?php if ($featuredNews['image']): ?>
                    <img src="<?= htmlspecialchars($featuredNews['image']) ?>" alt="<?= htmlspecialchars($featuredNews['title']) ?>" class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                <?php else: ?>
                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-blue-200 to-purple-200">
                        <i class="fas fa-image text-4xl text-blue-400"></i>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Featured Content -->
            <div class="bg-white p-6 sm:p-8 flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-3">
                        <span class="px-3 py-1 bg-blue-100 text-blue-600 text-xs font-semibold rounded-full">
                            <i class="fas fa-star mr-1"></i>FEATURED
                        </span>
                        <span class="text-xs text-gray-500">
                            <i class="fas fa-calendar mr-1"></i><?= formatDate($featuredNews['created_at']) ?>
                        </span>
                    </div>
                    <h2 class="text-xl sm:text-2xl font-bold text-gray-800 mb-3 line-clamp-3 hover:text-blue-500 transition-colors cursor-pointer">
                        <?= htmlspecialchars(substr($featuredNews['title'], 0, 100)) ?>...
                    </h2>
                    <p class="text-gray-600 text-sm line-clamp-3">
                        <?= htmlspecialchars($featuredNews['excerpt'] ?? substr($featuredNews['content'], 0, 150)) ?>
                    </p>
                </div>
                <a href="artikel.php?id=<?= $featuredNews['id'] ?>" class="mt-4 inline-block px-4 py-2 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-lg hover:shadow-lg transition-shadow">
                    Baca Selengkapnya
                    <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- News Grid Section -->
    <section>
        <div class="mb-8">
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-800">
                <i class="fas fa-fire text-orange-500 mr-2"></i>Berita Terbaru
            </h2>
            <div class="h-1 w-20 bg-gradient-to-r from-blue-500 to-purple-600 mt-2 rounded"></div>
        </div>

        <?php if (!empty($news)): ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
            <?php foreach ($news as $item): ?>
            <article class="bg-white rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300 overflow-hidden group cursor-pointer">
                <!-- Image -->
                <div class="h-40 sm:h-48 overflow-hidden bg-gray-200">
                    <?php if ($item['image']): ?>
                        <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['title']) ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                    <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-200 to-gray-300">
                            <i class="fas fa-image text-2xl text-gray-400"></i>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Content -->
                <div class="p-4 sm:p-5">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-xs bg-blue-100 text-blue-600 px-2 py-1 rounded-full font-semibold">
                            <i class="fas fa-tag mr-1"></i>Kategori
                        </span>
                        <span class="text-xs text-gray-500">
                            <i class="fas fa-calendar mr-1"></i><?= formatDate($item['created_at']) ?>
                        </span>
                    </div>

                    <h3 class="text-base sm:text-lg font-bold text-gray-800 mb-2 line-clamp-2 group-hover:text-blue-500 transition-colors">
                        <?= htmlspecialchars($item['title']) ?>
                    </h3>

                    <p class="text-gray-600 text-sm line-clamp-2 mb-4">
                        <?= htmlspecialchars($item['excerpt'] ?? substr($item['content'] ?? '', 0, 100)) ?>
                    </p>

                    <a href="artikel.php?id=<?= $item['id'] ?>" class="inline-block text-blue-500 hover:text-blue-700 font-semibold text-sm transition-colors">
                        Baca Selengkapnya
                        <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="bg-white rounded-lg p-12 text-center">
            <i class="fas fa-newspaper text-4xl text-gray-300 mb-4"></i>
            <p class="text-gray-500">Belum ada berita yang tersedia</p>
        </div>
        <?php endif; ?>

        <!-- Load More Button -->
        <?php if (count($news) >= 9): ?>
        <div class="text-center">
            <button onclick="loadMoreNews()" class="px-8 py-3 border-2 border-blue-500 text-blue-500 font-semibold rounded-lg hover:bg-blue-50 transition-colors">
                <i class="fas fa-plus mr-2"></i>Muat Lebih Banyak
            </button>
        </div>
        <?php endif; ?>
    </section>
</main>

<!-- ==================== FOOTER ==================== -->
<footer class="bg-gray-900 text-gray-300 mt-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 mb-8">
            <!-- About -->
            <div>
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-newspaper text-white"></i>
                    </div>
                    <h3 class="text-lg font-bold text-white">BeritaHub</h3>
                </div>
                <p class="text-sm text-gray-400">
                    Portal berita terpercaya dengan informasi terkini dan berkualitas dari berbagai kategori.
                </p>
            </div>

            <!-- Categories -->
            <div>
                <h4 class="text-white font-semibold mb-4">Kategori</h4>
                <ul class="space-y-2 text-sm">
                    <?php if (!empty($categories)): ?>
                        <?php foreach (array_slice($categories, 0, 5) as $cat): ?>
                            <li>
                                <a href="kategori.php?id=<?= $cat['id'] ?>" class="text-gray-400 hover:text-blue-400 transition-colors">
                                    <?= htmlspecialchars($cat['name']) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Quick Links -->
            <div>
                <h4 class="text-white font-semibold mb-4">Tautan Cepat</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="index.php" class="text-gray-400 hover:text-blue-400 transition-colors">Beranda</a></li>
                    <li><a href="tentang.php" class="text-gray-400 hover:text-blue-400 transition-colors">Tentang Kami</a></li>
                    <li><a href="kebijakan.php" class="text-gray-400 hover:text-blue-400 transition-colors">Kebijakan Privasi</a></li>
                    <li><a href="kontak.php" class="text-gray-400 hover:text-blue-400 transition-colors">Hubungi Kami</a></li>
                </ul>
            </div>

            <!-- Social Media -->
            <div>
                <h4 class="text-white font-semibold mb-4">Ikuti Kami</h4>
                <div class="flex gap-4">
                    <a href="#" class="w-10 h-10 bg-gray-800 hover:bg-blue-600 rounded-lg flex items-center justify-center transition-colors text-lg">
                        <i class="fab fa-facebook"></i>
                    </a>
                    <a href="#" class="w-10 h-10 bg-gray-800 hover:bg-blue-400 rounded-lg flex items-center justify-center transition-colors text-lg">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="w-10 h-10 bg-gray-800 hover:bg-pink-600 rounded-lg flex items-center justify-center transition-colors text-lg">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="w-10 h-10 bg-gray-800 hover:bg-red-600 rounded-lg flex items-center justify-center transition-colors text-lg">
                        <i class="fab fa-youtube"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Divider -->
        <hr class="border-gray-800 mb-6">

        <!-- Copyright -->
        <div class="flex flex-col sm:flex-row justify-between items-center text-sm text-gray-400">
            <p>&copy; 2026 BeritaHub. Semua hak dilindungi.</p>
            <p class="mt-4 sm:mt-0">Dibuat dengan <i class="fas fa-heart text-red-500 mx-1"></i> untuk Anda</p>
        </div>
    </div>
</footer>

<script>
// Toggle Mobile Menu
function toggleMobileMenu() {
    const menu = document.getElementById('mobileMenu');
    menu.classList.toggle('hidden');
}

// Toggle Category Menu (Mobile)
function toggleCategoryMenu() {
    const menu = document.getElementById('mobileCategoryMenu');
    menu.classList.toggle('hidden');
}

// Load More News
function loadMoreNews() {
    // Bisa diintegrasikan dengan AJAX untuk load berita lebih banyak
    alert('Fitur load more akan ditambahkan');
}

// Close mobile menu when clicking on link
document.querySelectorAll('#mobileMenu a').forEach(link => {
    link.addEventListener('click', () => {
        document.getElementById('mobileMenu').classList.add('hidden');
    });
});
</script>

</body>
</html>