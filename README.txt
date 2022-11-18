Create database "news_project"

php bin/console make:migration

php bin/console doctrine:migrations:migrate

composer dump -o

composer dump-autoload

php -S localhost:3000 -t public

php bin/console news //Command for cron

http://localhost:3000/login //login

http://localhost:3000/article //get news or articles
