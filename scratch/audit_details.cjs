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

// 1. BUTTON CLASSES
const btnClasses = {};
bladeFiles.forEach(f => {
    const c = fs.readFileSync(f, 'utf8');
    const matches = c.matchAll(/class\s*=\s*["']([^"']*)["']/g);
    for (const m of matches) {
        m[1].split(/\s+/).forEach(cls => {
            if (cls.includes('btn') || cls.includes('button')) {
                btnClasses[cls] = (btnClasses[cls] || 0) + 1;
            }
        });
    }
});

console.log('=== BUTTON CLASSES IN BLADE FILES ===');
console.log('Total unique classes:', Object.keys(btnClasses).length);
console.log(JSON.stringify(Object.entries(btnClasses).sort((a,b)=>b[1]-a[1]), null, 2));

// 2. CARD CLASSES
const cardClasses = {};
bladeFiles.forEach(f => {
    const c = fs.readFileSync(f, 'utf8');
    const matches = c.matchAll(/class\s*=\s*["']([^"']*)["']/g);
    for (const m of matches) {
        m[1].split(/\s+/).forEach(cls => {
            if (cls.includes('card')) {
                cardClasses[cls] = (cardClasses[cls] || 0) + 1;
            }
        });
    }
});

console.log('=== CARD CLASSES IN BLADE FILES ===');
console.log('Total unique classes:', Object.keys(cardClasses).length);
console.log(JSON.stringify(Object.entries(cardClasses).sort((a,b)=>b[1]-a[1]), null, 2));

// 3. INPUT CLASSES
const inputClasses = {};
bladeFiles.forEach(f => {
    const c = fs.readFileSync(f, 'utf8');
    const matches = c.matchAll(/class\s*=\s*["']([^"']*)["']/g);
    for (const m of matches) {
        m[1].split(/\s+/).forEach(cls => {
            if (cls.includes('input') || cls.includes('select') || cls.includes('form-control')) {
                inputClasses[cls] = (inputClasses[cls] || 0) + 1;
            }
        });
    }
});

console.log('=== INPUT / SELECT CLASSES IN BLADE FILES ===');
console.log('Total unique classes:', Object.keys(inputClasses).length);
console.log(JSON.stringify(Object.entries(inputClasses).sort((a,b)=>b[1]-a[1]), null, 2));

