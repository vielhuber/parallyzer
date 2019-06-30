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
composer require vielhuber/dbhelper
```

```php
require __DIR__ . '/vendor/autoload.php';
use vielhuber\parallyzer\parallyzer;
$p = new parallyzer();
```

## usage

```php
$p->add('php test.php', 100);
$p->add('ls -s', 25);
$p->observe(1);
$p->log('logs');
$p->run();
```
