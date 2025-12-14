@echo off
echo Copying language flags...

copy /Y "..\ai-bot-front-main\public\img\lang\ru.png" "src\renderer\public\img\ru.png"
copy /Y "..\ai-bot-front-main\public\img\lang\en.png" "src\renderer\public\img\en.png"
copy /Y "..\ai-bot-front-main\public\img\lang\uk.png" "src\renderer\public\img\uk.png"

echo.
echo Flags copied successfully!
echo.
dir src\renderer\public\img\*.png
pause




