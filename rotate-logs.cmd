@echo off
setlocal enabledelayedexpansion
c:
cd \temp

:: --- Configuration ---
set "sourceFile=%1"
set "n=%2"

:: Check argument count
if "%n%"=="" (
   set "n=5"
)

:: Check if source file exists
if not exist "%sourceFile%" (
    echo Le fichier %sourceFile% est introuvable.
	type nul > "%sourceFile%"
	pause
    exit /b
)

:: --- Rotate ---
:: 1. Delete oldest version (n)
if exist "%sourceFile%.%n%" del "%sourceFile%.%n%"

:: 2. Version shift (from n-1 to n, from n-2 to n-1, etc.)
for /L %%i in (%n%, -1, 2) do (
    set /a prev=%%i-1
    if exist "%sourceFile%.!prev!" (
        ren "%sourceFile%.!prev!" "%sourceFile%.%%i"
    )
)

:: 3. Rename the current file to Version 1
ren "%sourceFile%" "%sourceFile%.1"

:: 4. Create a new empty file (optional; uncomment if necessary)
type nul > "%sourceFile%"

echo Cycle %sourceFile% completed: %n% versions retained.
