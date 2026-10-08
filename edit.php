<?php
/**
 * edit.php — UPDATE: form ter-isi data lama + UPDATE (prepared statement)
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/includes/validate.php';

$pdo = Database::getInstance()->getConnection();
$pageTitle = 'Edit Produk';

$id = (int) ($_GET['id'] ?? 0);

try {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$id]);
    $product = $stmt->fetch();

    if (!$product) {
        flash_set('error', 'Produk tidak ditemukan.');
        redirect('index.php');
    }

    $categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
    $suppliers  = $pdo->query('SELECT id, name FROM suppliers ORDER BY name')->fetchAll();
} catch (PDOException $e) {
    error_log('DB Error (edit/load): ' . $e->getMessage());
    flash_set('error', 'Gagal memuat data produk.');
    redirect('index.php');
}

$errors = [];
$values = [   // form pre-filled
    'name'        => $product['name'],
    'category_id' => $product['category_id'],
    'supplier_id' => $product['supplier_id'],
    'price'       => (float) $product['price'],
    'stock'       => $product['stock'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    [$clean, $errors] = validate_product($_POST, $categories, $suppliers);
    $values = array_merge($values, $clean);

    if (!$errors) {
        try {
            $stmt = $pdo->prepare(
                'UPDATE products
                    SET name = ?, category_id = ?, supplier_id = ?, price = ?, stock = ?
                  WHERE id = ?'
            );
            $stmt->execute([
                $clean['name'], $clean['category_id'], $clean['supplier_id'],
                $clean['price'], $clean['stock'], $id,
            ]);
            flash_set('success', 'Produk "' . $clean['name'] . '" berhasil diperbarui.');
            redirect('index.php');
        } catch (PDOException $e) {
            error_log('DB Error (edit): ' . $e->getMessage());
            $errors[] = 'Gagal memperbarui produk. Silakan coba lagi.';
        }
    }
}

$submitLabel = '💾 Simpan Perubahan';
include __DIR__ . '/includes/header.php';
?>
<div class="page-head"><h1>Edit Produk</h1></div>
<?php include __DIR__ . '/includes/product_form.php'; ?>
<?php include __DIR__ . '/includes/footer.php'; ?>
