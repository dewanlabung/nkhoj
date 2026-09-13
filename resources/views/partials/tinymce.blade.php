{{-- Self-hosted TinyMCE (MIT / GPL, no API key required)
     @include('partials.tinymce', ['editorId' => 'body-editor', 'height' => 480])
--}}
<script src="/tinymce/tinymce.min.js" referrerpolicy="origin"></script>
<script>
(function () {
    var _id   = '{{ $editorId ?? "body-editor" }}';
    var _dark  = document.documentElement.classList.contains('dark') ||
                 document.documentElement.getAttribute('data-theme') === 'dark';

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
        automatic_uploads: false,

        setup: function (editor) {
            editor.on('change input', function () {
                editor.save();
                var el = document.getElementById(_id);
                if (el) el.dispatchEvent(new Event('input', { bubbles: true }));
            });
        },
    });

    window.__richEditors = window.__richEditors || {};
    // Store reference after init
    tinymce.on('AddEditor', function (e) {
        if (e.editor.id === _id) {
            window.__richEditors[_id] = e.editor;
        }
    });
})();
</script>
