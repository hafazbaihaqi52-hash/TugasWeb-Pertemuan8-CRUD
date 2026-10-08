<?php
/**
 * index.php — READ: daftar produk (JOIN 3 tabel) + pencarian + pagination
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';

$pdo     = Database::getInstance()->getConnection();
$pageTitle = 'Daftar Produk';

$q       = trim($_GET['q'] ?? '');
$page    = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 5;

$products = [];
$total    = 0;
$logs     = [];

try {
    // Klausa pencarian — placeholder BERBEDA (:q1,:q2,:q3) karena EMULATE_PREPARES = false
    $where  = '';
    $params = [];
    if ($q !== '') {
        $where  = 'WHERE p.name LIKE :q1 OR c.name LIKE :q2 OR s.name LIKE :q3';
        $like   = '%' . $q . '%';
        $params = [':q1' => $like, ':q2' => $like, ':q3' => $like];
    }

    $from = 'FROM products p
             JOIN categories c ON p.category_id = c.id
             JOIN suppliers  s ON p.supplier_id = s.id';

    // Hitung total untuk pagination
    $stmt = $pdo->prepare("SELECT COUNT(*) $from $where");
    $stmt->execute($params);
    $total = (int) $stmt->fetchColumn();

    $totalPages = max(1, (int) ceil($total / $perPage));
    $page       = min($page, $totalPages);
    $offset     = ($page - 1) * $perPage;

    // Ambil data halaman ini
    $sql = "SELECT p.id, p.name, p.price, p.stock,
                   c.name AS category, s.name AS supplier
            $from $where
            ORDER BY p.name
            LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val, PDO::PARAM_STR);
    }
    $stmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset,  PDO::PARAM_INT);
    $stmt->execute();
    $products = $stmt->fetchAll();

    // Log aktivitas terakhir (bonus)
    $logs = $pdo->query('SELECT action, product_name, created_at
                         FROM activity_logs ORDER BY id DESC LIMIT 5')->fetchAll();
} catch (PDOException $e) {
    error_log('DB Error (index): ' . $e->getMessage());
    flash_set('error', 'Terjadi kesalahan database saat memuat data.');
    $totalPages = 1;
}

include __DIR__ . '/includes/header.php';
?>

<div class="page-head">
  <h1>Daftar Produk</h1>
  <a href="create.php" class="btn btn-primary">➕ Tambah Produk</a>
</div>

<form method="GET" class="search">
  <input type="search" name="q" placeholder="Cari produk, kategori, atau supplier…" value="<?= e($q) ?>">
  <button class="btn btn-primary" type="submit">🔍 Cari</button>
  <?php if ($q !== ''): ?><a href="index.php" class="btn btn-ghost">Reset</a><?php endif; ?>
</form>

<div class="card table-wrap">
  <table>
    <thead>
      <tr>
        <th>#</th><th>Nama Produk</th><th>Kategori</th><th>Supplier</th>
        <th class="num">Harga</th><th class="num">Stok</th><th>Aksi</th>
      </tr>
    </thead>
    <tbody>
    <?php if (!$products): ?>
      <tr><td colspan="7" class="empty">Belum ada data<?= $q !== '' ? ' yang cocok dengan pencarian' : '' ?>.</td></tr>
    <?php endif; ?>
    <?php foreach ($products as $i => $p): ?>
      <tr>
        <td><?= ($page - 1) * $perPage + $i + 1 ?></td>
        <td><?= e($p['name']) ?></td>
        <td><span class="badge"><?= e($p['category']) ?></span></td>
        <td><?= e($p['supplier']) ?></td>
        <td class="num"><?= e(rupiah($p['price'])) ?></td>
        <td class="num <?= (int) $p['stock'] <= 10 ? 'low' : '' ?>"><?= (int) $p['stock'] ?></td>
        <td class="aksi">
          <a href="edit.php?id=<?= (int) $p['id'] ?>" class="btn btn-sm">✏️ Edit</a>
          <form method="POST" action="delete.php" class="inline"
                onsubmit="return confirm('Hapus produk &quot;<?= e(addslashes($p['name'])) ?>&quot;?')">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
            <button type="submit" class="btn btn-sm btn-danger">🗑️ Hapus</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<div class="pagination">
  <span class="muted">Total <?= $total ?> produk · Halaman <?= $page ?> dari <?= $totalPages ?></span>
  <div>
    <?php for ($n = 1; $n <= $totalPages; $n++): ?>
      <a class="page <?= $n === $page ? 'active' : '' ?>"
         href="?<?= e(http_build_query(['q' => $q, 'page' => $n])) ?>"><?= $n ?></a>
    <?php endfor; ?>
  </div>
</div>

<?php if ($logs): ?>
<section class="card log">
  <h2>🕒 Log Aktivitas Terakhir</h2>
  <ul>
    <?php foreach ($logs as $l): ?>
      <li><strong><?= e(strtoupper($l['action'])) ?></strong> — <?= e($l['product_name']) ?>
          <span class="muted">(<?= e($l['created_at']) ?>)</span></li>
    <?php endforeach; ?>
  </ul>
</section>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
