ssh root@82.198.225.45

cd /
cd home/ahmed-albakor-rwady-backend/htdocs/rwady-backend.ahmed-albakor.com
git pull


ssh -t root@82.198.225.45 "cd /home/ahmed-albakor-rwady-backend/htdocs/rwady-backend.ahmed-albakor.com && git pull"


backend.rwady.com

cd /
cd home/rwady-backend/htdocs/backend.rwady.com
git pull

ssh -t root@82.198.225.45 "cd /home/rwady-backend/htdocs/backend.rwady.com && php artisan import:products storage/files/catalog_2025-08-04_16-00.csv"



5213 7203 0423 8582 
01/32
642


// setup laravel 11 project on vps server

1 .cloen the project
2. install composer
3. create database and user
4. create .env file
 - set database connection
 - set app key // php artisan key:generate
 - set app url
 - set app debug
 - set app timezone
 - set app locale
5. set Domin Root Directory to public folder
6. change permissions of storage/framework and storage/logs
7. run migrations // php artisan migrate
8. run seeders // php artisan db:seed
9. storage link // 
  // php artisan storage:link
  // change permissions of storage/app permissions to 777
  // chown root:root storage/app -R

php artisan import:products storage/files/catalog_2025-08-04_16-00.csv