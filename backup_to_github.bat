@echo off
cd /d C:\xampp\htdocs\mims
git init
git add .
git commit -m "Initial backup"
git remote add origin https://github.com/yro1278/Fundharmony.git
git push -u origin master
pause
