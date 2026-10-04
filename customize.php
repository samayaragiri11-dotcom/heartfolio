<?php
require_once 'assets/includes/bootstrap.php';

$product = product_find((int)($_GET['id'] ?? 0));
if (!$product) {
    flash('error', 'Pick a magazine from the shop to customize it.');
    redirect('shop.php');
}
$pid = (int)$product['id'];

// Edit mode: load an existing customized item from the cart
$editItem = null;
$existingCover = null;
$existingPages = [];
if (!empty($_GET['edit'])) {
    $editItem = cart_find((int)$_GET['edit']);
    if (!$editItem || !(int)$editItem['is_custom'] || (int)$editItem['product_id'] !== $pid) {
        flash('error', 'That customized magazine is no longer in your cart.');
        redirect('cart.php');
    }
    foreach (db_all('SELECT id, kind FROM custom_photos WHERE cart_item_id = ? ORDER BY kind, position, id', [(int)$editItem['id']]) as $ph) {
        if ($ph['kind'] === 'cover') {
            $existingCover = ['id' => (int)$ph['id'], 'url' => 'photo.php?id=' . (int)$ph['id']];
        } else {
            $existingPages[] = ['id' => (int)$ph['id'], 'url' => 'photo.php?id=' . (int)$ph['id']];
        }
    }
}

$stockLeft = (int)$product['stock'] - cart_qty_of_product($pid, $editItem ? (int)$editItem['id'] : 0);
if ($stockLeft < 1) {
    flash('error', 'There are no more copies of ' . $product['name'] . ' available right now.');
    redirect('product-details.php?id=' . $pid);
}

$pageTitle = ($editItem ? 'Edit ' : 'Customize ') . $product['name'];
$active = 'shop';
include 'assets/includes/navbar.php';
?>

