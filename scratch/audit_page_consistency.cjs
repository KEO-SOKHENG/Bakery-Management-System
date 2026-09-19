const fs = require('fs');
const path = require('path');

const majorPages = [
    { name: 'Login', file: 'resources/views/login.blade.php' },
    { name: 'Admin Dashboard', file: 'resources/views/admin/dashboard.blade.php' },
    { name: 'Manager Dashboard', file: 'resources/views/manager/dashboard.blade.php' },
    { name: 'Cashier Dashboard', file: 'resources/views/cashier/dashboard.blade.php' },
    { name: 'POS', file: 'resources/views/pos/index.blade.php' },
    { name: 'Orders Index', file: 'resources/views/admin/orders/index.blade.php' },
    { name: 'Orders Show', file: 'resources/views/admin/orders/show.blade.php' },
    { name: 'Products', file: 'resources/views/admin/products.blade.php' },
    { name: 'Categories', file: 'resources/views/admin/categories.blade.php' },
    { name: 'Ingredients', file: 'resources/views/admin/ingredients.blade.php' },
    { name: 'Recipes', file: 'resources/views/admin/recipes.blade.php' },
    { name: 'Production', file: 'resources/views/admin/production.blade.php' },
    { name: 'Suppliers', file: 'resources/views/admin/suppliers.blade.php' },
    { name: 'Customers', file: 'resources/views/admin/customers/index.blade.php' },
    { name: 'Users', file: 'resources/views/admin/users/index.blade.php' },
    { name: 'HR Salaries', file: 'resources/views/admin/hr/salaries.blade.php' },
    { name: 'HR Attendance', file: 'resources/views/admin/hr/attendance.blade.php' },
    { name: 'HR Schedules', file: 'resources/views/admin/hr/schedules.blade.php' },
    { name: 'Reports', file: 'resources/views/admin/reports.blade.php' },
    { name: 'Notifications', file: 'resources/views/admin/notifications/index.blade.php' },
    { name: 'Settings', file: 'resources/views/admin/settings.blade.php' },
];

const results = [];

majorPages.forEach(p => {
    const fullPath = path.join(__dirname, '..', p.file);
    if (!fs.existsSync(fullPath)) {
        results.push({ name: p.name, error: 'FILE NOT FOUND' });
        return;
    }
    const content = fs.readFileSync(fullPath, 'utf8');
    const lines = content.split('\n');

    let inlineStyles = 0;
    lines.forEach(l => {
        if (/style\s*=\s*["']/.test(l)) inlineStyles++;
    });

    const hasLayout = content.includes("@extends('layouts.app')");
    const hasPageHeaderComp = content.includes('<x-page-header');
    const hasCardComp = content.includes('<x-card');
    const hasButtonComp = content.includes('<x-button');
    const hasBadgeComp = content.includes('<x-badge');
    const hasAlertComp = content.includes('<x-alert');
    const hasModalComp = content.includes('<x-modal');
    const hasInputComp = content.includes('<x-input');
    const hasSelectComp = content.includes('<x-select');

    const hasStandardBtn = /class\s*=\s*["'][^"']*\bbtn-(primary|secondary|danger|success|outline|ghost)\b[^"']*["']/.test(content);
    const hasCustomBtn = /class\s*=\s*["'][^"']*\bbtn-(user|cust|notif|prod|ios|card-outline)\b[^"']*["']/.test(content);

    const hasCardClass = /class\s*=\s*["'][^"']*\b(card|bakery-card|stat-card)\b[^"']*["']/.test(content);
    const hasCustomCardClass = /class\s*=\s*["'][^"']*\b(settings-glass-card|users-card|customers-card|prod-toolbar-card|recipe-card|category-card)\b[^"']*["']/.test(content);

    const hasTable = content.includes('<table');
    const hasModal = /id\s*=\s*["'][^"']*modal[^"']*["']/i.test(content);

    results.push({
        name: p.name,
        file: p.file,
        layout: hasLayout,
        inlineStyles,
        pageHeader: hasPageHeaderComp ? '<x-page-header>' : (content.includes('top-header-bar') || content.includes('header_title') ? 'Layout/TopBar' : (content.includes('page-header') ? '.page-header' : 'Custom/None')),
        componentsUsed: [
            hasPageHeaderComp && 'page-header',
            hasCardComp && 'card',
            hasButtonComp && 'button',
            hasBadgeComp && 'badge',
            hasAlertComp && 'alert',
            hasModalComp && 'modal',
            hasInputComp && 'input',
            hasSelectComp && 'select'
        ].filter(Boolean),
        buttonSystem: hasCustomBtn ? 'Custom module classes' : (hasButtonComp ? '<x-button>' : (hasStandardBtn ? '.btn standard' : 'None/Other')),
        cardSystem: hasCustomCardClass ? 'Custom card classes' : (hasCardComp ? '<x-card>' : (hasCardClass ? 'Standard card' : 'None/Other')),
        hasTable,
        hasModal
    });
});

console.log(JSON.stringify(results, null, 2));
