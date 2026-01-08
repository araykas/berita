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
