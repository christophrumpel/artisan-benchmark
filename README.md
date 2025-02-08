
![CleanShot 2025-02-08 at 17 56 49 3@2x](https://github.com/user-attachments/assets/6d0cac81-7e3f-4443-9ad3-e6b04e16b8e7)

# Artisan Benchmark

This package lets you `benchmark` your artisan commands:

```shell
php artisan benchmark your:command
```

Just replaces `your:command` with the signature of your command. Your command will be run; afterward, you will see the benchmark output.

![CleanShot 2025-02-08 at 17 56 49@2x](https://github.com/user-attachments/assets/d5a6e86d-1cc4-4786-b246-3c8939aec053)


You can also provide no signature, then benchmark command will show you a list of commands to pick from.

```shell
php artisan benchmark
```
