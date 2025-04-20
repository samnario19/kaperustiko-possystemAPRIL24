@echo off
echo Starting Apache...
cd /d C:\users\miked\xampp
echo Current directory: %cd%
start apache_start.bat
echo Apache started.
echo Starting MySQL...
start mysql_start.bat
echo MySQL started.