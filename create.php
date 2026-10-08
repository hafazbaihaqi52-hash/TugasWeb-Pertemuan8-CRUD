<?php
/**
 * create.php — CREATE: form tambah produk + INSERT (prepared statement)
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/includes/validate.php';

$pdo = Database::getInstance()->getConnection();
$pageTitle = 'Tambah Produk';

try {
    $categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
    $suppliers  = $pdo->query('SELECT id, name FROM suppliers ORDER BY name')->fetchAll();
} catch (PDOException $e) {
    error_log('DB Error (create/load): ' . $e->getMessage());
    flash_set('error', 'Gagal memuat data kategori/supplier.');
    redirect('index.php');
}

$errors = [];
$values = ['name' => '', 'category_id' => '', 'supplier_id' => '', 'price' => '', 'stock' => 0];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    [$clean, $errors] = validate_product($_POST, $categories, $suppliers);
    $values = array_merge($values, $clean);

    if (!$errors) {
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO products (name, category_id, supplier_id, price, stock)
                 VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $clean['name'], $clean['category_id'], $clean['supplier_id'],
                $clean['price'], $clean['stock'],
            ]);
            flash_set('success', 'Produk "' . $clean['name'] . '" berhasil ditambahkan.');
            redirect('index.php');           // Post/Redirect/Get
        } catch (PDOException $e) {
            error_log('DB Error (create): ' . $e->getMessage());
            $errors[] = 'Gagal menyimpan produk. Silakan coba lagi.';
        }
    }
}

$submitLabel = '💾 Simpan Produk';
include __DIR__ . '/includes/header.php';
?>
<div class="page-head"><h1>Tambah Produk</h1></div>
<?php include __DIR__ . '/includes/product_form.php'; ?>
<?php include __DIR__ . '/includes/footer.php'; ?>
