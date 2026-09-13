import { cpSync, mkdirSync } from 'fs';
import { fileURLToPath } from 'url';
import { dirname, join } from 'path';

const __dirname = dirname(fileURLToPath(import.meta.url));
const src  = join(__dirname, '../node_modules/tinymce');
const dest = join(__dirname, '../public/tinymce');

mkdirSync(dest, { recursive: true });

for (const dir of ['themes', 'models', 'plugins', 'skins', 'icons']) {
    cpSync(join(src, dir), join(dest, dir), { recursive: true });
}
cpSync(join(src, 'tinymce.min.js'), join(dest, 'tinymce.min.js'));

console.log('TinyMCE assets copied to public/tinymce/');
