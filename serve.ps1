# Zero-Dependency Local Static Web Server for Barangay Management System
# Uses built-in Windows .NET HttpListener

$Port = 3000
$Prefix = "http://localhost:$Port/"
$Folder = $PSScriptRoot
if ([string]::IsNullOrWhiteSpace($Folder)) {
    $Folder = (Get-Location).Path
}

$Listener = New-Object System.Net.HttpListener
$Listener.Prefixes.Add($Prefix)

try {
    $Listener.Start()
    Write-Host "============================================================" -ForegroundColor Cyan
    Write-Host "  BARANGAY MANAGEMENT SYSTEM - LOCAL WEB SERVER" -ForegroundColor White
    Write-Host "============================================================" -ForegroundColor Cyan
    Write-Host "  URL:     $Prefix" -ForegroundColor Green
    Write-Host "  Folder:  $Folder" -ForegroundColor DarkGray
    Write-Host "  Status:  Running (Press Ctrl+C to stop)" -ForegroundColor Yellow
    Write-Host "============================================================" -ForegroundColor Cyan

    # Auto launch default browser
    Start-Process $Prefix

    while ($Listener.IsListening) {
        try {
            $Context = $Listener.GetContext()
            $Request = $Context.Request
            $Response = $Context.Response

            $UrlPath = $Request.Url.LocalPath
            if ($UrlPath -eq "/" -or [string]::IsNullOrWhiteSpace($UrlPath)) {
                $UrlPath = "/index.html"
            }

            # Security check: sanitize path
            $SafePath = $UrlPath.TrimStart('/').Replace('/', '\')
            $FilePath = Join-Path $Folder $SafePath

            if (Test-Path $FilePath -PathType Leaf) {
                $Extension = [System.IO.Path]::GetExtension($FilePath).ToLower()
                $ContentType = switch ($Extension) {
                    ".html" { "text/html; charset=utf-8" }
                    ".php"  { "text/html; charset=utf-8" }
                    ".css"  { "text/css; charset=utf-8" }
                    ".js"   { "application/javascript; charset=utf-8" }
                    ".json" { "application/json; charset=utf-8" }
                    ".png"  { "image/png" }
                    ".jpg"  { "image/jpeg" }
                    ".jpeg" { "image/jpeg" }
                    ".svg"  { "image/svg+xml" }
                    ".ico"  { "image/x-icon" }
                    default { "application/octet-stream" }
                }

                $Bytes = [System.IO.File]::ReadAllBytes($FilePath)
                $Response.ContentType = $ContentType
                $Response.ContentLength64 = $Bytes.Length
                $Response.StatusCode = 200
                $Response.OutputStream.Write($Bytes, 0, $Bytes.Length)
            } else {
                $Response.StatusCode = 404
                $404Bytes = [System.Text.Encoding]::UTF8.GetBytes("404 Not Found: $UrlPath")
                $Response.ContentType = "text/plain; charset=utf-8"
                $Response.ContentLength64 = $404Bytes.Length
                $Response.OutputStream.Write($404Bytes, 0, $404Bytes.Length)
            }

            $Response.OutputStream.Close()
        } catch {
            Write-Warning "Request processing error: $_"
        }
    }
} catch {
    Write-Error $_
} finally {
    if ($Listener.IsListening) {
        $Listener.Stop()
    }
    $Listener.Close()
}
