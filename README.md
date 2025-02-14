![CleanShot 2025-02-08 at 17 56 49 3@2x](https://github.com/user-attachments/assets/6d0cac81-7e3f-4443-9ad3-e6b04e16b8e7)

(Part of the video [Import One Million Rows To The Database (PHP/Laravel)](https://youtu.be/CAi4WEKOT4A))

# Artisan Benchmark

## Installation

```shell
composer require christophrumpel/artisan-benchmark
```

## Usage

This package lets you `benchmark` your artisan commands:

```shell
php artisan benchmark your:command
```

Simply replace `⁠your:command` with your command signature. After execution, you'll see detailed benchmark results.


![CleanShot 2025-02-08 at 17 56 49@2x](https://github.com/user-attachments/assets/d5a6e86d-1cc4-4786-b246-3c8939aec053)


If you run the command without a signature, it will display a list of available commands to choose from:


```shell
php artisan benchmark
```

![CleanShot 2025-02-14 at 13 21 14@2x](https://github.com/user-attachments/assets/a490b8ec-7859-4966-9fbf-f1e3c66d55d2)


## Table Count Monitoring

You can monitor changes in a specific database table's record count by using the `⁠--tableToWatch` option:
```php
php artisan benchmark your:command --tableToWatch=users
```

Be aware that it only shows the count difference from before running your command.

![CleanShot 2025-02-14 at 13 34 31@2x](https://github.com/user-attachments/assets/ce0ec54a-b99b-49d6-99cd-7b4f062097cc)



## Technical Details

### Query Count
The package uses Laravel's query logging functionality to track the number of database queries:

```php
DB::enableQueryLog();
DB::flushQueryLog();
```

Please note that this only tracks queries executed through Eloquent or the Query Builder. Direct database queries will not be counted. Contributions for improving this functionality are welcome.
