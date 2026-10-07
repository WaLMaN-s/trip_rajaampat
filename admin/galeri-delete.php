<?php
require_once '../includes/config.php';
require_once '../includes/session.php';
require_once '../includes/function.php';

require_admin();

$id = $_GET['id'] ?? 0;

// Get photo
$stmt = $pdo->prepare("SELECT * FROM galeri WHERE id = ?");
$stmt->execute([$id]);
$foto = $stmt->fetch();

if (!$foto) {
    alert('Foto tidak ditemukan!', 'danger');
    redirect('galeri-list.php');
}

// Delete photo file
if ($foto['foto'] && file_exists(UPLOAD_DIR . 'galeri/' . $foto['foto'])) {
    unlink(UPLOAD_DIR . 'galeri/' . $foto['foto']);
}

// Delete from database
$stmt = $pdo->prepare("DELETE FROM galeri WHERE id = ?");
if ($stmt->execute([$id])) {
    alert('Foto berhasil dihapus dari galeri!', 'success');
} else {
    alert('Gagal menghapus foto!', 'danger');
}

redirect('galeri-list.php');