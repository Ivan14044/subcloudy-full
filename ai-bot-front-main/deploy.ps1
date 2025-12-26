# Скрипт для деплоя на сервер
$server = "193.111.63.104"
$user = "root"
$password = "HJjuUJ73j0qE18hF2w"
$remotePath = "/var/www/html"
$localPath = "dist"

# Проверяем наличие Posh-SSH модуля
if (-not (Get-Module -ListAvailable -Name Posh-SSH)) {
    Write-Host "Устанавливаем модуль Posh-SSH..."
    Install-Module -Name Posh-SSH -Force -Scope CurrentUser -AllowClobber
}

Import-Module Posh-SSH

# Создаем credentials
$securePassword = ConvertTo-SecureString $password -AsPlainText -Force
$credential = New-Object System.Management.Automation.PSCredential($user, $securePassword)

Write-Host "Подключаемся к серверу..."
$session = New-SSHSession -ComputerName $server -Credential $credential -AcceptKey

if ($session) {
    Write-Host "Копируем файлы на сервер..."
    Set-SCPFile -ComputerName $server -Credential $credential -LocalFile "$localPath\index.html" -RemotePath $remotePath
    Set-SCPItem -ComputerName $server -Credential $credential -LocalItem "$localPath\*" -RemotePath $remotePath -Recurse
    Write-Host "Файлы успешно скопированы!"
    Remove-SSHSession -SessionId $session.SessionId
} else {
    Write-Host "Ошибка подключения к серверу"
}



