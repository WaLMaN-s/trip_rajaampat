<?php
require_once '../includes/config.php';
require_once '../includes/session.php';
require_once '../includes/function.php';

require_admin();

$id = $_GET['id'] ?? 0;

// Get package
$stmt = $pdo->prepare("SELECT * FROM paket_wisata WHERE id = ?");
$stmt->execute([$id]);
$package = $stmt->fetch();

if (!$package) {
    alert('Paket tidak ditemukan!', 'danger');
    redirect('paket-list.php');
}

// Check if package has active orders
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM pesanan WHERE paket_id = ? AND status != 'cancelled'");
$stmt->execute([$id]);
$active_orders = $stmt->fetch()['total'];

if ($active_orders > 0) {
    alert("Tidak dapat menghapus paket ini karena masih ada $active_orders pesanan aktif!", 'danger');
    redirect('paket-list.php');
}

// Delete photo file
if ($package['foto'] && file_exists(UPLOAD_DIR . 'paket/' . $package['foto'])) {
    unlink(UPLOAD_DIR . 'paket/' . $package['foto']);
}

// Delete package
$stmt = $pdo->prepare("DELETE FROM paket_wisata WHERE id = ?");
if ($stmt->execute([$id])) {
    alert('Paket wisata berhasil dihapus!', 'success');
} else {
    alert('Gagal menghapus paket wisata!', 'danger');
}

redirect('paket-list.php');