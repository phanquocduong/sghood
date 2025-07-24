@echo off
REM Laravel Queue Management Script for Windows
REM Usage: queue.bat [start|stop|restart|status|monitor]

set ARTISAN_PATH=php artisan

if "%1"=="start" goto START
if "%1"=="stop" goto STOP
if "%1"=="restart" goto RESTART
if "%1"=="status" goto STATUS
if "%1"=="monitor" goto MONITOR
if "%1"=="retry-all" goto RETRY_ALL
if "%1"=="clear-failed" goto CLEAR_FAILED
goto HELP

:START
echo Starting Laravel Queue Worker...
start /B %ARTISAN_PATH% queue:work --queue=default --sleep=3 --tries=3 --max-time=3600
echo Queue worker started
goto END

:STOP
echo Stopping Laravel Queue Worker...
%ARTISAN_PATH% queue:restart
echo Queue worker stopped
goto END

:RESTART
echo Restarting Laravel Queue Worker...
%ARTISAN_PATH% queue:restart
timeout /t 2 /nobreak > nul
call %0 start
goto END

:STATUS
echo Checking queue worker status...
tasklist /FI "IMAGENAME eq php.exe" /FI "WINDOWTITLE eq*queue:work*" 2>nul | find /I "php.exe" >nul
if %ERRORLEVEL%==0 (
    echo Queue worker is running
) else (
    echo Queue worker is not running
)
goto END

:MONITOR
echo Monitoring queue status...
echo ----------------------------------------
echo Jobs in queue:
%ARTISAN_PATH% queue:monitor
echo ----------------------------------------
echo Failed jobs:
%ARTISAN_PATH% queue:failed
goto END

:RETRY_ALL
echo Retrying all failed jobs...
%ARTISAN_PATH% queue:retry all
goto END

:CLEAR_FAILED
echo Clearing all failed jobs...
%ARTISAN_PATH% queue:flush
goto END

:HELP
echo Usage: %0 [start^|stop^|restart^|status^|monitor^|retry-all^|clear-failed]
echo.
echo Commands:
echo   start       - Start queue worker
echo   stop        - Stop queue worker
echo   restart     - Restart queue worker
echo   status      - Check worker status
echo   monitor     - Monitor queue status
echo   retry-all   - Retry all failed jobs
echo   clear-failed- Clear all failed jobs
goto END

:END
