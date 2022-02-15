# Jlab/Epas

A Laravel package for maintaining a master database of Jefferson Lab *Plant Items* which can be integrated with the ePAS application hosted for Jlab by Prometheus. 

## Installation

Via Composer

``` bash
$ composer require jlab/epas
```

Also see detailed [installation cookbook](https://github.com/JeffersonLab/epas/blob/main/cookbook.md)

## Usage

### Create Database

Also see detailed [supplementary database schema info](https://github.com/JeffersonLab/epas/blob/main/database.md)

If the database does not exist, publish and run the migrations

``` bash
$ php artisan vendor:publish --provider="Jlab\Epas\EpasServiceProvider" --tag=migrations
$ php artisan migrate
```

If Oracle or Mysql isn't available you can a create a quick-and-dirty sqlite database before running the migrations by editing config/database.php with the following:
```php
'default' => env('DB_CONNECTION', 'sqlite'),
'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DATABASE_URL'),
            'database' => __DIR__ . '/../storage/app/db.sqlite',
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],
```
and then from top level of laravel application
```bash
$ chmod 777 storage/app/
$ touch storage/app/db.sqlite
$ chmod 666 storage/app/db.sqlite
 ```

### Database Constraints (Oracle Only)
If using an Oracle database, the check constraints below can be added for improved data integrity.  Because they are not compatible with mysql or sqlite databases, they do not get applied automatically by the migrate command.

```text
ALTER TABLE plant_items ADD CONSTRAINT chk_is_plant_item check (is_plant_item in ('0','1'));
ALTER TABLE plant_items ADD CONSTRAINT chk_is_isolation_point check (is_isolation_point in ('0','1'));
ALTER TABLE plant_items ADD CONSTRAINT chk_is_safety_system check (is_safety_system in ('0','1'));
ALTER TABLE plant_items ADD CONSTRAINT chk_is_confined_space check (is_confined_space in ('0','1'));
ALTER TABLE plant_items ADD CONSTRAINT chk_is_limited_authority check (is_limited_authority in ('0','1'));
ALTER TABLE plant_items ADD CONSTRAINT chk_is_critical_plant check (is_critical_plant in ('0','1'));
ALTER TABLE plant_items ADD CONSTRAINT chk_is_passing_valve check (is_passing_valve in ('0','1'));
ALTER TABLE plant_items ADD CONSTRAINT chk_is_temporary_item check (is_temporary_item in ('0','1'));
ALTER TABLE plant_items ADD CONSTRAINT chk_method_of_proving check (method_of_proving in ('ZEV', 'ZVV', 'VVU'));
ALTER TABLE plant_items ADD CONSTRAINT chk_circuit_voltage check (circuit_voltage in ('120V', '208V', '277V','480V','4.16kV','13kV'));
```
## Routes
The package publishes the following routes for interacting with plant items
```
+----------+---------------------------------------+-------------------------------------------------------------------+
[theo@localhost plant]$ php artisan route:list --compact 
+----------+---------------------------------------+-------------------------------------------------------------------+
| Method   | URI                                   | Action                                                            |
+----------+---------------------------------------+-------------------------------------------------------------------+                                               |
| POST     | api/plant-items                       | Jlab\Epas\Http\Controllers\PlantItemApiController@store           |
| GET|HEAD | api/plant-items                       | Jlab\Epas\Http\Controllers\PlantItemApiController@index           |
| GET|HEAD | api/plant-items/children              | Jlab\Epas\Http\Controllers\PlantItemApiController@children        |
| GET|HEAD | api/plant-items/data/isolation-points | Jlab\Epas\Http\Controllers\PlantItemApiController@isolationPoints |
| POST     | api/plant-items/upload                | Jlab\Epas\Http\Controllers\PlantItemApiController@upload          |
| GET|HEAD | api/plant-items/{plantItem}           | Jlab\Epas\Http\Controllers\PlantItemApiController@item            |
| PUT      | api/plant-items/{plantItem}           | Jlab\Epas\Http\Controllers\PlantItemApiController@update          |
| DELETE   | api/plant-items/{plantItem}           | Jlab\Epas\Http\Controllers\PlantItemApiController@delete          |                                               |
| GET|HEAD | plant-items                           | Jlab\Epas\Http\Controllers\PlantItemController@index              |
| POST     | plant-items                           | Jlab\Epas\Http\Controllers\PlantItemController@store              |
| GET|HEAD | plant-items/create                    | Jlab\Epas\Http\Controllers\PlantItemController@create             |
| GET|HEAD | plant-items/excel                     | Jlab\Epas\Http\Controllers\PlantItemController@excel              |
| GET|HEAD | plant-items/table                     | Jlab\Epas\Http\Controllers\PlantItemController@table              |
| GET|HEAD | plant-items/upload                    | Jlab\Epas\Http\Controllers\PlantItemController@uploadForm         |
| POST     | plant-items/upload                    | Jlab\Epas\Http\Controllers\PlantItemController@upload             |
| GET|HEAD | plant-items/{plantItem}               | Jlab\Epas\Http\Controllers\PlantItemController@item               |
+----------+---------------------------------------+-------------------------------------------------------------------+


```

## Assets

```bash
php artisan vendor:publish --tag=jlab-epas-assets --force
npm run dev  # or production, watch, etc.
```

## Configuration
The package provides an *epas.php* config file which must be published every time a new version is downloaded via composer. 

```bash
$ php artisan vendor:publish --provider="Jlab\Epas\EpasServiceProvider" --tag="config"
```

## Artisan Commands
The package provide an artisan console command to assist with importing plant items from a spreadsheet:

```text
$   Upload a spreadsheet containing Plant Items

Usage:
  plant-items:upload [options] [--] <file>

Arguments:
  file                             path to valid excel .xlsx file

Options:
      --plant-group[=PLANT-GROUP]  plant group name to use
      --progress-bar               show progress bar on CLI
      --replace                    delete any existing rows spreadsheet of same name
      --update                     update existing plantids
      --disable-search-syncing     disable search syncing during import
  -h, --help                       Display help for the given command. 
  -q, --quiet                      Do not output any message
  -V, --version                    Display this application version
      --ansi|--no-ansi             Force (or disable --no-ansi) ANSI output
  -n, --no-interaction             Do not ask any interactive question
      --env[=ENV]                  The environment the command should run under
  -v|vv|vvv, --verbose             Increase the verbosity of messages

```
```bash
$ php artisan help plant-items:upload-isolation-points
Description:
  Upload isolation points data from a spreadsheet.

Usage:
  plant-items:upload-isolation-points [options] [--] <file>

Arguments:
  file                  path to valid excel .xlsx file

Options:
      --progress-bar    show progress bar on CLI
      --replace         replace existing isolation points
      --sheet[=SHEET]    [default: "2: the number of the spreadsheet tab containing the isolation points data"]
  -h, --help            Display help for the given command. When no command is given display help for the list command
  -q, --quiet           Do not output any message

```


## Change log

Please see the [changelog](changelog.md) for more information on what has changed recently.

## Testing
The test suite can be executed by installing the package's dependencies via composer and then executing phpunit.

``` bash
$ composer update
$ vendor/bin/phpunit 
```

## Search Index
Data inserted into Oracle from Maximo and HCO via database links does automatically get added to the search index.  To make it searchable it is
necessary to flush the index and then rebuild it.  This can be done with the following to commands issued from the top-level project directory:

``` bash
./artisan scout:flush "\Jlab\Epas\Model\PlantItem"
./artisan scout:import "\Jlab\Epas\Model\PlantItem"
```

## Security

If you discover any security related issues, please email theo@jlab.org instead of using the issue tracker.

## License

MIT. Please see the [license file](license.md) for more information.

