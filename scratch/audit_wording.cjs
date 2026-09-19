const fs = require('fs');
const path = require('path');

function walk(dir, ext = '.blade.php') {
    let results = [];
    fs.readdirSync(dir).forEach(file => {
        const full = path.join(dir, file);
        if (fs.statSync(full).isDirectory()) results = results.concat(walk(full, ext));
        else if (file.endsWith(ext)) results.push(full);
    });
    return results;
}

const viewsDir = path.join(__dirname, '..', 'resources', 'views');
const bladeFiles = walk(viewsDir, '.blade.php');

const targetWords = [
    'database',
    'postgresql',
    'schema',
    'transaction',
    'persisted',
    'disbursed',
    'execute',
    'initiate',
    'entity',
    'payload',
    'foreign key',
    'primary key',
    'backend',
    'controller',
    'exception',
    'migration',
    'seed'
];

console.log('=== WORDING AUDIT: TECHNICAL TERMS IN VIEWS ===');

bladeFiles.forEach(file => {
    const rel = path.relative(path.join(__dirname, '..'), file);
    const content = fs.readFileSync(file, 'utf8');
    const lines = content.split('\n');

    lines.forEach((line, idx) => {
        // Skip script tags or comments if possible, but let's check everything
        targetWords.forEach(word => {
            const regex = new RegExp(`\\b${word}\\b`, 'i');
            if (regex.test(line)) {
                // Ignore code/variable names like $transaction, route('transactions'), etc., if clearly PHP/JS
                // But report if it's user facing text or in title/label/button/alert
                console.log(`[${word.toUpperCase()}] ${rel}:${idx + 1} -> ${line.trim()}`);
            }
        });
    });
});

console.log('\n=== CHECKING FORMAL / AWKWARD BUTTON & ACTION PHRASES ===');
const actionPhrases = [
    /Add New\s+[A-Za-z]+/i,
    /Initiate\s+[A-Za-z]+/i,
    /Disburse/i,
    /Mark.*disbursed/i,
    /Execute/i,
    /Persist/i
];

bladeFiles.forEach(file => {
    const rel = path.relative(path.join(__dirname, '..'), file);
    const content = fs.readFileSync(file, 'utf8');
    const lines = content.split('\n');
    lines.forEach((line, idx) => {
        actionPhrases.forEach(rgx => {
            if (rgx.test(line)) {
                console.log(`[ACTION PHRASE] ${rel}:${idx+1} -> ${line.trim()}`);
            }
        });
    });
});
