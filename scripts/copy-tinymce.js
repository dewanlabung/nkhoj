import { cpSync, mkdirSync, existsSync } from 'fs';
import { fileURLToPath } from 'url';
import { dirname, join } from 'path';

const __dirname = dirname(fileURLToPath(import.meta.url));
const src  = join(__dirname, '../node_modules/tinymce');
const dest = join(__dirname, '../public/tinymce');

// Check if TinyMCE is installed
if (!existsSync(src)) {
    console.error(`TinyMCE not found at ${src}`);
    console.error('Run: npm install');
    process.exit(1);
}

try {
    mkdirSync(dest, { recursive: true });

    for (const dir of ['themes', 'models', 'plugins', 'skins', 'icons']) {
        const srcDir = join(src, dir);
        if (existsSync(srcDir)) {
            cpSync(srcDir, join(dest, dir), { recursive: true });
        }
    }

    const jsFile = join(src, 'tinymce.min.js');
    if (existsSync(jsFile)) {
        cpSync(jsFile, join(dest, 'tinymce.min.js'));
    }

    console.log('✓ TinyMCE assets copied to public/tinymce/');
} catch (error) {
    console.error('✗ Error copying TinyMCE assets:', error.message);
    process.exit(1);
}
