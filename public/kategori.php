Logic saat user mau komentar (WAJIB)
if ($user['banned_until'] !== null && strtotime($user['banned_until']) > time()) {
    die("Kamu sedang dibanned dan tidak bisa komentar sementara waktu.");
}