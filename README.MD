parallyzer is a poor mans php tool for running multiple, non-blocking (php-)scripts in separate cpu threads.

-   runs on linux, mac and windows
-   no extensions like pthreads required
-   run any php script or any other command
-   watch the output in a definable interval
-   suppors file based output logging

```
$p = new parallyzer();
$p->add('php test.php', 100);
$p->add('ls -s', 25);
$p->run(2.5);
```
