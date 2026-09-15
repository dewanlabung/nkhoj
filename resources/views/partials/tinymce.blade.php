{{-- Self-hosted TinyMCE (MIT / GPL, no API key required)
     @include('partials.tinymce', ['editorId' => 'body-editor', 'height' => 480, 'mediaType' => 'posts'])
--}}
<script src="/tinymce/tinymce.min.js" referrerpolicy="origin"></script>
<script>
(function () {
    var _id        = '{{ $editorId ?? "body-editor" }}';
    var _mediaType = '{{ $mediaType ?? "posts" }}';
    var _dark      = document.documentElement.classList.contains('dark') ||
                     document.documentElement.getAttribute('data-theme') === 'dark';
    var _csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    tinymce.init({
        selector: '#' + _id,
        height: {{ $height ?? 480 }},
        license_key: 'gpl',
        base_url: '/tinymce',
        suffix: '.min',

        skin: _dark ? 'oxide-dark' : 'oxide',
        content_css: _dark ? 'dark' : 'default',

        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap',
            'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'wordcount',
        ],

        toolbar:
            'undo redo | blocks | bold italic underline strikethrough | ' +
            'forecolor backcolor | alignleft aligncenter alignright alignjustify | ' +
            'bullist numlist outdent indent | ' +
            'link image media table | ' +
            'removeformat | code fullscreen',

        menubar: false,
        branding: false,
        promotion: false,
        statusbar: true,

        block_formats: 'Paragraph=p; Heading 2=h2; Heading 3=h3; Heading 4=h4; Blockquote=blockquote; Preformatted=pre',

        image_advtab: true,
        image_caption: true,

        // Upload new images inline (converts blob → permanent URL)
        images_upload_url: '/user/media/upload',
        images_upload_credentials: true,
        images_upload_handler: function (blobInfo, progress) {
            return new Promise(function (resolve, reject) {
                var fd = new FormData();
                fd.append('file', blobInfo.blob(), blobInfo.filename());
                fd.append('type', _mediaType);
                fd.append('_token', _csrfToken);

                var xhr = new XMLHttpRequest();
                xhr.open('POST', '/user/media/upload');
                xhr.upload.onprogress = function (e) {
                    if (e.lengthComputable) progress(e.loaded / e.total * 100);
                };
                xhr.onload = function () {
                    if (xhr.status < 200 || xhr.status >= 300) {
                        reject({ message: 'Upload failed: HTTP ' + xhr.status, remove: true });
                        return;
                    }
                    var json = JSON.parse(xhr.responseText);
                    resolve(json.location);
                };
                xhr.onerror = function () { reject({ message: 'Upload error', remove: true }); };
                xhr.send(fd);
            });
        },

        // "Browse" button in image dialog — shows user's own previously uploaded images
        file_picker_types: 'image',
        file_picker_callback: function (callback, value, meta) {
            fetch('/user/media?type=' + _mediaType, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.images || !data.images.length) {
                    alert('No images uploaded yet for this section.');
                    return;
                }
                // Build a simple picker modal
                var overlay = document.createElement('div');
                overlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:9999;display:flex;align-items:center;justify-content:center';
                var box = document.createElement('div');
                box.style.cssText = 'background:#fff;border-radius:8px;padding:16px;max-width:680px;width:90%;max-height:80vh;overflow-y:auto';
                box.innerHTML = '<h3 style="margin:0 0 12px;font-size:15px;font-weight:600">My uploaded images</h3>';
                var grid = document.createElement('div');
                grid.style.cssText = 'display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:8px';
                data.images.forEach(function (img) {
                    var tile = document.createElement('div');
                    tile.style.cssText = 'cursor:pointer;border:2px solid transparent;border-radius:6px;overflow:hidden';
                    tile.innerHTML = '<img src="' + img.value + '" alt="" style="width:100%;height:90px;object-fit:cover"><p style="font-size:10px;margin:4px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">' + img.title + '</p>';
                    tile.onclick = function () {
                        callback(img.value, { title: img.title });
                        document.body.removeChild(overlay);
                    };
                    tile.onmouseenter = function () { tile.style.borderColor = '#4f46e5'; };
                    tile.onmouseleave = function () { tile.style.borderColor = 'transparent'; };
                    grid.appendChild(tile);
                });
                box.appendChild(grid);
                var close = document.createElement('button');
                close.textContent = '✕ Close';
                close.style.cssText = 'margin-top:12px;padding:6px 14px;border:1px solid #ccc;border-radius:6px;cursor:pointer;font-size:13px';
                close.onclick = function () { document.body.removeChild(overlay); };
                box.appendChild(close);
                overlay.appendChild(box);
                document.body.appendChild(overlay);
            });
        },

        setup: function (editor) {
            editor.on('change input', function () {
                editor.save();
                var el = document.getElementById(_id);
                if (el) el.dispatchEvent(new Event('input', { bubbles: true }));
            });
        },
    });

    window.__richEditors = window.__richEditors || {};
    tinymce.on('AddEditor', function (e) {
        if (e.editor.id === _id) window.__richEditors[_id] = e.editor;
    });
})();
</script>
