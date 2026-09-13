import { createRoot } from 'react-dom/client';
import { useEditor, EditorContent } from '@tiptap/react';
import StarterKit from '@tiptap/starter-kit';
import Image from '@tiptap/extension-image';
import Placeholder from '@tiptap/extension-placeholder';
import CharacterCount from '@tiptap/extension-character-count';

function MenuBar({ editor }) {
    if (!editor) return null;

    const btn = (label, action, active) => (
        <button
            type="button"
            onMouseDown={e => { e.preventDefault(); action(); }}
            className={`px-2 py-1 text-sm rounded ${active ? 'bg-brand-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600'}`}
        >
            {label}
        </button>
    );

    const addImage = () => {
        const url = prompt('Image URL');
        if (url) editor.chain().focus().setImage({ src: url }).run();
    };

    return (
        <div className="flex flex-wrap gap-1 p-2 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 rounded-t-lg">
            {btn('B', () => editor.chain().focus().toggleBold().run(), editor.isActive('bold'))}
            {btn('I', () => editor.chain().focus().toggleItalic().run(), editor.isActive('italic'))}
            {btn('S', () => editor.chain().focus().toggleStrike().run(), editor.isActive('strike'))}
            {btn('Code', () => editor.chain().focus().toggleCode().run(), editor.isActive('code'))}
            <span className="border-l border-gray-300 dark:border-gray-600 mx-1" />
            {btn('H2', () => editor.chain().focus().toggleHeading({ level: 2 }).run(), editor.isActive('heading', { level: 2 }))}
            {btn('H3', () => editor.chain().focus().toggleHeading({ level: 3 }).run(), editor.isActive('heading', { level: 3 }))}
            <span className="border-l border-gray-300 dark:border-gray-600 mx-1" />
            {btn('UL', () => editor.chain().focus().toggleBulletList().run(), editor.isActive('bulletList'))}
            {btn('OL', () => editor.chain().focus().toggleOrderedList().run(), editor.isActive('orderedList'))}
            {btn('Quote', () => editor.chain().focus().toggleBlockquote().run(), editor.isActive('blockquote'))}
            {btn('---', () => editor.chain().focus().setHorizontalRule().run(), false)}
            <span className="border-l border-gray-300 dark:border-gray-600 mx-1" />
            {btn('Image', addImage, false)}
            <span className="border-l border-gray-300 dark:border-gray-600 mx-1" />
            {btn('↩ Undo', () => editor.chain().focus().undo().run(), false)}
            {btn('↪ Redo', () => editor.chain().focus().redo().run(), false)}
        </div>
    );
}

function TiptapEditor({ targetId, initialContent }) {
    const editor = useEditor({
        extensions: [
            StarterKit,
            Image.configure({ inline: false, allowBase64: true }),
            Placeholder.configure({ placeholder: 'Write your content here…' }),
            CharacterCount,
        ],
        content: initialContent || '',
        onUpdate({ editor }) {
            const hidden = document.getElementById(targetId);
            if (hidden) hidden.value = editor.getHTML();
        },
        editorProps: {
            attributes: {
                class: 'prose dark:prose-invert max-w-none p-4 min-h-[400px] focus:outline-none',
            },
        },
    });

    const chars = editor ? editor.storage.characterCount.characters() : 0;
    const words = editor ? editor.storage.characterCount.words() : 0;

    return (
        <div className="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden bg-white dark:bg-gray-900">
            <MenuBar editor={editor} />
            <EditorContent editor={editor} />
            <div className="px-4 py-1.5 border-t border-gray-100 dark:border-gray-800 text-xs text-gray-400 text-right">
                {words} words · {chars} characters
            </div>
        </div>
    );
}

// Mount on all matching elements
document.querySelectorAll('[data-tiptap-editor]').forEach(el => {
    const targetId = el.dataset.tiptapTarget;
    const initialContent = el.dataset.tiptapContent || '';
    createRoot(el).render(
        <TiptapEditor targetId={targetId} initialContent={initialContent} />
    );
});
