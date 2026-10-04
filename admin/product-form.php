<?php
require_once __DIR__ . '/../assets/includes/admin-guard.php';

$id = (int)($_GET['id'] ?? 0);
$product = $id ? db_one('SELECT * FROM products WHERE id = ?', [$id]) : null;
if ($id && !$product) {
    flash('error', 'That product no longer exists.');
    redirect('products.php');
}

$form = $product ?: [
    'name' => '', 'category_id' => '', 'description' => '', 'price' => '699', 'stock' => '20',
    'pages' => '24', 'size' => '8.5" x 11"', 'paper' => 'Premium Glossy', 'image' => '',
    'is_featured' => 0, 'is_active' => 1,
];
$gallery = $id ? db_all('SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order, id', [$id]) : [];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST) && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
        flash('error', 'Those images are too large to upload together. Try fewer or smaller images.');
        redirect('product-form.php' . ($id ? '?id=' . $id : ''));
    }
    csrf_check();

    $form['name']        = trim($_POST['name'] ?? '');
    $form['category_id'] = (int)($_POST['category_id'] ?? 0);
    $form['description'] = trim($_POST['description'] ?? '');
    $form['price']       = trim($_POST['price'] ?? '');
    $form['stock']       = trim($_POST['stock'] ?? '');
    $form['pages']       = trim($_POST['pages'] ?? '');
    $form['size']        = trim($_POST['size'] ?? '');
    $form['paper']       = trim($_POST['paper'] ?? '');
    $form['is_featured'] = !empty($_POST['is_featured']) ? 1 : 0;
    $form['is_active']   = !empty($_POST['is_active']) ? 1 : 0;

    if ($form['name'] === '' || mb_strlen($form['name']) > 120) $errors['name'] = 'Enter a name (up to 120 characters).';
    if ($form['category_id'] && !db_value('SELECT COUNT(*) FROM categories WHERE id = ?', [$form['category_id']])) $errors['category_id'] = 'Choose a category.';
    if ($form['description'] === '') $errors['description'] = 'Write a short description for customers.';
    if (!is_numeric($form['price']) || (float)$form['price'] <= 0 || (float)$form['price'] > 1000000) $errors['price'] = 'Enter a price above 0.';
    if (!ctype_digit($form['stock']) || (int)$form['stock'] > 100000) $errors['stock'] = 'Enter a whole number, 0 or more.';
    if (!ctype_digit($form['pages']) || (int)$form['pages'] < 1 || (int)$form['pages'] > 500) $errors['pages'] = 'Enter the number of pages.';
    if ($form['size'] === '' || mb_strlen($form['size']) > 40) $errors['size'] = 'Enter the size, e.g. 8.5" x 11".';
    if ($form['paper'] === '' || mb_strlen($form['paper']) > 60) $errors['paper'] = 'Enter the paper type.';

    $mainFile = files_list($_FILES['image'] ?? null)[0] ?? null;
    if (!$id && !$mainFile) {
        $errors['image'] = 'Upload a cover image.';
    }

    if (!$errors) {
        $newFiles = [];
        try {
            $newImage = null;
            if ($mainFile) {
                $newImage = 'uploads/products/' . store_uploaded_image($mainFile, 'uploads/products');
                $newFiles[] = $newImage;
            }
            $galleryNew = [];
            foreach (files_list($_FILES['gallery'] ?? null) as $f) {
                $path = 'uploads/products/' . store_uploaded_image($f, 'uploads/products');
                $newFiles[] = $path;
                $galleryNew[] = $path;
            }

            $values = [
                $form['category_id'] ?: null, $form['name'], $form['description'], (float)$form['price'], (int)$form['stock'],
                (int)$form['pages'], $form['size'], $form['paper'], $form['is_featured'], $form['is_active'],
            ];

            if ($id) {
                db_exec('UPDATE products SET category_id = ?, name = ?, description = ?, price = ?, stock = ?, pages = ?, size = ?, paper = ?, is_featured = ?, is_active = ? WHERE id = ?',
                    array_merge($values, [$id]));
                if ($newImage) {
                    db_exec('UPDATE products SET image = ? WHERE id = ?', [$newImage, $id]);
                    delete_upload($product['image']);
                }
            } else {
                $id = db_exec('INSERT INTO products (category_id, name, description, price, stock, pages, size, paper, is_featured, is_active, image)
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', array_merge($values, [$newImage]));
            }

            // Gallery: remove ticked images, add new ones at the end
            foreach ((array)($_POST['remove_gallery'] ?? []) as $gid) {
                $g = db_one('SELECT * FROM product_images WHERE id = ? AND product_id = ?', [(int)$gid, $id]);
                if ($g) {
                    db_exec('DELETE FROM product_images WHERE id = ?', [(int)$g['id']]);
                    delete_upload($g['image']);
                }
            }
            $nextSort = (int)db_value('SELECT COALESCE(MAX(sort_order), 0) FROM product_images WHERE product_id = ?', [$id]);
            foreach ($galleryNew as $path) {
                db_exec('INSERT INTO product_images (product_id, image, sort_order) VALUES (?, ?, ?)', [$id, $path, ++$nextSort]);
            }

            flash('success', $form['name'] . ($product ? ' is updated.' : ' is added to the shop.'));
            redirect('products.php');
        } catch (RuntimeException $e) {
            foreach ($newFiles as $f) {
                delete_upload($f);
            }
            $errors['image'] = $e->getMessage();
        }
    }
}

