# ⛓️ parallyzer ⛓️

parallyzer is a poor mans php tool for running multiple, non-blocking (php-)scripts in separate cpu threads.

## features

-   runs on linux and mac
-   no extensions like [pthreads](https://www.php.net/manual/de/book.pthreads.php) required
-   runs php scripts or any other commands
-   watch the output in a definable interval
-   supports file based output logging

## installation

```bash
composer require vielhuber/parallyzer
```

```php
require __DIR__ . '/vendor/autoload.php';
use vielhuber\parallyzer\parallyzer;
$p = new parallyzer();
```

## usage

```php
$p->add('php test.php', 100); // adds the command "php test.php" in 100 parallel threads to the worker
$p->add('ls -s', 25); // adds the command "ls -s" in 25 parallel threads to the worker
$p->observe(1); // enables live observing with an update interval of 1 second
$p->log('logs'); // enables output logging in the folder "logs"
$p->run(); // runs the machinery
```
