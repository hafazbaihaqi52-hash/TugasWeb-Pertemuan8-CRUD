<?php
/**
 * Partial form produk (dipakai create.php & edit.php)
 * Variabel yang dibutuhkan: $values, $categories, $suppliers, $errors, $submitLabel
 */
?>
<form method="POST" class="card form" novalidate>
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

  <?php if ($errors): ?>
    <div class="alert alert-error">
      <ul>
        <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <label for="name">Nama Produk</label>
  <input type="text" id="name" name="name" maxlength="150" required value="<?= e($values['name']) ?>">

  <div class="row">
    <div>
      <label for="category_id">Kategori</label>
      <select id="category_id" name="category_id" required>
        <option value="">-- Pilih kategori --</option>
        <?php foreach ($categories as $c): ?>
          <option value="<?= (int) $c['id'] ?>" <?= (int) $values['category_id'] === (int) $c['id'] ? 'selected' : '' ?>>
            <?= e($c['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label for="supplier_id">Supplier</label>
      <select id="supplier_id" name="supplier_id" required>
        <option value="">-- Pilih supplier --</option>
        <?php foreach ($suppliers as $s): ?>
          <option value="<?= (int) $s['id'] ?>" <?= (int) $values['supplier_id'] === (int) $s['id'] ? 'selected' : '' ?>>
            <?= e($s['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <div class="row">
    <div>
      <label for="price">Harga (Rp)</label>
      <input type="number" id="price" name="price" min="0" step="100" required value="<?= e($values['price']) ?>">
    </div>
    <div>
      <label for="stock">Stok</label>
      <input type="number" id="stock" name="stock" min="0" step="1" required value="<?= e($values['stock']) ?>">
    </div>
  </div>

  <div class="actions">
    <button type="submit" class="btn btn-primary"><?= e($submitLabel) ?></button>
    <a href="index.php" class="btn btn-ghost">Batal</a>
  </div>
</form>
