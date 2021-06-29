<?php

declare(strict_types=1);

require 'autoload.php';
require 'functions.php';

//简单路由类
class route
{
    /*
     * 实例化
     */
    public function index()
    {
        if (empty($_GET['m'])) {
            exit('Empty  Controller');
        }

        if (empty($_GET['a'])) {
            exit('Empty Action');
        }

        define('MODULE', $_GET['m']);
        define('ACTION', $_GET['a']);

        $class = 'Controllers\\' . $_GET['m'];

        $obj = new $class();

        $action = strtolower($_GET['a']);
        $obj->$action();
    }
}

$obj = new route();
$obj->index();
