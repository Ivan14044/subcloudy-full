@echo off
echo Deploying to server...
echo.
echo Please use one of the following methods:
echo.
echo Method 1: Using WinSCP (if installed)
echo   - Open WinSCP
echo   - Connect to 193.111.63.104 with user root and password HJjuUJ73j0qE18hF2w
echo   - Copy all files from dist folder to /var/www/html/
echo.
echo Method 2: Using SCP command (if available)
echo   scp -r dist/* root@193.111.63.104:/var/www/html/
echo.
echo Method 3: Using SSH and tar
echo   tar -czf dist.tar.gz -C dist .
echo   scp dist.tar.gz root@193.111.63.104:/tmp/
echo   ssh root@193.111.63.104 "cd /var/www/html && tar -xzf /tmp/dist.tar.gz"
echo.
pause

