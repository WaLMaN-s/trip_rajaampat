<?php
// Sidebar admin bersama - di-include dari tiap halaman admin.
// $current dipakai untuk highlight menu yang sedang aktif.
$current = basename($_SERVER['SCRIPT_NAME']);
$nav_items = [
    'dashboard.php'        => ['icon' => '📊', 'label' => 'Dashboard'],
    'paket-list.php'       => ['icon' => '📦', 'label' => 'Paket Wisata'],
    'galeri-list.php'      => ['icon' => '📸', 'label' => 'Galeri'],
    'user-list.php'        => ['icon' => '👥', 'label' => 'User'],
    'pembayaran-list.php'  => ['icon' => '💳', 'label' => 'Pembayaran'],
    'cancelled-order.php'  => ['icon' => '🗑️', 'label' => 'Log Batal'],
];
// Halaman detail/form ikut menyorot menu induknya di sidebar.
$active_map = [
    'paket-add.php' => 'paket-list.php', 'paket-edit.php' => 'paket-list.php',
    'galeri-add.php' => 'galeri-list.php', 'galeri-edit.php' => 'galeri-list.php',
    'user-detail.php' => 'user-list.php',
    'pembayaran-detail.php' => 'pembayaran-list.php',
];
$active = $active_map[$current] ?? $current;
?>
<aside class="admin-sidebar">
    <a href="dashboard.php" class="admin-sidebar-brand">🏝️ <span><?= SITE_NAME ?></span></a>
    <nav class="admin-nav">
        <ul>
            <?php foreach ($nav_items as $href => $item): ?>
                <li>
                    <a href="<?= $href ?>" class="<?= $active === $href ? 'active' : '' ?>">
                        <span class="admin-nav-icon"><?= $item['icon'] ?></span> <?= $item['label'] ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>
    <a href="logout.php" class="admin-sidebar-logout">🚪 Logout</a>
</aside>
