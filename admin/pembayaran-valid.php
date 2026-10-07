<?php
require_once '../includes/config.php';
require_once '../includes/session.php';
require_once '../includes/function.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pesanan_id = $_POST['pesanan_id'] ?? 0;
    
    // Update payment status
    $stmt = $pdo->prepare("
        UPDATE pembayaran 
        SET status = 'valid', 
            tanggal_verifikasi = NOW(), 
            verified_by = ? 
        WHERE pesanan_id = ?
    ");
    
    if ($stmt->execute([get_user_id(), $pesanan_id])) {
        // Update order status
        $stmt = $pdo->prepare("UPDATE pesanan SET status = 'paid' WHERE id = ?");
        $stmt->execute([$pesanan_id]);
        
        alert('Pembayaran berhasil diverifikasi!', 'success');
    } else {
        alert('Gagal memverifikasi pembayaran!', 'danger');
    }
}

redirect('pembayaran-list.php');