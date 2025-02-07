# WIP - Artisan Benchmark

This package lets you `benchmark` your artisan commands:

```shell
php artisan benchmark your:command
```

Just replaces `your:command` with the signature of your command.

You can also provide no signature, then benchmark command will show you a list of commands to pick from.

```shell
php artisan benchmark
```
