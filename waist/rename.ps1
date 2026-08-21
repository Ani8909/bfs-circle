$files = Get-ChildItem -Path . -Recurse -Include *.php, *.js, *.html

foreach ($file in $files) {
    $content = Get-Content $file.FullName -Raw
    $original = $content
    
    $content = $content -replace "AuraCRM", "BFS Financial Services"
    $content = $content -replace "Aura CRM", "BFS Financial Services"
    $content = $content -replace "BFS FinCircle", "BFS Financial Services"
    $content = $content -replace "FinCircle", "BFS Financial Services"
    $content = $content -replace '<span class="p1">Aura</span><span class="p2">CRM</span>', '<div style="display:flex; justify-content:center; align-items:center; gap:10px; margin-bottom:15px;"><img src="logo.png" alt="Logo" style="height:40px;"> <span style="font-family:''Outfit'',sans-serif; font-weight:800; font-size:24px; color:var(--primary);">BFS Financial Services</span></div>'
    
    if ($content -cne $original) {
        Write-Host "Updated $($file.Name)"
        Set-Content -Path $file.FullName -Value $content -NoNewline
    }
}
