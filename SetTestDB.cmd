@echo off
:: ==========================================
:: CONFIGURATION - Update these values
:: ==========================================

cd C:\Users\franc\OneDrive\devenv\htdocs\DnD

set DB_USER=root
set DB_PASS=
set DB_NAME=dnd_test

:: Optional: Path to MariaDB bin folder if 'mysql'/'mysqldump' is not in your system PATH
set DB_BIN=C:\xampp\mysql\bin\

:: ==========================================
:: PARAMETER & PATH LOGIC
:: ==========================================
set SOURCE_DB=%~1

if "%SOURCE_DB%"=="" (
    echo No source database provided.
    echo Using fallback dump file.
    set DUMP_FILE="C:\Users\franc\OneDrive\devenv\Backup-dnd.sql"
    set RUN_BACKUP=0
) else (
    echo Source database provided: %SOURCE_DB%
    echo Will back up '%SOURCE_DB%' first.
    set DUMP_FILE="C:\temp\dmp.sql"
    set RUN_BACKUP=1
    
    :: Ensure the temp directory exists
    if not exist "C:\temp" mkdir "C:\temp"
)

:: ==========================================
:: EXECUTION
:: ==========================================
echo ==========================================
echo Starting database refresh process
echo Destination Database: %DB_NAME%
echo Target Dump File:     %DUMP_FILE%
echo ==========================================
echo.

rem 1. Optional Backup Step
if %RUN_BACKUP% NEQ 1 goto :SKIP_BACKUP

echo [1/4] Backing up source database '%SOURCE_DB%' to '%DUMP_FILE%'...
"%DB_BIN%mysqldump" -u%DB_USER% -p%DB_PASS% -c %SOURCE_DB% --ignore-table-data=%SOURCE_DB%.access_log > %DUMP_FILE%
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: Backup of '%SOURCE_DB%' failed.
    goto ERROR
)
echo Backup complete
echo.
goto :CONTINUE

:SKIP_BACKUP
echo [1/4] Skipping backup step (no source DB parameter provided).
echo.

:CONTINUE

:: 2. Drop the existing destination database
echo [2/4] Dropping destination database '%DB_NAME%' (if it exists)...
"%DB_BIN%mysql" -u%DB_USER% -p%DB_PASS% -e "DROP DATABASE IF EXISTS %DB_NAME%;"
if %ERRORLEVEL% NEQ 0 goto ERROR

:: 3. Create a fresh destination database
echo [3/4] Creating destination database '%DB_NAME%'...
"%DB_BIN%mysql" -u%DB_USER% -p%DB_PASS% -e "CREATE DATABASE %DB_NAME% CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
if %ERRORLEVEL% NEQ 0 goto ERROR

:: 4. Import the SQL dump
echo [4/4] Importing %DUMP_FILE% into '%DB_NAME%'...
"%DB_BIN%mysql" -u%DB_USER% -p%DB_PASS% %DB_NAME% < %DUMP_FILE%
if %ERRORLEVEL% NEQ 0 goto ERROR

echo ==========================================
echo SUCCESS: Process completed successfully!
echo ==========================================
goto END

:ERROR
echo ==========================================
echo ERROR: An error occurred during execution.
echo ==========================================

:END
echo.