$categories = categories_all();
$title = $product ? 'Edit ' . $product['name'] : 'Add product';

function err(array $errors, string $key): string {
    return isset($errors[$key]) ? '<p class="hint" style="color: var(--bad-ink);">' . e($errors[$key]) . '</p>' : '';
}

admin_header($title, 'products');
?>

<p><a href="products.php">&larr; All products</a></p>
<?php if ($errors): ?>
    <div class="alert alert-error" style="margin-bottom: 16px;"><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i><span>Please fix the fields marked below.</span></div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="grid-2">
    <?php echo csrf_field(); ?>
    <div class="panel">
        <h2>Details</h2>
        <div class="form-grid">
            <div class="field span-2">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" value="<?php echo e($form['name']); ?>" required maxlength="120">
                <?php echo err($errors, 'name'); ?>
            </div>
            <div class="field">
                <label for="category_id">Category</label>
                <select id="category_id" name="category_id">
                    <option value="">No category</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?php echo (int)$c['id']; ?>" <?php echo (int)$form['category_id'] === (int)$c['id'] ? 'selected' : ''; ?>><?php echo e($c['name']); ?></option>
                    <?php endforeach; ?>
                </select>
                <?php echo err($errors, 'category_id'); ?>
            </div>
            <div class="field span-3">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="5" required><?php echo e($form['description']); ?></textarea>
                <?php echo err($errors, 'description'); ?>
            </div>
            <div class="field">
                <label for="price">Price (Rs.)</label>
                <input type="number" id="price" name="price" value="<?php echo e($form['price']); ?>" min="1" step="0.01" required>
                <?php echo err($errors, 'price'); ?>
            </div>
            <div class="field">
                <label for="stock">Stock</label>
                <input type="number" id="stock" name="stock" value="<?php echo e($form['stock']); ?>" min="0" step="1" required>
                <?php echo err($errors, 'stock'); ?>
            </div>
            <div class="field">
                <label for="pages">Pages</label>
                <input type="number" id="pages" name="pages" value="<?php echo e($form['pages']); ?>" min="1" step="1" required>
                <?php echo err($errors, 'pages'); ?>
            </div>
            <div class="field">
                <label for="size">Size</label>
                <input type="text" id="size" name="size" value="<?php echo e($form['size']); ?>" required maxlength="40">
                <?php echo err($errors, 'size'); ?>
            </div>
            <div class="field span-2">
                <label for="paper">Paper</label>
                <input type="text" id="paper" name="paper" value="<?php echo e($form['paper']); ?>" required maxlength="60">
                <?php echo err($errors, 'paper'); ?>
            </div>
        </div>
        <label class="check"><input type="checkbox" name="is_active" value="1" <?php echo (int)$form['is_active'] ? 'checked' : ''; ?>> Visible in the shop</label>
        <label class="check"><input type="checkbox" name="is_featured" value="1" <?php echo (int)$form['is_featured'] ? 'checked' : ''; ?>> Feature on the home page</label>
    </div>

    <div class="stack">
        <div class="panel">
            <h2>Cover image</h2>
            <?php if (!empty($form['image'])): ?>
                <div class="img-current">
                    <img src="<?php echo e(img_url($form['image'])); ?>" alt="Current cover">
                    <p class="muted small">Upload a new image below to replace this one.</p>
                </div>
            <?php endif; ?>
            <div class="field">
                <label for="image"><?php echo $product ? 'Replace cover' : 'Cover image'; ?></label>
                <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp" <?php echo $product ? '' : 'required'; ?>>
                <p class="hint">JPG, PNG or WebP, up to <?php echo MAX_UPLOAD_MB; ?> MB. A tall 8.5 x 11 image looks best.</p>
                <?php echo err($errors, 'image'); ?>
            </div>
        </div>

        <div class="panel">
            <h2>Extra photos</h2>
            <?php if ($gallery): ?>
                <div class="gallery-grid">
                    <?php foreach ($gallery as $g): ?>
                        <figure>
                            <img src="<?php echo e(img_url($g['image'])); ?>" alt="">
                            <figcaption><label class="check" style="font-weight: 500; margin: 0;"><input type="checkbox" name="remove_gallery[]" value="<?php echo (int)$g['id']; ?>"> Remove</label></figcaption>
                        </figure>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="muted small">Inside pages or angles shown as thumbnails on the product page.</p>
            <?php endif; ?>
            <div class="field" style="margin: 0;">
                <label for="gallery">Add photos</label>
                <input type="file" id="gallery" name="gallery[]" accept="image/jpeg,image/png,image/webp" multiple>
            </div>
        </div>

        <div class="btn-row">
            <button type="submit" class="btn btn-primary"><?php echo $product ? 'Save changes' : 'Add product'; ?></button>
            <a href="products.php" class="btn btn-ghost">Cancel</a>
            <?php if ($product): ?><a href="../product-details.php?id=<?php echo (int)$product['id']; ?>" class="btn btn-ghost" target="_blank" rel="noopener">View in shop</a><?php endif; ?>
        </div>
    </div>
</form>

<?php admin_footer(); ?>
