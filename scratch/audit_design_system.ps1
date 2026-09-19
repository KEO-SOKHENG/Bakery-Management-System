$files = Get-ChildItem -Path "resources\views" -Recurse -Filter "*.blade.php"

# 1. Inline style count
$totalInlineStyles = 0
$filesWithInlineStyles = @{}

foreach ($f in $files) {
    $lines = Get-Content $f.FullName
    $fileInlineCount = 0
    for ($i = 0; $i -lt $lines.Length; $i++) {
        if ($lines[$i] -match 'style\s*=\s*["'']') {
            $totalInlineStyles++
            $fileInlineCount++
        }
    }
    if ($fileInlineCount -gt 0) {
        $rel = $f.FullName.Replace((Get-Location).Path + "\", "")
        $filesWithInlineStyles[$rel] = $fileInlineCount
    }
}

Write-Host "=========================================="
Write-Host "INLINE STYLES COUNT"
Write-Host "Total lines with style=... : $totalInlineStyles"
Write-Host "Top files with inline styles:"
$filesWithInlineStyles.GetEnumerator() | Sort-Object -Property Value -Descending | Select-Object -First 20 | ForEach-Object {
    Write-Host "  $($_.Key) : $($_.Value) lines"
}

# 2. Button classes / variants
$btnClasses = @{}
foreach ($f in $files) {
    $content = [System.IO.File]::ReadAllText($f.FullName)
    $matches = [regex]::Matches($content, 'class\s*=\s*["'']([^"'']*)["'']')
    foreach ($m in $matches) {
        $clsList = $m.Groups[1].Value.Split(" `t`r`n", [System.StringSplitOptions]::RemoveEmptyEntries)
        foreach ($c in $clsList) {
            if ($c -match 'btn' -or $c -match 'button') {
                $btnClasses[$c] = ($btnClasses[$c] + 1)
            }
        }
    }
}
Write-Host "`n=========================================="
Write-Host "BUTTON CLASSES FOUND IN BLADE FILES"
Write-Host "Unique button-related classes: $($btnClasses.Keys.Count)"
$btnClasses.GetEnumerator() | Sort-Object -Property Value -Descending | ForEach-Object {
    Write-Host "  $($_.Key) : $($_.Value) times"
}

# 3. Card classes found
$cardClasses = @{}
foreach ($f in $files) {
    $content = [System.IO.File]::ReadAllText($f.FullName)
    $matches = [regex]::Matches($content, 'class\s*=\s*["'']([^"'']*)["'']')
    foreach ($m in $matches) {
        $clsList = $m.Groups[1].Value.Split(" `t`r`n", [System.StringSplitOptions]::RemoveEmptyEntries)
        foreach ($c in $clsList) {
            if ($c -match 'card') {
                $cardClasses[$c] = ($cardClasses[$c] + 1)
            }
        }
    }
}
Write-Host "`n=========================================="
Write-Host "CARD CLASSES FOUND IN BLADE FILES"
Write-Host "Unique card-related classes: $($cardClasses.Keys.Count)"
$cardClasses.GetEnumerator() | Sort-Object -Property Value -Descending | Select-Object -First 20 | ForEach-Object {
    Write-Host "  $($_.Key) : $($_.Value) times"
}

# 4. Modals
$modalFiles = @{}
foreach ($f in $files) {
    $content = [System.IO.File]::ReadAllText($f.FullName)
    if ($content -match 'modal' -or $content -match 'x-modal') {
        $matches = [regex]::Matches($content, 'id\s*=\s*["'']([^"'']*modal[^"'']*)["'']', [System.Text.RegularExpressions.RegexOptions]::IgnoreCase)
        $rel = $f.FullName.Replace((Get-Location).Path + "\", "")
        if ($matches.Count -gt 0) {
            $ids = ($matches | ForEach-Object { $_.Groups[1].Value }) -join ', '
            $modalFiles[$rel] = "$($matches.Count) modals ($ids)"
        }
    }
}
Write-Host "`n=========================================="
Write-Host "MODAL IMPLEMENTATIONS IN BLADE FILES"
$modalFiles.GetEnumerator() | ForEach-Object {
    Write-Host "  $($_.Key) : $($_.Value)"
}

# 5. Alert implementations
$alertFiles = @{}
foreach ($f in $files) {
    $content = [System.IO.File]::ReadAllText($f.FullName)
    $rel = $f.FullName.Replace((Get-Location).Path + "\", "")
    $c1 = ([regex]::Matches($content, 'class\s*=\s*["''][^"'']*alert[^"'']*["''])).Count
    $c2 = ([regex]::Matches($content, '<x-alert[\s>]')).Count
    if ($c1 -gt 0 -or $c2 -gt 0) {
        $alertFiles[$rel] = "class alert: $c1, x-alert: $c2"
    }
}
Write-Host "`n=========================================="
Write-Host "ALERT IMPLEMENTATIONS IN BLADE FILES"
$alertFiles.GetEnumerator() | ForEach-Object {
    Write-Host "  $($_.Key) : $($_.Value)"
}
