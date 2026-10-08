<?php
/**
 * delete.php — DELETE (hanya via POST) dengan TRANSACTION:
 * hapus produk + catat ke activity_logs. Jika salah satu gagal -> rollback semua.
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}
csrf_check();

$pdo = Database::getInstance()->getConnection();
$id  = (int) ($_POST['id'] ?? 0);

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare('SELECT id, name FROM products WHERE id = ? FOR UPDATE');
    $stmt->execute([$id]);
    $product = $stmt->fetch();

    if (!$product) {
        $pdo->rollBack();
        flash_set('error', 'Produk tidak ditemukan atau sudah dihapus.');
        redirect('index.php');
    }

    // Operasi 1: hapus produk
    $stmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
    $stmt->execute([$id]);

    // Operasi 2: catat log aktivitas
    $stmt = $pdo->prepare(
        'INSERT INTO activity_logs (action, product_id, product_name) VALUES (?, ?, ?)'
    );
    $stmt->execute(['delete', $product['id'], $product['name']]);

    $pdo->commit();   // semua sukses -> simpan permanen
    flash_set('success', 'Produk "' . $product['name'] . '" berhasil dihapus.');
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();   // ada yang gagal -> batalkan SEMUA
    }
    error_log('DB Error (delete): ' . $e->getMessage());
    flash_set('error', 'Gagal menghapus produk. Tidak ada perubahan yang disimpan.');
}

redirect('index.php');
