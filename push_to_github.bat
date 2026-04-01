@echo off
cd /d C:\xampp\htdocs\mims
git remote set-url origin https://github.com/yro1278/Fundharmony.git
git add .
git commit -m "Update"
git push -u origin main
pause
