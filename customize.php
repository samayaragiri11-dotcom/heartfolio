<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once 'assets/includes/products-data.php';

$product = hf_product($_GET['id'] ?? 0);

include 'assets/includes/navbar.php';
?>

<style>
.hf-customize { padding: 50px 20px 70px; }
.hf-customize .cz-head { margin-bottom: 30px; }
.hf-customize .cz-head h1 { color: var(--dark-brown, #3F352D); margin-bottom: 6px; }
.hf-customize .cz-head p { color: var(--text-light, #7a6f66); }
.hf-customize .cz-grid {
    display: grid;
    grid-template-columns: minmax(260px, 380px) 1fr;
    gap: 48px;
    align-items: start;
}

/* Live preview of the cover */
.hf-customize .cz-preview { position: sticky; top: 20px; }
.hf-customize .cz-cover {
    position: relative;
    aspect-ratio: 8.5 / 11;
    border-radius: 4px 10px 10px 4px;
    overflow: hidden;
    background: var(--warm-beige, #F1E6D6);
    box-shadow: -6px 0 0 -2px var(--sand, #DCC7AD), 0 18px 40px rgba(63, 53, 45, 0.22);
}
.hf-customize .cz-cover img {
    position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover;
}
.hf-customize .cz-cover-empty {
    position: absolute; inset: 0; display: flex; flex-direction: column;
    align-items: center; justify-content: center; gap: 10px;
    color: var(--light-taupe, #B8A895); font-size: 14px; text-align: center; padding: 30px;
}
.hf-customize .cz-cover-empty i { font-size: 40px; }
.hf-customize .cz-cover-text {
    position: absolute; left: 0; right: 0; bottom: 0;
    padding: 70px 22px 22px;
    background: linear-gradient(to top, rgba(30, 24, 20, 0.78), rgba(30, 24, 20, 0));
    color: #fff;
}
.hf-customize .cz-cover-title {
    font-family: Georgia, "Times New Roman", serif;
    font-size: 30px; line-height: 1.1; margin: 0; word-wrap: break-word;
}
.hf-customize .cz-cover-names { font-size: 14px; margin-top: 8px; opacity: 0.9; word-wrap: break-word; }
.hf-customize .cz-preview-note { font-size: 13px; color: var(--text-light, #7a6f66); margin-top: 14px; }

/* Form */
.hf-customize .cz-step {
    padding: 24px 0;
    border-bottom: 1px solid var(--sand, #DCC7AD);
}
.hf-customize .cz-step:first-child { padding-top: 0; }
.hf-customize .cz-step h3 { color: var(--dark-brown, #3F352D); margin-bottom: 4px; font-size: 18px; }
.hf-customize .cz-step > p { color: var(--text-light, #7a6f66); font-size: 14px; margin-bottom: 14px; }
.hf-customize .cz-drop {
    display: flex; align-items: center; justify-content: center; gap: 10px;
    padding: 22px; border: 2px dashed var(--sand, #DCC7AD); border-radius: 10px;
    color: var(--dark-brown, #3F352D); cursor: pointer; text-align: center;
    background: var(--cream, #FAF5ED); transition: border-color 0.2s, background 0.2s;
}
.hf-customize .cz-drop:hover, .hf-customize .cz-drop.dragging {
    border-color: var(--soft-pink, #D98291); background: #fff;
}
.hf-customize .cz-drop:focus-within { outline: 3px solid rgba(217, 130, 145, 0.4); outline-offset: 2px; }
.hf-customize .cz-drop input { position: absolute; width: 1px; height: 1px; opacity: 0; }
.hf-customize .cz-pages {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));
    gap: 10px; margin-top: 14px;
}
.hf-customize .cz-page { position: relative; aspect-ratio: 3 / 4; border-radius: 6px; overflow: hidden; }
.hf-customize .cz-page img { width: 100%; height: 100%; object-fit: cover; display: block; }
.hf-customize .cz-page-num {
    position: absolute; left: 6px; bottom: 6px; background: rgba(0,0,0,0.6); color: #fff;
    font-size: 11px; padding: 2px 6px; border-radius: 4px;
}
.hf-customize .cz-page button {
    position: absolute; top: 6px; right: 6px; width: 26px; height: 26px;
    border: none; border-radius: 50%; background: rgba(0,0,0,0.65); color: #fff;
    cursor: pointer; font-size: 14px; line-height: 26px; padding: 0;
}
.hf-customize .cz-page button:hover { background: var(--soft-pink, #D98291); }
.hf-customize .cz-count { font-size: 13px; color: var(--text-light, #7a6f66); margin-top: 8px; }
.hf-customize .form-group { margin-bottom: 16px; }
.hf-customize .form-group label { display: block; font-weight: 600; margin-bottom: 6px; color: var(--dark-brown, #3F352D); }
.hf-customize .form-group input[type="text"],
.hf-customize .form-group textarea {
    width: 100%; padding: 11px 13px; border: 1px solid var(--sand, #DCC7AD);
    border-radius: 8px; font: inherit; background: #fff;
}
.hf-customize .cz-hint { font-size: 12px; color: var(--text-light, #7a6f66); margin-top: 4px; }
.hf-customize .cz-error {
    display: none; margin: 18px 0 0; padding: 12px 14px; border-radius: 8px;
    background: #F8D7DA; color: #721C24; font-size: 14px;
}
.hf-customize .cz-actions {
    display: flex; flex-wrap: wrap; align-items: center; gap: 14px; margin-top: 24px;
}
.hf-customize .cz-actions .btn { width: auto; margin: 0; }
.hf-customize .cz-actions .btn[disabled] { opacity: 0.6; cursor: wait; }
.hf-customize .cz-price { font-size: 20px; font-weight: 700; color: var(--dark-brown, #3F352D); }

@media (max-width: 820px) {
    .hf-customize .cz-grid { grid-template-columns: 1fr; gap: 30px; }
    .hf-customize .cz-preview { position: static; max-width: 340px; margin: 0 auto; }
}
</style>

<?php if (!$product): ?>
<div class="container" style="text-align: center; padding: 60px 20px;">
    <h2>Magazine not found</h2>
    <p style="margin: 10px 0 20px;">Pick a magazine from the shop to customize it.</p>
    <a href="shop.php" class="btn btn-primary">Back to Shop</a>
</div>
<?php else: ?>
<div class="container hf-customize" style="display: block;">
    <div class="cz-head">
        <h1 id="cz-heading">Customize <?php echo hf_e($product['name']); ?></h1>
        <p>Add your own photos and words. The preview updates as you go.</p>
    </div>

    <div class="cz-grid">
        <!-- Live preview -->
        <div class="cz-preview">
            <div class="cz-cover" aria-label="Cover preview">
                <img id="pv-cover-img" alt="" hidden>
                <div class="cz-cover-empty" id="pv-cover-empty">
                    <i class="fas fa-image" aria-hidden="true"></i>
                    Your cover photo will appear here
                </div>
                <div class="cz-cover-text">
                    <p class="cz-cover-title" id="pv-title"><?php echo hf_e($product['name']); ?></p>
                    <div class="cz-cover-names" id="pv-names"></div>
                </div>
            </div>
            <p class="cz-preview-note">Photos are saved in this browser only.</p>
        </div>

        <!-- Form -->
        <form id="cz-form" novalidate>
            <div class="cz-step">
                <h3>Cover photo</h3>
                <p>Required. A portrait (tall) photo fills the cover best.</p>
                <label class="cz-drop" id="cover-drop">
                    <i class="fas fa-upload" aria-hidden="true"></i>
                    <span id="cover-label">Choose a cover photo or drop it here</span>
                    <input type="file" id="cover-input" accept="image/jpeg,image/png,image/webp">
                </label>
            </div>

            <div class="cz-step">
                <h3>Inside pages</h3>
                <p>Up to 12 photos, printed in the order shown.</p>
                <label class="cz-drop" id="pages-drop">
                    <i class="fas fa-images" aria-hidden="true"></i>
                    <span>Add photos or drop them here</span>
                    <input type="file" id="pages-input" accept="image/jpeg,image/png,image/webp" multiple>
                </label>
                <div class="cz-pages" id="pages-grid"></div>
                <p class="cz-count" id="pages-count">0 of 12 photos</p>
            </div>

            <div class="cz-step">
                <h3>Your words</h3>
                <p>Shown on the cover and the first page.</p>
                <div class="form-group">
                    <label for="cz-title">Magazine title</label>
                    <input type="text" id="cz-title" maxlength="40" placeholder="<?php echo hf_e($product['name']); ?>">
                    <p class="cz-hint">Up to 40 characters. Leave empty to keep "<?php echo hf_e($product['name']); ?>".</p>
                </div>
                <div class="form-group">
                    <label for="cz-names">Names</label>
                    <input type="text" id="cz-names" maxlength="60" placeholder="e.g. Aashma & Priya">
                </div>
                <div class="form-group">
                    <label for="cz-message">Message</label>
                    <textarea id="cz-message" rows="4" maxlength="300" placeholder="A note for the first page"></textarea>
                    <p class="cz-hint"><span id="msg-count">0</span>/300 characters</p>
                </div>
            </div>

            <div class="cz-step" style="border-bottom: none;">
                <div class="quantity-selector">
                    <button type="button" id="qty-dec" aria-label="Decrease quantity">-</button>
                    <input type="number" id="quantity" value="1" min="1" max="99" aria-label="Quantity">
                    <button type="button" id="qty-inc" aria-label="Increase quantity">+</button>
                </div>

                <div class="cz-error" id="cz-error" role="alert"></div>

                <div class="cz-actions">
                    <span class="cz-price"><?php echo hf_price($product['price']); ?></span>
                    <button type="submit" class="btn btn-primary" id="cz-save">Add customized magazine to cart</button>
                    <a href="product-details.php?id=<?php echo (int)$product['id']; ?>" class="btn btn-secondary" id="cz-cancel">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="assets/js/photo-store.js"></script>
<script>
(function () {
    var PRODUCT = <?php echo json_encode([
        'id'       => $product['id'],
        'name'     => $product['name'],
        'price'    => $product['price'],
        'image'    => $product['image'],
        'category' => hf_category_label($product['category']),
    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
    var MAX_PAGES = 12;

    var params = new URLSearchParams(window.location.search);
    var editKey = params.get('edit');
    var editing = null;            // the cart line being edited, if any

    var cover = null;              // Blob
    var pages = [];                // [Blob]
    var urls = [];                 // object URLs to clean up

    var $ = function (id) { return document.getElementById(id); };
    var errorBox = $('cz-error');

    function showError(msg) {
        errorBox.textContent = msg;
        errorBox.style.display = msg ? 'block' : 'none';
    }

    function makeUrl(blob) {
        var u = URL.createObjectURL(blob);
        urls.push(u);
        return u;
    }

    // ---------- Preview ----------
    function renderCover() {
        var img = $('pv-cover-img');
        if (cover) {
            img.src = makeUrl(cover);
            img.hidden = false;
            $('pv-cover-empty').hidden = true;
            $('cover-label').textContent = 'Change cover photo';
        } else {
            img.hidden = true;
            $('pv-cover-empty').hidden = false;
            $('cover-label').textContent = 'Choose a cover photo or drop it here';
        }
    }

    function renderPages() {
        $('pages-grid').innerHTML = pages.map(function (blob, i) {
            return '<div class="cz-page">' +
                '<img src="' + makeUrl(blob) + '" alt="Page ' + (i + 1) + ' photo">' +
                '<span class="cz-page-num">' + (i + 1) + '</span>' +
                '<button type="button" data-index="' + i + '" aria-label="Remove page ' + (i + 1) + ' photo">&times;</button>' +
            '</div>';
        }).join('');
        $('pages-count').textContent = pages.length + ' of ' + MAX_PAGES + ' photos';
        $('pages-drop').style.display = pages.length >= MAX_PAGES ? 'none' : '';
    }

    function renderText() {
        $('pv-title').textContent = $('cz-title').value.trim() || PRODUCT.name;
        $('pv-names').textContent = $('cz-names').value.trim();
        $('msg-count').textContent = $('cz-message').value.length;
    }

    // ---------- Adding photos ----------
    function setCover(file) {
        if (!file) return;
        showError('');
        HFPhotos.prepareImage(file, 1800).then(function (blob) {
            cover = blob;
            renderCover();
        }).catch(function (err) { showError(err.message); });
    }

    function addPages(fileList) {
        showError('');
        var files = Array.prototype.slice.call(fileList);
        var room = MAX_PAGES - pages.length;
        if (files.length > room) {
            showError('Only ' + MAX_PAGES + ' inside photos fit. The first ' + room + ' were added.');
            files = files.slice(0, room);
        }
        // Process in order so pages keep the order the customer picked
        files.reduce(function (chain, file) {
            return chain.then(function () {
                return HFPhotos.prepareImage(file, 1400).then(function (blob) {
                    pages.push(blob);
                }).catch(function (err) { showError(err.message); });
            });
        }, Promise.resolve()).then(renderPages);
    }

    $('cover-input').addEventListener('change', function () {
        setCover(this.files[0]);
        this.value = '';
    });

    $('pages-input').addEventListener('change', function () {
        addPages(this.files);
        this.value = '';
    });

    $('pages-grid').addEventListener('click', function (e) {
        var btn = e.target.closest('button[data-index]');
        if (!btn) return;
        pages.splice(parseInt(btn.getAttribute('data-index'), 10), 1);
        renderPages();
    });

    // Drag and drop onto either upload box
    [['cover-drop', function (files) { setCover(files[0]); }],
     ['pages-drop', addPages]].forEach(function (pair) {
        var zone = $(pair[0]);
        zone.addEventListener('dragover', function (e) { e.preventDefault(); zone.classList.add('dragging'); });
        zone.addEventListener('dragleave', function () { zone.classList.remove('dragging'); });
        zone.addEventListener('drop', function (e) {
            e.preventDefault();
            zone.classList.remove('dragging');
            if (e.dataTransfer.files.length) pair[1](e.dataTransfer.files);
        });
    });

    ['cz-title', 'cz-names', 'cz-message'].forEach(function (id) {
        $(id).addEventListener('input', renderText);
    });

    // ---------- Quantity ----------
    function getQty() {
        var q = parseInt($('quantity').value, 10);
        return isNaN(q) ? 1 : Math.min(99, Math.max(1, q));
    }
    $('qty-dec').addEventListener('click', function () { $('quantity').value = Math.max(1, getQty() - 1); });
    $('qty-inc').addEventListener('click', function () { $('quantity').value = Math.min(99, getQty() + 1); });
    $('quantity').addEventListener('change', function () { this.value = getQty(); });

    // ---------- Save ----------
    $('cz-form').addEventListener('submit', function (e) {
        e.preventDefault();
        showError('');

        if (!cover) {
            showError('Add a cover photo to continue.');
            $('cover-drop').scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        var saveBtn = $('cz-save');
        var label = saveBtn.textContent;
        saveBtn.disabled = true;
        saveBtn.textContent = 'Saving photos...';

        var photoKey = (editing && editing.custom && editing.custom.photoKey) || HFCart.uniqueId('p');
        var custom = {
            title: $('cz-title').value.trim(),
            names: $('cz-names').value.trim(),
            message: $('cz-message').value.trim(),
            hasCover: true,
            pageCount: pages.length,
            photoKey: photoKey
        };

        HFPhotos.save(photoKey, { cover: cover, pages: pages }).then(function () {
            HFCart.saveCustom(PRODUCT, getQty(), custom, editing ? editing.key : null);
            window.location.href = 'cart.php?customized=' + (editing ? 'updated' : 'added');
        }).catch(function (err) {
            saveBtn.disabled = false;
            saveBtn.textContent = label;
            showError((err && err.name === 'QuotaExceededError')
                ? 'Your browser storage is full. Remove some photos and try again.'
                : (err && err.message) || 'Photos could not be saved. Try again.');
        });
    });

    // ---------- Edit mode: load an existing customized cart line ----------
    if (editKey) {
        editing = HFCart.getItem(editKey);
        if (!editing || !editing.custom || editing.id !== PRODUCT.id) {
            editing = null;
            showError('That customized item is no longer in your cart. You can start a new one below.');
        } else {
            $('cz-heading').textContent = 'Edit your ' + PRODUCT.name;
            $('cz-save').textContent = 'Save changes';
            $('cz-cancel').setAttribute('href', 'cart.php');
            $('cz-title').value = editing.custom.title || '';
            $('cz-names').value = editing.custom.names || '';
            $('cz-message').value = editing.custom.message || '';
            $('quantity').value = editing.qty;
            renderText();

            HFPhotos.get(editing.custom.photoKey).then(function (data) {
                if (data) {
                    cover = data.cover || null;
                    pages = data.pages || [];
                } else {
                    showError('The photos for this item were not found in this browser. Add them again.');
                }
                renderCover();
                renderPages();
            }).catch(function (err) { showError(err.message); });
        }
    }

    window.addEventListener('beforeunload', function () {
        urls.forEach(function (u) { URL.revokeObjectURL(u); });
    });

    renderCover();
    renderPages();
    renderText();
})();
</script>
<?php endif; ?>

<?php include 'assets/includes/footer.php'; ?>
