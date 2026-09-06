{{-- Rich text editor (Jodit — MIT, no API key required)
     @include('partials.tinymce', ['editorId' => 'body-editor', 'height' => 480])
--}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jodit@3/build/jodit.min.css">
<script src="https://cdn.jsdelivr.net/npm/jodit@3/build/jodit.min.js"></script>
<script>
(function () {
    var _id   = '{{ $editorId ?? "body-editor" }}';
    var _dark  = document.documentElement.classList.contains('dark');

    var editor = Jodit.make('#' + _id, {
        height: {{ $height ?? 480 }},
        theme: _dark ? 'dark' : 'default',
        toolbarAdaptive: false,
        toolbarSticky: true,
        useSearch: false,
        showCharsCounter: false,
        showWordsCounter: false,
        showXPathInStatusbar: false,
        buttons: [
            'bold', 'italic', 'underline', 'strikethrough', '|',
            'ul', 'ol', '|',
            'outdent', 'indent', '|',
            'font', 'fontsize', 'brush', 'paragraph', '|',
            'image', 'table', 'link', '|',
            'align', '|',
            'undo', 'redo', '|',
            'hr', 'eraser', '|',
            'fullsize', 'source'
        ],
        events: {
            change: function (newContent) {
                var el = document.getElementById(_id);
                if (el) {
                    el.dispatchEvent(new Event('input', { bubbles: true }));
                }
            }
        }
    });

    window.__richEditors = window.__richEditors || {};
    window.__richEditors[_id] = editor;
})();
</script>
