$utf8NoBom = New-Object System.Text.UTF8Encoding($false)

$targets = @(
    "app\Models\*.php",
    "database\seeders\*.php",
    "routes\api.php"
)

foreach ($target in $targets) {
    Get-ChildItem $target -File -ErrorAction SilentlyContinue | ForEach-Object {
        $path = $_.FullName
        $text = [System.IO.File]::ReadAllText($path)

        # Hapus BOM kalau ada
        $text = $text -replace "^\uFEFF", ""

        # Cari tag PHP pertama
        $index = $text.IndexOf("<?php")

        if ($index -ge 0) {
            # Buang semua karakter sebelum <?php
            $text = $text.Substring($index)
        }

        # Simpan ulang UTF-8 tanpa BOM
        [System.IO.File]::WriteAllText($path, $text.TrimEnd() + "`r`n", $utf8NoBom)

        Write-Host "Fixed:" $_.FullName -ForegroundColor Green
    }
}

Write-Host ""
Write-Host "Selesai. Semua file PHP sudah dibersihkan dari BOM/spasi sebelum <?php." -ForegroundColor Cyan