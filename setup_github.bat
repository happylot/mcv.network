@echo off
echo ==========================================
echo   MCV.Network - GitHub Setup Script
echo ==========================================
echo.

cd /d "C:\Users\PhucNguyen\Desktop\MCV.Network"

echo [1/6] Initializing git repository...
git init

echo.
echo [2/6] Adding all files...
git add .

echo.
echo [3/6] Creating initial commit...
git commit -m "Initial commit: MCV Network - Performance Advertising Platform

- Homepage + 48 static marketing pages
- Build system (build.mjs) + serve.mjs
- Design system CSS (assets/css/mcv.css)
- Shared nav/footer JS (assets/js/mcv.js)
- Ad SDK Technical Spec
- Brand Kit (HTML interactive)
- Sitemap (HTML visual)
- Project Overview document
- Logo assets"

echo.
echo [4/6] Setting branch to main...
git branch -M main

echo.
echo [5/6] Adding remote origin...
git remote add origin https://github.com/happylot/mcv.network.git

echo.
echo [6/6] Pushing to GitHub...
git push -u origin main

echo.
echo ==========================================
echo   DONE! Check: https://github.com/happylot/mcv.network
echo ==========================================
pause
