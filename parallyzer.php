<?php
class parallyzer
{
    public $commands = null;
    public $tasks = null;
    public $observe = null;
    public $observe_interval = null;
    public $log = null;
    public $log_folder = null;
    public $time = null;

    public function __construct()
    {
        $this->initDefaultValues();
    }

    public function initDefaultValues()
    {
        $this->commands = [];
        $this->tasks = [];
        $this->observe = false;
        $this->observe_interval = 1;
        $this->log = false;
        $this->log_folder = 'logs';
        $this->time = microtime(true);
    }

    public function observe($interval)
    {
        $this->observe = true;
        $this->observe_interval = $interval;
    }

    public function log($folder)
    {
        $this->log = true;
        $this->log_folder = $folder;
    }

    private function createLogDir()
    {
        if (!is_dir($this->getLogDir())) {
            mkdir($this->getLogDir());
        }
    }

    private function getLogDir()
    {
        if ($this->log === true) {
            return $this->log_folder;
        } else {
            return sys_get_temp_dir() . '/parallyzer/';
        }
    }

    private function removeLogDir()
    {
        $dir = $this->getLogDir();
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != '.' && $object != '..') {
                    unlink($dir . '/' . $object);
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
                'cmd' => $filename
            ];
        }
    }

    public function run()
    {
        $this->removeLogDir();
        $this->createLogDir();
        $this->execCommands();
        if ($this->observe === true) {
            $this->observeTasks();
        } else {
            echo 'spawned ' . count($this->commands) . ' processes. checkout your log folder!';
        }
    }

    private function execCommands()
    {
        foreach ($this->commands as $commands__key => $commands__value) {
            $log_file = $this->getLogDir() . '/log.' . $commands__key;
            $pid_file = $this->getLogDir() . '/pid.' . $commands__key;
            $pid = file_put_contents($pid_file, '');
            exec($commands__value['cmd'] . ' > ' . $log_file . ' 2>&1 & echo $! >> ' . $pid_file);
            $pid = (int) trim(file_get_contents($pid_file));
            @unlink($pid_file);
            $this->tasks[] = [
                'cmd' => $commands__value['cmd'],
                'log' => $log_file,
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
                $commands__value['cmd'] . ' > ' . $log_file . ' 2>&1 & ',
                $descr,
                $pipes
            );
            $status = proc_get_status($process);
            $this->tasks[] = [
                'cmd' => $commands__value['cmd'],
                'log' => $log_file,
                'pid' => $status['pid'] + 1
            ];
            */
        }
    }

    private function observeTasks()
    {
        while (true) {
            if (empty($this->tasks)) {
                $time = number_format(microtime(true) - $this->time, 5);
                echo PHP_EOL;
                echo PHP_EOL;
                echo 'finished all tasks in ' . $time . 's.';
                if ($this->log === false) {
                    $this->removeLogDir();
                }
                break;
            }
            //print_r($this->tasks);
            sleep($this->observe_interval);
            foreach ($this->tasks as $tasks__key => $tasks__value) {
                if (file_exists($tasks__value['log'])) {
                    $output = trim(file_get_contents($tasks__value['log']));
                    //var_dump($output);
                    if ($output != '') {
                        echo PHP_EOL;
                        echo 'pid ' . $tasks__value['pid'] . ':' . PHP_EOL;
                        echo $output;
                        echo PHP_EOL;
                        echo '#####################################';
                        file_put_contents($tasks__value['log'], '');
                        // collect all outputs
                        file_put_contents(
                            $tasks__value['log'] . '.tmp',
                            $output . PHP_EOL,
                            FILE_APPEND
                        );
                    }
                }
                if (!$this->isRunning($tasks__value['pid'])) {
                    //echo $tasks__value['pid'] . ' not running anymore!';
                    unset($this->tasks[$tasks__key]);
                    @unlink($tasks__value['log']);
                    @rename($tasks__value['log'] . '.tmp', $tasks__value['log']);
                }
            }
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

$p = new parallyzer();
$p->add('php test.php', 100);
$p->observe(20);
//$p->log('logs');
$p->run();
