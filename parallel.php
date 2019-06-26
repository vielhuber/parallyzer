<?php
class Parallel
{
    public $commands = [];
    public $tasks = [];

    public function __construct()
    {
        $this->cleanUp();
        $this->start();
    }

    private function start()
    {
        $this->createDir($this->getTmpDir());
    }

    private function cleanUp()
    {
        $this->removeDir($this->getTmpDir());
    }

    private function getTmpDir()
    {
        return realpath(dirname(__FILE__)) . '/tmp';
    }

    private function createDir($dir)
    {
        @mkdir($dir);
    }

    private function removeDir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != '.' && $object != '..') {
                    if (filetype($dir . '/' . $object) == 'dir') {
                        rrmdir($dir . '/' . $object);
                    } else {
                        unlink($dir . '/' . $object);
                    }
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }

    public function add($filename, $instances = 1)
    {
        for ($i = 0; $i < $instances; $i++) {
            $this->commands[] = [
                'cmd' => $filename,
                'log' => $this->getTmpDir() . '/output' . $i
            ];
        }
    }

    public function run($observe_seconds = 1)
    {
        $this->execCommands();
        $this->observeTasks($observe_seconds);
    }

    private function execCommands()
    {
        foreach ($this->commands as $commands__value) {
            $pid_file = $commands__value['log'] . '.pid';
            $pid = file_put_contents($pid_file, '');
            exec(
                $commands__value['cmd'] .
                    ' > ' .
                    $commands__value['log'] .
                    ' 2>&1 & echo $! >> ' .
                    $commands__value['log'] .
                    '.pid'
            );
            $pid = (int) trim(file_get_contents($commands__value['log'] . '.pid'));
            @unlink($pid_file);
            $this->tasks[] = [
                'cmd' => $commands__value['cmd'],
                'log' => $commands__value['log'],
                'pid' => $pid
            ];
            /*
            $pipes = [];
            $descr = [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w']
            ];
            $process = proc_open(
                $commands__value['cmd'] . ' > ' . $commands__value['log'] . ' 2>&1 & ',
                $descr,
                $pipes
            );
            $status = proc_get_status($process);
            $this->tasks[] = [
                'cmd' => $commands__value['cmd'],
                'log' => $commands__value['log'],
                'pid' => $status['pid'] + 1
            ];
            */
        }
    }

    private function observeTasks($observe_seconds = 1)
    {
        while (true) {
            if (empty($this->tasks)) {
                echo 'DONE';
                //$this->cleanUp();
                break;
            }
            //print_r($this->tasks);
            foreach ($this->tasks as $tasks__key => $tasks__value) {
                if (file_exists($tasks__value['log'])) {
                    $output = trim(file_get_contents($tasks__value['log']));
                    //var_dump($output);
                    if ($output != '') {
                        echo 'pid ' . $tasks__value['pid'] . ':' . PHP_EOL;
                        echo $output;
                        file_put_contents($tasks__value['log'], '');
                    }
                }
                if (!$this->isRunning($tasks__value['pid'])) {
                    echo $tasks__value['pid'] . ' not running anymore!';
                    unset($this->tasks[$tasks__key]);
                }
            }
            sleep($observe_seconds);
        }
    }

    private function isRunning($pid)
    {
        if ($this->getOs() === 'windows') {
            // TODO
        } else {
            return shell_exec('ps aux | grep ' . $pid . ' | grep -v grep | wc -l') > 0;
        }
    }

    private function getOs()
    {
        if (stristr(PHP_OS, 'DAR')) {
            return 'mac';
        }
        if (stristr(PHP_OS, 'WIN') || stristr(PHP_OS, 'CYGWIN')) {
            return 'windows';
        }
        if (stristr(PHP_OS, 'LINUX')) {
            return 'linux';
        }
        return 'unknown';
    }
}

$p = new Parallel();
$p->add('php test.php', 100);
$p->run(2.5);
