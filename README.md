# Artisan Benchmark

This package lets you `benchmark` your artisan commands:

```shell
php artisan benchmark your:command
```

Just replaces `your:command` with the signature of your command.

You can also provide no signature, then the benchmark command will show you a list of commands to pick from:

```shell
php artisan benchmark
```

## Good To Know

### Query Count
This package uses `query logging` to count the number of queries made, and resets it with every run:

```php
DB::enableQueryLog();
DB::flushQueryLog();
```

This also mean that only queries made by Eloquent or the Query Builder will be tracked. (I am open for suggestions to improve this.)
