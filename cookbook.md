# Install Cookbook

Below are instructions for installation of a fully functioning laravel application that uses the jlab/epas package for access to a plant-items database.  It will assume that an oracle instance of the database already exists and that we have login credentials to access it.

## Install Laravel

Instructions for various OS and Docker available at: https://laravel.com/docs/8.x/installation

Example on linux using composer (https://getcomposer.org/). 
```shell
composer create-project laravel/laravel plant-items
cd plant-items
chmod 777 storage/logs
chmod -R 777 storage/framework
```


## Add Required Packages
We will use some JeffersonLab packages that are available on github, but not registered at packagist.  In order to do this, we need to edit the composer.json file in our plant-items directory and add a repositories section:
```json
"repositories": {
  "jlab/epas": {
    "type": "vcs",
    "url": "https://github.com/JeffersonLab/epas.git"   
  },
  "laravel-utilities": {
    "type": "vcs",
    "url": "https://github.com/JeffersonLab/laravel-utilities.git"
    }
},

```

Then we can use composer to require these packages an others such as an oci8 package to interact with Oracle.

```shell
 composer require jlab/epas
 composer require jlab/laravel-utilities
 composer require yajra/laravel-oci8

```

## Add The Oracle Database Connection
Edit the config/database.php file to add the following Oracle database definition beneath connections:

```php
'connections' => [
// ...
    'oracle' => array(
        'driver' => 'oracle',
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '1521'),
        'database' => env('DB_DATABASE', 'xe'),
        'service_name' => env('DB_SERVICE_NAME', ''),
        'username' => env('DB_USERNAME', ''),
        'password' => env('DB_PASSWORD', ''),
        'charset' => 'AL32UTF8',
        'prefix' => '',
    ),
]
```
And then edit the .env file to provide the necessary values for the DB_* environment variables corresponding to the database to be accessed.  Change the DB_CONNECTION variable from mysql to oracle
```shell
DB_CONNECTION=oracle
DB_HOST=127.0.0.1
DB_PORT=1521
DB_DATABASE=xepdb1
DB_SERVICE_NAME=xepdb1
DB_USERNAME=########
DB_PASSWORD=########
```

## Configure and run Laravel Mix
Update the webpack.mix.js file so that it executes the command
```js
mix.js('resources/js/app.js', 'public/js')
    .sourceMaps()
    .version();
```
Export assets from jlab/epas
```shell
php artisan vendor:publish --tag=jlab-epas-assets --force
```
Then execute npm commands
```shell
npm install
npm run dev  
```

## Access the /plant-items route

It should now be possible to visit https://localhost/plant-items or the appropriate host:port combination for where laravel was installed in the first step.





