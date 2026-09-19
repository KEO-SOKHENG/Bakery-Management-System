const fs = require('fs');
const path = require('path');

function walk(dir, ext = '.js') {
    let results = [];
    const list = fs.readdirSync(dir);
    list.forEach(file => {
        const fullPath = path.join(dir, file);
        const stat = fs.statSync(fullPath);
        if (stat && stat.isDirectory()) {
            results = results.concat(walk(fullPath, ext));
        } else if (file.endsWith(ext)) {
            results.push(fullPath);
        }
    });
    return results;
}

const jsFiles = walk(path.join(__dirname, '..', 'public', 'js'));

const targetWords = [
    'database',
    'postgresql',
    'schema',
    'persisted',
    'disbursed',
    'execute',
    'initiate',
    'entity',
    'add new '
];

console.log('=== JS FILES WORDING AUDIT ===');
jsFiles.forEach(file => {
    const rel = path.relative(path.join(__dirname, '..'), file);
    const content = fs.readFileSync(file, 'utf8');
    const lines = content.split('\n');
    lines.forEach((line, idx) => {
        targetWords.forEach(word => {
            const regex = new RegExp(`\\b${word}\\b`, 'i');
            if (regex.test(line)) {
                console.log(`[${word.toUpperCase()}] ${rel}:${idx + 1} -> ${line.trim()}`);
            }
        });
    });
});
