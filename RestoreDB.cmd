@echo off

cd C:\Users\franc\OneDrive\devenv\htdocs\DnD

set DB_USER=root
set DB_PASS=

set DB_BIN=C:\xampp\mysql\bin\

set TARGET_DB=%~1

if "%TARGET_DB%"=="" (
    echo No target database provided.
    echo Using fallback target database "dnd_test".
    set TARGET_DB=dnd_test
) else (
    echo Target database provided: %TARGET_DB%
)
set DUMP_FILE="C:\Users\franc\OneDrive\devenv\Backup-%TARGET_DB%.sql"

:: Check if the file exists
if exist "%DUMP_FILE%" (
    goto RESTORE
) else (
    set ERROR_MSG=Backup file does not exist: "%DUMP_FILE%"
	goto ERROR
)

:RESTORE
:: ==========================================
:: EXECUTION
:: ==========================================
echo ==========================================
echo Starting database refresh process
echo Destination Database: %TARGET_DB%
echo Target Dump File:     %DUMP_FILE%
echo ==========================================
echo.


:: 1. Drop the existing destination database
echo [1/3] Dropping destination database '%TARGET_DB%' (if it exists)...
"%DB_BIN%mysql" -u%DB_USER% -e "DROP DATABASE IF EXISTS %TARGET_DB%;"
if %ERRORLEVEL% NEQ 0 goto ERROR

:: 2. Create a fresh destination database
echo [2/3] Creating destination database '%TARGET_DB%'...
"%DB_BIN%mysql" -u%DB_USER% -e "CREATE DATABASE %TARGET_DB% CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
if %ERRORLEVEL% NEQ 0 goto ERROR

:: 3. Import the SQL dump
echo [3/3] Importing %DUMP_FILE% into '%TARGET_DB%'...
"%DB_BIN%mysql" -u%DB_USER% %TARGET_DB% < %DUMP_FILE%
if %ERRORLEVEL% NEQ 0 goto ERROR

echo ==========================================
echo SUCCESS: %TARGET_DB% successfully restored!
echo ==========================================
goto END

:ERROR
echo ==========================================
echo ERROR: An error occurred during execution. %ERROR_MSG%
echo ==========================================

:END
echo.
