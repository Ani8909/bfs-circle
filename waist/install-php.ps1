$Url = "https://windows.php.net/downloads/releases/php-8.2.33-nts-Win32-vs16-x64.zip"
$ZipFile = "php.zip"
$ExtractPath = ".\php-portable"

Write-Host "Downloading PHP..."
Invoke-WebRequest -Uri $Url -OutFile $ZipFile

Write-Host "Extracting PHP..."
Expand-Archive -Path $ZipFile -DestinationPath $ExtractPath -Force

Write-Host "Configuring PHP..."
Copy-Item -Path "$ExtractPath\php.ini-development" -Destination "$ExtractPath\php.ini"

$ConfigContent = Get-Content -Path "$ExtractPath\php.ini"
$ConfigContent = $ConfigContent -replace ';extension=pdo_sqlite', 'extension=pdo_sqlite'
$ConfigContent = $ConfigContent -replace ';extension=sqlite3', 'extension=sqlite3'
$ConfigContent = $ConfigContent -replace ';extension_dir = "ext"', 'extension_dir = "ext"'
Set-Content -Path "$ExtractPath\php.ini" -Value $ConfigContent

Write-Host "PHP Installed."
Remove-Item -Path $ZipFile -Force
