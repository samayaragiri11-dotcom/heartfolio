/*
 * Heartfolio photo storage (front-end only, no server).
 * Customer photos are saved in the browser's IndexedDB, which holds far more
 * than localStorage. Photos never leave this browser: the shop owner does
 * not receive them, and clearing browser data deletes them.
 */
window.HFPhotos = window.HFPhotos || (function () {
    'use strict';

    var DB_NAME = 'heartfolio_photos';
    var STORE = 'customizations';
    var ACCEPTED = ['image/jpeg', 'image/png', 'image/webp'];
    var MAX_FILE_BYTES = 15 * 1024 * 1024; // 15 MB per photo before resizing
    var dbPromise = null;

    function openDb() {
        if (dbPromise) return dbPromise;
        dbPromise = new Promise(function (resolve, reject) {
            if (!('indexedDB' in window)) {
                reject(new Error('This browser cannot store photos. Try Chrome, Edge or Firefox.'));
                return;
            }
            var req = indexedDB.open(DB_NAME, 1);
            req.onupgradeneeded = function () {
                req.result.createObjectStore(STORE);
            };
            req.onsuccess = function () { resolve(req.result); };
            req.onerror = function () { reject(req.error || new Error('Photo storage could not be opened.')); };
        });
        // If opening failed, allow a retry next time
        dbPromise.catch(function () { dbPromise = null; });
        return dbPromise;
    }

    function run(mode, action) {
        return openDb().then(function (db) {
            return new Promise(function (resolve, reject) {
                var tx = db.transaction(STORE, mode);
                var req = action(tx.objectStore(STORE));
                tx.oncomplete = function () { resolve(req ? req.result : undefined); };
                tx.onerror = function () { reject(tx.error); };
                tx.onabort = function () {
                    reject(tx.error || new Error('Saving stopped. Your browser storage may be full.'));
                };
            });
        });
    }

    // data = { cover: Blob, pages: [Blob, ...] }
    function save(key, data) {
        return run('readwrite', function (s) { return s.put(data, key); });
    }

    function get(key) {
        return run('readonly', function (s) { return s.get(key); })
            .then(function (result) { return result || null; });
    }

    function remove(key) {
        return run('readwrite', function (s) { return s.delete(key); });
    }

    // Checks a chosen file, shrinks it to a sensible size and returns a JPEG Blob.
    // Shrinking keeps storage small and makes the preview fast.
    function prepareImage(file, maxSide) {
        maxSide = maxSide || 1600;
        return new Promise(function (resolve, reject) {
            if (ACCEPTED.indexOf(file.type) === -1) {
                reject(new Error(file.name + ' is not a JPG, PNG or WebP image.'));
                return;
            }
            if (file.size > MAX_FILE_BYTES) {
                reject(new Error(file.name + ' is larger than 15 MB. Choose a smaller photo.'));
                return;
            }

            var url = URL.createObjectURL(file);
            var img = new Image();

            img.onload = function () {
                var scale = Math.min(1, maxSide / Math.max(img.naturalWidth, img.naturalHeight));
                var w = Math.max(1, Math.round(img.naturalWidth * scale));
                var h = Math.max(1, Math.round(img.naturalHeight * scale));

                var canvas = document.createElement('canvas');
                canvas.width = w;
                canvas.height = h;
                var ctx = canvas.getContext('2d');
                ctx.fillStyle = '#ffffff'; // PNGs with transparency get a white background
                ctx.fillRect(0, 0, w, h);
                ctx.drawImage(img, 0, 0, w, h);
                URL.revokeObjectURL(url);

                canvas.toBlob(function (blob) {
                    if (blob) resolve(blob);
                    else reject(new Error(file.name + ' could not be processed.'));
                }, 'image/jpeg', 0.85);
            };

            img.onerror = function () {
                URL.revokeObjectURL(url);
                reject(new Error(file.name + ' could not be opened as an image.'));
            };

            img.src = url;
        });
    }

    return {
        save: save,
        get: get,
        remove: remove,
        prepareImage: prepareImage,
        ACCEPTED: ACCEPTED
    };
})();
