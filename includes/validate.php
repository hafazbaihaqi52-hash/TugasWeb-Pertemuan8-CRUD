<?php
/**
 * Validasi & casting input produk. Mengembalikan [$clean, $errors].
 */
function validate_product(array $input, array $categories, array $suppliers): array
{
    $errors = [];

    $name       = trim($input['name'] ?? '');
    $categoryId = (int) ($input['category_id'] ?? 0);
    $supplierId = (int) ($input['supplier_id'] ?? 0);
    $priceRaw   = $input['price'] ?? '';
    $stockRaw   = $input['stock'] ?? '';

    $categoryIds = array_map('intval', array_column($categories, 'id'));
    $supplierIds = array_map('intval', array_column($suppliers, 'id'));

    if ($name === '' || mb_strlen($name) > 150) {
        $errors[] = 'Nama produk wajib diisi (maksimal 150 karakter).';
    }
    if (!in_array($categoryId, $categoryIds, true)) {
        $errors[] = 'Kategori tidak valid.';
    }
    if (!in_array($supplierId, $supplierIds, true)) {
        $errors[] = 'Supplier tidak valid.';
    }
    if (!is_numeric($priceRaw) || (float) $priceRaw < 0) {
        $errors[] = 'Harga harus berupa angka ≥ 0.';
    }
    if (filter_var($stockRaw, FILTER_VALIDATE_INT) === false || (int) $stockRaw < 0) {
        $errors[] = 'Stok harus berupa bilangan bulat ≥ 0.';
    }

    $clean = [
        'name'        => $name,
        'category_id' => $categoryId,
        'supplier_id' => $supplierId,
        'price'       => (float) $priceRaw,
        'stock'       => (int) $stockRaw,
    ];
    return [$clean, $errors];
}
