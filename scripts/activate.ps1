$projectRoot = Split-Path -Parent $PSScriptRoot
$toolsPath = Join-Path $projectRoot '.tools'

if (-not (Test-Path -LiteralPath (Join-Path $toolsPath 'php\php.exe'))) {
    throw 'Project-local PHP is missing. Install PHP 8.3+ and Composer, or restore the local .tools installation.'
}

$env:PATH = "$toolsPath;$(Join-Path $toolsPath 'php');$env:PATH"
$env:COMPOSER_HOME = Join-Path $toolsPath 'composer-home'
$env:COMPOSER_CACHE_DIR = Join-Path $toolsPath 'composer-cache'
$env:npm_config_cache = Join-Path $toolsPath 'npm-cache'

Write-Host 'Project PHP and Composer activated for this terminal.'
