const fs = require('fs');
const path = require('path');

function walk(dir, ext = '.blade.php') {
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

const viewsDir = path.join(__dirname, '..', 'resources', 'views');
const bladeFiles = walk(viewsDir, '.blade.php');

console.log(`Total blade files found: ${bladeFiles.length}`);

// 1. INLINE STYLES COUNT
let totalInlineStyles = 0;
const fileInlineCounts = {};
const inlineStyleLines = [];

bladeFiles.forEach(file => {
    const rel = path.relative(path.join(__dirname, '..'), file);
    const content = fs.readFileSync(file, 'utf8');
    const lines = content.split('\n');
    let count = 0;
    lines.forEach((line, idx) => {
        if (/style\s*=\s*["']/.test(line)) {
            totalInlineStyles++;
            count++;
            inlineStyleLines.push({ file: rel, lineNum: idx + 1, content: line.trim() });
        }
    });
    if (count > 0) {
        fileInlineCounts[rel] = count;
    }
});

console.log(`\n========================================`);
console.log(`2. INLINE STYLES COUNT`);
console.log(`Total inline style lines: ${totalInlineStyles}`);
const sortedFiles = Object.entries(fileInlineCounts).sort((a, b) => b[1] - a[1]);
console.log(`Top 20 files with inline styles:`);
sortedFiles.slice(0, 20).forEach(([f, c]) => console.log(`  ${f}: ${c}`));

// Categorize inline styles
const inlineCategories = {
    'display/visibility/grid/flex': 0,
    'width/max-width/min-width': 0,
    'color/background': 0,
    'margin/padding': 0,
    'text-align/alignment': 0,
    'badge/status custom colors': 0,
    'other': 0
};
inlineStyleLines.forEach(item => {
    const s = item.content.toLowerCase();
    if (s.includes('display:') || s.includes('grid-template') || s.includes('flex')) {
        inlineCategories['display/visibility/grid/flex']++;
    } else if (s.includes('width:') || s.includes('max-width:')) {
        inlineCategories['width/max-width/min-width']++;
    } else if (s.includes('background') || s.includes('color:')) {
        inlineCategories['color/background']++;
    } else if (s.includes('margin') || s.includes('padding')) {
        inlineCategories['margin/padding']++;
    } else if (s.includes('text-align')) {
        inlineCategories['text-align/alignment']++;
    } else {
        inlineCategories['other']++;
    }
});
console.log(`\nInline style breakdown by CSS property pattern:`, inlineCategories);

// 2. COMPONENT USAGE
const components = ['x-button', 'x-card', 'x-badge', 'x-alert', 'x-input', 'x-select', 'x-modal', 'x-page-header'];
console.log(`\n========================================`);
console.log(`3. COMPONENT USAGE`);
components.forEach(comp => {
    let count = 0;
    const pages = [];
    const regex = new RegExp(`<${comp}[\\s>]`, 'g');
    bladeFiles.forEach(file => {
        const rel = path.relative(path.join(__dirname, '..'), file);
        const content = fs.readFileSync(file, 'utf8');
        const matches = content.match(regex);
        if (matches) {
            count += matches.length;
            pages.push(`${rel} (${matches.length})`);
        }
    });
    console.log(`Component <${comp}>: ${count} usages`);
    pages.forEach(p => console.log(`   - ${p}`));
});

// 3. DESIGN SYSTEM VERIFICATION: BUTTONS
console.log(`\n========================================`);
console.log(`1 & 5. BUTTON STYLES & CLASSES`);
const btnClasses = {};
bladeFiles.forEach(file => {
    const content = fs.readFileSync(file, 'utf8');
    const classMatches = content.matchAll(/class\s*=\s*["']([^"']*)["']/g);
    for (const match of classMatches) {
        const classes = match[1].split(/\s+/);
        classes.forEach(c => {
            if (c.includes('btn') || c.includes('button')) {
                btnClasses[c] = (btnClasses[c] || 0) + 1;
            }
        });
    }
});
console.log(`Button classes found in Blade files (${Object.keys(btnClasses).length} unique classes):`);
Object.entries(btnClasses).sort((a, b) => b[1] - a[1]).forEach(([k, v]) => console.log(`  ${k}: ${v}`));

// 4. CARDS
console.log(`\n========================================`);
console.log(`1 & 4. CARD STYLES & CLASSES`);
const cardClasses = {};
bladeFiles.forEach(file => {
    const content = fs.readFileSync(file, 'utf8');
    const classMatches = content.matchAll(/class\s*=\s*["']([^"']*)["']/g);
    for (const match of classMatches) {
        const classes = match[1].split(/\s+/);
        classes.forEach(c => {
            if (c.includes('card')) {
                cardClasses[c] = (cardClasses[c] || 0) + 1;
            }
        });
    }
});
console.log(`Card classes found in Blade files (${Object.keys(cardClasses).length} unique classes):`);
Object.entries(cardClasses).sort((a, b) => b[1] - a[1]).forEach(([k, v]) => console.log(`  ${k}: ${v}`));

// 5. INPUTS & SELECTS & FORMS
console.log(`\n========================================`);
console.log(`1. FORM & INPUT CLASSES`);
const formClasses = {};
bladeFiles.forEach(file => {
    const content = fs.readFileSync(file, 'utf8');
    const classMatches = content.matchAll(/class\s*=\s*["']([^"']*)["']/g);
    for (const match of classMatches) {
        const classes = match[1].split(/\s+/);
        classes.forEach(c => {
            if (c.includes('form') || c.includes('input') || c.includes('select')) {
                formClasses[c] = (formClasses[c] || 0) + 1;
            }
        });
    }
});
console.log(`Form/Input/Select classes found (${Object.keys(formClasses).length} unique classes):`);
Object.entries(formClasses).sort((a, b) => b[1] - a[1]).slice(0, 30).forEach(([k, v]) => console.log(`  ${k}: ${v}`));

// 6. MODALS
console.log(`\n========================================`);
console.log(`1. MODALS IN BLADE FILES`);
const modalInstances = [];
bladeFiles.forEach(file => {
    const rel = path.relative(path.join(__dirname, '..'), file);
    const content = fs.readFileSync(file, 'utf8');
    const matches = content.matchAll(/id\s*=\s*["']([^"']*modal[^"']*)["']/gi);
    for (const m of matches) {
        modalInstances.push({ file: rel, id: m[1] });
    }
});
console.log(`Total modal IDs found: ${modalInstances.length}`);
modalInstances.forEach(m => console.log(`  ${m.file} -> id="${m.id}"`));

// 7. ALERTS
console.log(`\n========================================`);
console.log(`1. ALERTS IN BLADE FILES`);
const alertInstances = [];
bladeFiles.forEach(file => {
    const rel = path.relative(path.join(__dirname, '..'), file);
    const content = fs.readFileSync(file, 'utf8');
    const xAlerts = (content.match(/<x-alert/g) || []).length;
    const manualAlerts = (content.match(/class\s*=\s*["'][^"']*alert[^"']*["']/g) || []).length;
    if (xAlerts > 0 || manualAlerts > 0) {
        alertInstances.push({ file: rel, xAlerts, manualAlerts });
    }
});
alertInstances.forEach(a => console.log(`  ${a.file} -> x-alert: ${a.xAlerts}, manual alert class: ${a.manualAlerts}`));

// 8. PAGE HEADERS
console.log(`\n========================================`);
console.log(`1. PAGE HEADERS IN BLADE FILES`);
const headerInstances = [];
bladeFiles.forEach(file => {
    const rel = path.relative(path.join(__dirname, '..'), file);
    const content = fs.readFileSync(file, 'utf8');
    const xHeader = (content.match(/<x-page-header/g) || []).length;
    const manualHeader = (content.match(/class\s*=\s*["'][^"']*page-header[^"']*["']/g) || []).length;
    const headerTitle = (content.match(/class\s*=\s*["'][^"']*(page-title|header-title)[^"']*["']/g) || []).length;
    if (xHeader > 0 || manualHeader > 0 || headerTitle > 0) {
        headerInstances.push({ file: rel, xHeader, manualHeader, headerTitle });
    }
});
headerInstances.forEach(h => console.log(`  ${h.file} -> x-page-header: ${h.xHeader}, .page-header: ${h.manualHeader}, title: ${h.headerTitle}`));

