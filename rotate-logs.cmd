@echo off
setlocal enabledelayedexpansion
c:
cd \temp

:: --- Configuration ---
set "fichierSource=%1"
set "n=%2"

:: V‚rification des arguments
if "%n%"=="" (
   set "n=5"
)

:: V‚rification si le fichier source existe
if not exist "%fichierSource%" (
    echo Le fichier %fichierSource% est introuvable.
	pause
    exit /b
)

:: --- Rotation ---
:: 1. Suppression de la version la plus ancienne (n)
if exist "%fichierSource%.%n%" del "%fichierSource%.%n%"

:: 2. D‚calage des versions (de n-1 vers n, n-2 vers n-1, etc.)
for /L %%i in (%n%, -1, 2) do (
    set /a prev=%%i-1
    if exist "%fichierSource%.!prev!" (
        ren "%fichierSource%.!prev!" "%fichierSource%.%%i"
    )
)

:: 3. Renommage du fichier actuel en version 1
ren "%fichierSource%" "%fichierSource%.1"

:: 4. Cr‚ation d'un nouveau fichier vide (optionnel, … d‚commenter si besoin)
:: type nul > "%fichierSource%"

echo Rotation fichier %fichierSource% termin‚e : %n% versions conserv‚es.
