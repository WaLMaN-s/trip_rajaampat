<?php
require_once '../includes/config.php';
require_once '../includes/session.php';
require_once '../includes/function.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pesanan_id = $_POST['pesanan_id'] ?? 0;
    $keterangan = $_POST['keterangan'] ?? '';
    
    // Update payment status
    $stmt = $pdo->prepare("
        UPDATE pembayaran 
        SET status = 'invalid', 
            tanggal_verifikasi = NOW(), 
            verified_by = ?, 
            keterangan = ? 
        WHERE pesanan_id = ?
    ");
    
    if ($stmt->execute([get_user_id(), $keterangan, $pesanan_id])) {
        // Update order status
        $stmt = $pdo->prepare("UPDATE pesanan SET status = 'cancelled' WHERE id = ?");
        $stmt->execute([$pesanan_id]);
        
        alert('Pembayaran ditolak!', 'success');
    } else {
        alert('Gagal menolak pembayaran!', 'danger');
    }
}

redirect('pembayaran-list.php');