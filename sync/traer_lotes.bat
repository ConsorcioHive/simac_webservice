@echo off
REM sync\traer_lotes.bat — Task Scheduler cada 2 minutos
REM Baja lotes de egreso pendientes de la nube y los deja "disponible" para el ERP.
setlocal
set PHP=C:\xampp\php\php.exe
set SCRIPT=C:\xampp\htdocs\simac_webservice\sync\traer_lotes.php
set LOGDIR=C:\xampp\htdocs\simac_webservice\storage\logs
if not exist "%LOGDIR%" mkdir "%LOGDIR%"
"%PHP%" "%SCRIPT%" >> "%LOGDIR%\traer_lotes.log" 2>&1
echo. >> "%LOGDIR%\traer_lotes.log"
endlocal
