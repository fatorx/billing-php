#  Billing

This system aims to control billings and manage payments of these billings.

------

## Functionalities

------

### Sequence Receive File

------
### Technologies
- PHP 8.3 with Laminas
- MySQL 8.0.32
- RabbitMQ 3.10.1

------

## Instructions for run this app:

### First time

Clone project in your projects folder.
```shell script
$ git clone git@github.com:fatorx/billing.git && cd billing
```
Copy .env.dist to .env and adjust values in the .env file to your preferences.
```shell script
cp .env.dist .env 
```

Add permissions to folder data (MySQL and RabbitMQ) and api/data (logs, storage files), this is where the persistence files will be kept.
```shell script
chmod -R 755 data
```
```shell script
chmod -R 755 api/storage
```

Mount the environment based in docker-compose.yml.
```shell script
docker-compose up -d --build
```
Run composer
```shell script
docker exec app-billing-php-fpm php composer.phar install
```
Run migrate
```shell script
docker exec app-billing-php-fpm php artisan migrate
```

------
### Working routine
```shell script
docker-compose up -d
```
------

### Access to environment
###
Test to send a file:
```shell script
curl --location '0.0.0.0:8009/api/billings/upload' \
--form 'file=@"/home/yourpath/projects/billing/temp/test_length_ok.csv"'
```

------
### Tests Outside Docker
```shell script
docker exec -it app-billing-php-fpm php artisan test
```



