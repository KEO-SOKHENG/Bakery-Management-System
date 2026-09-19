const fs = require('fs');
const path = require('path');

function walk(dir, exts) {
    let results = [];
    const list = fs.readdirSync(dir);
    list.forEach(file => {
        const fullPath = path.join(dir, file);
        const stat = fs.statSync(fullPath);
        if (stat && stat.isDirectory()) {
            results = results.concat(walk(fullPath, exts));
        } else if (exts.some(e => file.endsWith(e))) {
            results.push(fullPath);
        }
    });
    return results;
}

const root = path.join(__dirname, '..');
const publicFiles = walk(path.join(root, 'public'), ['.css', '.js']);
const bladeFiles = walk(path.join(root, 'resources', 'views'), ['.blade.php']);
const allFiles = [...publicFiles, ...bladeFiles];

const patterns = {
    keyframes: /@keyframes\s+([a-zA-Z0-9_-]+)/g,
    animation: /animation\s*:\s*([^;}\n]+)/g,
    transition: /transition\s*:\s*([^;}\n]+)/g,
    transform: /transform\s*:\s*([^;}\n]+)/g,
    rotate: /rotate\s*\(([^)]+)\)/g,
    scale: /scale\s*\(([^)]+)\)/g,
    translate: /translate(X|Y|3d)?\s*\(([^)]+)\)/g,
    hoverAnimation: /:hover[^{]*\{[^}]*(?:animation|transform)[^}]*\}/gs
};

const findings = {
    keyframes: [],
    animation: [],
    transition: [],
    transform: [],
    rotate: [],
    scale: [],
    translate: []
};

allFiles.forEach(file => {
    const rel = path.relative(root, file);
    const content = fs.readFileSync(file, 'utf8');

    // Keyframes
    for (const m of content.matchAll(patterns.keyframes)) {
        findings.keyframes.push({ file: rel, name: m[1] });
    }

    // Animation
    for (const m of content.matchAll(patterns.animation)) {
        findings.animation.push({ file: rel, val: m[1].trim() });
    }

    // Transition
    for (const m of content.matchAll(patterns.transition)) {
        findings.transition.push({ file: rel, val: m[1].trim() });
    }

    // Transform
    for (const m of content.matchAll(patterns.transform)) {
        findings.transform.push({ file: rel, val: m[1].trim() });
    }

    // Rotate
    for (const m of content.matchAll(patterns.rotate)) {
        findings.rotate.push({ file: rel, val: m[1].trim() });
    }

    // Scale
    for (const m of content.matchAll(patterns.scale)) {
        findings.scale.push({ file: rel, val: m[1].trim() });
    }

    // Translate
    for (const m of content.matchAll(patterns.translate)) {
        findings.translate.push({ file: rel, type: m[1] || '', val: m[2].trim() });
    }
});

console.log('=== ANIMATION / MOTION AUDIT ===');
console.log(`Keyframes definitions: ${findings.keyframes.length}`);
const uniqueKeyframes = [...new Set(findings.keyframes.map(k => `${k.name} (${k.file})`))];
uniqueKeyframes.forEach(k => console.log(`  - ${k}`));

console.log(`\nAnimation usages: ${findings.animation.length}`);
findings.animation.slice(0, 30).forEach(a => console.log(`  [${a.file}] ${a.val}`));
if (findings.animation.length > 30) console.log(`  ... and ${findings.animation.length - 30} more`);

console.log(`\nTransitions total: ${findings.transition.length}`);
const transitionByFile = {};
findings.transition.forEach(t => {
    transitionByFile[t.file] = (transitionByFile[t.file] || 0) + 1;
});
Object.entries(transitionByFile).sort((a,b)=>b[1]-a[1]).forEach(([f, c]) => console.log(`  ${f}: ${c} transitions`));

console.log(`\nTransforms total: ${findings.transform.length}`);
const transformByFile = {};
findings.transform.forEach(t => {
    transformByFile[t.file] = (transformByFile[t.file] || 0) + 1;
});
Object.entries(transformByFile).sort((a,b)=>b[1]-a[1]).forEach(([f, c]) => console.log(`  ${f}: ${c} transforms`));

console.log(`\nRotate usages: ${findings.rotate.length}`);
findings.rotate.forEach(r => console.log(`  [${r.file}] rotate(${r.val})`));

console.log(`\nScale usages: ${findings.scale.length}`);
findings.scale.forEach(s => console.log(`  [${s.file}] scale(${s.val})`));