<div class="wrap page">
    <?php echo flash_render(); ?>
    <nav class="crumbs" aria-label="Breadcrumb">
        <a href="shop.php">Shop</a> / <a href="product-details.php?id=<?php echo $pid; ?>"><?php echo e($product['name']); ?></a> / <?php echo $editItem ? 'Edit' : 'Customize'; ?>
    </nav>
    <div class="page-head">
        <h1><?php echo $editItem ? 'Edit your ' : 'Customize '; ?><?php echo e($product['name']); ?></h1>
        <p>Add your own photos and words. The cover preview updates as you go.</p>
    </div>

    <div class="cz-grid">
        <div class="cz-preview">
            <div class="cz-cover" aria-label="Cover preview">
                <img id="pv-img" alt="" hidden>
                <div class="cz-empty" id="pv-empty">
                    <i class="fa-regular fa-image" aria-hidden="true"></i>
                    Your cover photo appears here
                </div>
                <div class="cz-cover-text">
                    <p class="cz-cover-title" id="pv-title"><?php echo e(($editItem['custom_title'] ?? '') ?: $product['name']); ?></p>
                    <div class="cz-cover-names" id="pv-names"><?php echo e($editItem['custom_names'] ?? ''); ?></div>
                </div>
            </div>
            <p class="muted small" style="margin-top: 14px;">We print exactly what you upload, so use clear, well-lit photos.</p>
        </div>

        <form id="cz-form" action="customize-action.php" method="post" enctype="multipart/form-data" novalidate>
            <?php echo csrf_field(); ?>
            <input type="hidden" name="product_id" value="<?php echo $pid; ?>">
            <?php if ($editItem): ?><input type="hidden" name="item_id" value="<?php echo (int)$editItem['id']; ?>"><?php endif; ?>

            <div class="cz-step">
                <h3>Cover photo</h3>
                <p>Required. A tall (portrait) photo fills the cover best.</p>
                <label class="drop" id="cover-drop">
                    <i class="fa-solid fa-upload" aria-hidden="true"></i>
                    <span id="cover-label">Choose a cover photo or drop it here</span>
                    <input type="file" id="cover-input" name="cover" accept="image/jpeg,image/png,image/webp">
                </label>
            </div>

            <div class="cz-step">
                <h3>Inside pages</h3>
                <p>Up to <?php echo MAX_PAGE_PHOTOS; ?> photos, printed in the order shown.</p>
                <label class="drop" id="pages-drop">
                    <i class="fa-solid fa-images" aria-hidden="true"></i>
                    <span>Add photos or drop them here</span>
                    <input type="file" id="pages-input" name="pages[]" accept="image/jpeg,image/png,image/webp" multiple>
                </label>
                <div class="cz-pages" id="pages-grid"></div>
                <p class="muted small" id="pages-count" style="margin-top: 8px;"></p>
            </div>

            <div class="cz-step">
                <h3>Your words</h3>
                <p>Shown on the cover and the first page.</p>
                <div class="field">
                    <label for="cz-title">Magazine title</label>
                    <input type="text" id="cz-title" name="title" maxlength="40" placeholder="<?php echo e($product['name']); ?>" value="<?php echo e($editItem['custom_title'] ?? ''); ?>">
                    <p class="hint">Up to 40 characters. Leave empty to keep "<?php echo e($product['name']); ?>".</p>
                </div>
                <div class="field">
                    <label for="cz-names">Names</label>
                    <input type="text" id="cz-names" name="names" maxlength="60" placeholder="e.g. Aashma &amp; Priya" value="<?php echo e($editItem['custom_names'] ?? ''); ?>">
                </div>
                <div class="field">
                    <label for="cz-message">Message for the first page</label>
                    <textarea id="cz-message" name="message" rows="4" maxlength="300" placeholder="A note they will keep forever"><?php echo e($editItem['custom_message'] ?? ''); ?></textarea>
                    <p class="hint"><span id="msg-count">0</span>/300 characters</p>
                </div>
            </div>

            <div class="cz-step" style="border-bottom: 0;">
                <div id="cz-error" class="alert alert-error" role="alert" hidden></div>
                <div class="cz-actions">
                    <div class="qty">
                        <button type="button" data-step="-1" aria-label="Decrease quantity">&minus;</button>
                        <input type="number" name="quantity" id="cz-qty" value="<?php echo (int)($editItem['quantity'] ?? 1); ?>" min="1" max="<?php echo min(99, $stockLeft); ?>" aria-label="Quantity">
                        <button type="button" data-step="1" aria-label="Increase quantity">+</button>
                    </div>
                    <span class="pd-price" style="margin: 0; font-size: 1.4rem;"><?php echo price($product['price']); ?></span>
                </div>
                <div class="cz-actions">
                    <button type="submit" class="btn btn-primary" id="cz-save"><?php echo $editItem ? 'Save changes' : 'Add to cart'; ?></button>
                    <a href="<?php echo $editItem ? 'cart.php' : 'product-details.php?id=' . $pid; ?>" class="btn btn-ghost">Cancel</a>
                </div>
                <div class="progress" id="cz-progress" aria-hidden="true"><span></span></div>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    'use strict';
    var MAX_PAGES = <?php echo MAX_PAGE_PHOTOS; ?>;
    var PRODUCT_NAME = <?php echo json_encode($product['name'], JSON_HEX_TAG | JSON_HEX_AMP); ?>;
    var ACCEPTED = ['image/jpeg', 'image/png', 'image/webp'];

    // cover: {id, url} for a saved photo, or {blob, url} for a new one
    var cover = <?php echo json_encode($existingCover); ?>;
    var pages = <?php echo json_encode($existingPages); ?>;

    var $ = function (id) { return document.getElementById(id); };
    var form = $('cz-form');
    var errorBox = $('cz-error');

    function showError(msg) {
        errorBox.textContent = msg || '';
        errorBox.hidden = !msg;
    }

    // Shrinks a photo in the browser before upload: faster upload, smaller files
    function prepare(file, maxSide) {
        return new Promise(function (resolve, reject) {
            if (ACCEPTED.indexOf(file.type) === -1) {
                reject(new Error(file.name + ' is not a JPG, PNG or WebP image.'));
                return;
            }
            if (file.size > 25 * 1024 * 1024) {
                reject(new Error(file.name + ' is larger than 25 MB.'));
                return;
            }
            var url = URL.createObjectURL(file);
            var img = new Image();
            img.onload = function () {
                var scale = Math.min(1, maxSide / Math.max(img.naturalWidth, img.naturalHeight));
                var canvas = document.createElement('canvas');
                canvas.width = Math.max(1, Math.round(img.naturalWidth * scale));
                canvas.height = Math.max(1, Math.round(img.naturalHeight * scale));
                var ctx = canvas.getContext('2d');
                ctx.fillStyle = '#fff';
                ctx.fillRect(0, 0, canvas.width, canvas.height);
                ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                URL.revokeObjectURL(url);
                canvas.toBlob(function (blob) {
                    if (!blob) { reject(new Error(file.name + ' could not be processed.')); return; }
                    resolve({ blob: blob, url: URL.createObjectURL(blob) });
                }, 'image/jpeg', 0.88);
            };
            img.onerror = function () {
                URL.revokeObjectURL(url);
                reject(new Error(file.name + ' could not be opened as an image.'));
            };
            img.src = url;
        });
    }

    function renderCover() {
        var img = $('pv-img');
        if (cover) {
            img.src = cover.url;
            img.hidden = false;
            $('pv-empty').hidden = true;
            $('cover-label').textContent = 'Change cover photo';
        } else {
            img.hidden = true;
            $('pv-empty').hidden = false;
            $('cover-label').textContent = 'Choose a cover photo or drop it here';
        }
    }

    function renderPages() {
        var grid = $('pages-grid');
        grid.innerHTML = '';
        pages.forEach(function (p, i) {
            var div = document.createElement('div');
            div.className = 'cz-page';
            div.innerHTML = '<img alt="Page ' + (i + 1) + ' photo"><span class="num">' + (i + 1) + '</span>' +
                '<button type="button" data-index="' + i + '" aria-label="Remove page ' + (i + 1) + ' photo">&times;</button>';
            div.querySelector('img').src = p.url;
            grid.appendChild(div);
        });
        $('pages-count').textContent = pages.length + ' of ' + MAX_PAGES + ' photos';
        $('pages-drop').hidden = pages.length >= MAX_PAGES;
    }

    function renderText() {
        $('pv-title').textContent = $('cz-title').value.trim() || PRODUCT_NAME;
        $('pv-names').textContent = $('cz-names').value.trim();
        $('msg-count').textContent = $('cz-message').value.length;
    }

    function setCover(file) {
        if (!file) return;
        showError('');
        prepare(file, 2000).then(function (res) {
            cover = res;
            renderCover();
        }).catch(function (err) { showError(err.message); });
    }

    function addPages(fileList) {
        showError('');
        var files = Array.prototype.slice.call(fileList);
        var room = MAX_PAGES - pages.length;
        if (files.length > room) {
            showError('Only ' + MAX_PAGES + ' page photos fit. The first ' + room + ' were added.');
            files = files.slice(0, room);
        }
        files.reduce(function (chain, file) {
            return chain.then(function () {
                return prepare(file, 1600).then(function (res) { pages.push(res); })
                    .catch(function (err) { showError(err.message); });
            });
        }, Promise.resolve()).then(renderPages);
    }

    $('cover-input').addEventListener('change', function () { setCover(this.files[0]); this.value = ''; });
    $('pages-input').addEventListener('change', function () { addPages(this.files); this.value = ''; });

    $('pages-grid').addEventListener('click', function (e) {
        var btn = e.target.closest('button[data-index]');
        if (!btn) return;
        pages.splice(parseInt(btn.getAttribute('data-index'), 10), 1);
        renderPages();
    });

    [['cover-drop', function (f) { setCover(f[0]); }], ['pages-drop', addPages]].forEach(function (pair) {
        var zone = $(pair[0]);
        zone.addEventListener('dragover', function (e) { e.preventDefault(); zone.classList.add('dragging'); });
        zone.addEventListener('dragleave', function () { zone.classList.remove('dragging'); });
        zone.addEventListener('drop', function (e) {
            e.preventDefault();
            zone.classList.remove('dragging');
            if (e.dataTransfer.files.length) pair[1](e.dataTransfer.files);
        });
    });

    ['cz-title', 'cz-names', 'cz-message'].forEach(function (id) { $(id).addEventListener('input', renderText); });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        showError('');
        if (!cover) {
            showError('Add a cover photo to continue.');
            $('cover-drop').scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        // Build the upload ourselves so the resized photos are sent, in order
        var data = new FormData();
        ['csrf', 'product_id', 'item_id', 'title', 'names', 'message', 'quantity'].forEach(function (name) {
            var el = form.elements[name];
            if (el) data.append(name, el.value);
        });
        if (cover.blob) {
            data.append('cover', cover.blob, 'cover.jpg');
        } else {
            data.append('keep_cover', cover.id);
        }
        var newIndex = 0;
        pages.forEach(function (p) {
            if (p.blob) {
                data.append('pages[]', p.blob, 'page.jpg');
                data.append('page_order[]', 'n:' + newIndex++);
            } else {
                data.append('page_order[]', 'e:' + p.id);
            }
        });

        var btn = $('cz-save');
        var label = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Uploading photos...';
        var bar = $('cz-progress');
        bar.classList.add('show');

        var xhr = new XMLHttpRequest();
        xhr.open('POST', form.getAttribute('action'));
        xhr.setRequestHeader('X-Requested-With', 'fetch');
        xhr.upload.onprogress = function (ev) {
            if (ev.lengthComputable) bar.firstElementChild.style.width = Math.round(ev.loaded / ev.total * 100) + '%';
        };
        xhr.onload = function () {
            var res = null;
            try { res = JSON.parse(xhr.responseText); } catch (err) { /* not JSON */ }
            if (res && res.ok) {
                window.location.href = res.redirect;
                return;
            }
            btn.disabled = false;
            btn.textContent = label;
            bar.classList.remove('show');
            showError((res && res.error) || 'Your photos could not be saved. Please try again.');
        };
        xhr.onerror = function () {
            btn.disabled = false;
            btn.textContent = label;
            bar.classList.remove('show');
            showError('Upload failed. Check your connection and try again.');
        };
        xhr.send(data);
    });

    renderCover();
    renderPages();
    renderText();
})();
</script>

<?php include 'assets/includes/footer.php'; ?>
