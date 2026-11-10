@echo off
echo ================================================
echo   INNOVAN-CI - API Flask de prediction
echo ================================================
echo.

set INNOVAN_MODELS_DIR=C:\Hackathon\tutore_2\innovan\models
set INNOVAN_DATA_DIR=C:\Hackathon\tutore_2\innovan\Data
set INNOVAN_PORT=5001

echo Dossier modeles : %INNOVAN_MODELS_DIR%
echo Dossier data    : %INNOVAN_DATA_DIR%
echo Port            : %INNOVAN_PORT%
echo.

py -m pip install -r requirements_api.txt --quiet
py api_innovan.py

pause
