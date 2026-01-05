<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Index</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-100 p-6">

<div class="max-w-3xl mx-auto bg-white rounded-lg shadow p-6">

    <h1 class="text-2xl font-bold mb-4">INDEX (Public Page)</h1>

    <?php if (!empty($_SESSION['user_id'])): ?>
        <!-- USER SUDAH LOGIN -->
        <div class="p-4 mb-4 bg-green-50 border border-green-200 rounded">
            <p class="text-green-700">
                Login sebagai <b><?= htmlspecialchars($_SESSION['username']) ?></b>
                (<?= htmlspecialchars($_SESSION['role']) ?>)
            </p>
        </div>

        <a href="../auth/logout.php"
           class="inline-block bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded">
            Logout
        </a>

    <?php else: ?>
        <!-- USER BELUM LOGIN -->
        <div class="p-4 mb-4 bg-blue-50 border border-blue-200 rounded">
            <p class="text-blue-700">
                Anda belum login. Index tetap bisa diakses.
            </p>
        </div>

        <a href="../auth/login.php"
           class="inline-block bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
            Login
        </a>
    <?php endif; ?>

</div>

</body>
</html>
