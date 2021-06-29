<?php

declare(strict_types=1);

namespace Controllers;
use Extend\DB;

abstract class Base
{
    /**
     * 应用实例
     * @var PDO
     */
    protected $pdo;

    /**
     * 构造方法
     * @access public
     */
    public function __construct()
    {
        $this->pdo = new DB;
        // 控制器初始化
        $this->initialize();
    }

    // 初始化
    protected function initialize()
    {
        //表前序
        $this->pdo->prefix = '';
    }

    //返回格式
    public function output($errNo = 200, $errMsg = 'ok', $data = array())
    {
        $res['errno'] = $errNo;
        $res['errMsg'] = $errMsg;
        $res['data'] = $data;
        return json_encode($res);
    }
}
