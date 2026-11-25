$ErrorActionPreference = 'Stop'
$projectRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
Push-Location $projectRoot
try {
    $paths = @(git log --no-merges --author=omar.altoom.ot@gmail.com --name-only --format= | Where-Object { $_.Trim() } | Sort-Object -Unique)
    $existing = @($paths | Where-Object { (Test-Path -LiteralPath $_ -PathType Leaf) -and $_ -notmatch '^(vendor|node_modules)/' })
    $removed = @($paths | Where-Object { -not (Test-Path -LiteralPath $_ -PathType Leaf) -and $_ -notmatch '^(vendor|node_modules)/' })
    $dependencies = @($paths | Where-Object { $_ -match '^(vendor|node_modules)/' })
    $lines = @('OMAR ALTOOM - FILE CONTRIBUTION INVENTORY', '', 'Source: Git commits authored by omar.altoom.ot@gmail.com.', 'Non-merge commits only; merge commits are excluded so teammates work imported by merges is not attributed to Omar.', 'These files were added, modified, renamed or deleted by at least one authored commit. This does not imply exclusive ownership or authorship of their current full contents.', '', "Project root: $projectRoot", "Unique historical paths: $($paths.Count)", "Existing project files: $($existing.Count)", "Removed or renamed project paths: $($removed.Count)", "Dependency paths: $($dependencies.Count)", '', '=== EXISTING PROJECT FILES ===')
    foreach ($group in ($existing | Group-Object { ($_ -split '/')[0] } | Sort-Object Name)) {
        $lines += ''; $lines += ('[' + $group.Name + ']')
        $lines += $group.Group
    }
    $lines += @('', '=== REMOVED OR RENAMED HISTORICAL PROJECT PATHS ===')
    $lines += $removed
    $lines += @('', '=== DEPENDENCY PATHS (VENDOR / NODE_MODULES) ===')
    $lines += $dependencies
    $lines += @('', '=== SOURCE COMMITS ===')
    $lines += @(git log --no-merges --author=omar.altoom.ot@gmail.com --format='%h | %ad | %s' --date=short)
    $lines | Set-Content -LiteralPath (Join-Path $PSScriptRoot 'omar-files.txt') -Encoding UTF8
    [pscustomobject]@{ total=$paths.Count; existing=$existing.Count; removed=$removed.Count; dependencies=$dependencies.Count; report=(Join-Path $PSScriptRoot 'omar-files.txt') } | ConvertTo-Json -Compress
} finally { Pop-Location }
