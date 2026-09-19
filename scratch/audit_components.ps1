$files = Get-ChildItem -Path "resources\views" -Recurse -Filter "*.blade.php"
Write-Host "Total blade files: $($files.Count)"

$components = @('x-button', 'x-card', 'x-badge', 'x-alert', 'x-input', 'x-select', 'x-modal', 'x-page-header')

foreach ($comp in $components) {
    $count = 0
    $pages = @()
    foreach ($f in $files) {
        $content = [System.IO.File]::ReadAllText($f.FullName)
        $matches = [regex]::Matches($content, "<$comp[\s>/]")
        if ($matches.Count -gt 0) {
            $count += $matches.Count
            $rel = $f.FullName.Replace((Get-Location).Path + "\", "")
            $pages += "    - $rel ($($matches.Count))"
        }
    }
    Write-Host "`n=== Component: <$comp> (Total: $count usages) ==="
    if ($pages.Count -gt 0) {
        $pages | ForEach-Object { Write-Host $_ }
    } else {
        Write-Host "    (No usages found)"
    }
}
