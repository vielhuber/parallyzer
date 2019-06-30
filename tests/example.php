<?php
require_once '../src/parallyzer.php';
$p = new parallyzer();
$p->add('php test.php', 3);
$p->observe(1);
//$p->log('logs');
$p->run();
