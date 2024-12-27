@echo off
cd /d "C:\Users\randy\Downloads\Pharmacy project\PharmacyMS-Laravel"
php artisan queue:work --sleep=3 --tries=3
