<?php

namespace Extend;
/**
 * PDO Mysql类
 */
define('DB_NAME', 'lcdswap');
define('DB_USER', 'root');
define('DB_PASSWORD', 'root');
define('DB_HOST', '127.0.0.1');
define('REDIS_PORT', '6379');
class DB {

    public $getLastSql, $getLastInsert, $prefix, $errorInfo = array(), $param = array();
    private $tableName, $field = '*', $where = '', $join = array(), $order = '',
            $group = '', $limit = '';

    public function __construct() {
        $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME;
        try {
            if(empty($this->dbh)){
                $this->dbh = new PDO($dsn, DB_USER, DB_PASSWORD);
                $this->dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                $this->dbh->exec('SET NAMES UTF8');
            }
        } catch (\PDOException $e) {
            die("Error!: {$e->getMessage()} <br/>");
        }
    }

    /**
     * 设置表名
     * @param string $name 表名称
     * @return PDO 返回变量
     */
    public function tableName($name, $databases = '') {
        // $this->prefix = 'wp_';
        if (empty($databases)) {
            $databases = "`".DB_NAME."`.";
        } else {
            $databases = "`{$databases}`.";
        }
        $this->tableName = $databases . $this->prefix . $name;
        return $this;
    }

    /**
     * 设置显示字段
     * @param string $name 字段名
     * @return PDO 返回变量
     */
    public function field($name) {
        if (empty($name)) {
            $this->field = '*';
        } else {
            $this->field = $name;
        }
        return $this;
    }

    /**
     * 设置条件
     * @param string $condition 条件
     * @return PDO 返回变量
     */
    public function where($condition) {
        if (empty($condition)) {
            $this->where = '';
        } else {
            $this->where = ' WHERE ' . $condition;
        }
        return $this;
    }

    /**
     * 设置左联表
     * @param string $condition 条件
     * @return \Core\Db\Mysql 返回变量
     */
    public function join($condition) {
        if (empty($condition)) {
            $this->join = array();
        } else {
            $this->join[] = ' LEFT JOIN ' . $condition;
        }

        return $this;
    }

    /**
     * 设置排序
     * @param string $condition 条件
     * @return PDO 返回变量
     */
    public function order($condition) {
        if (empty($condition)) {
            $this->order = '';
        } else {
            $this->order = ' ORDER BY ' . $condition;
        }
        return $this;
    }

    /**
     * 设置组合
     * @param str $condition 条件
     * @return \Core\Db\Mysql 返回变量
     */
    public function group($condition) {
        if (empty($condition)) {
            $this->group = '';
        } else {
            $this->group = ' GROUP BY ' . $condition;
        }
        return $this;
    }

    /**
     * 设置限制
     * @param string $condition 条件
     * @return PDO 返回变量
     */
    public function limit($condition) {
        if (empty($condition)) {
            $this->limit = '';
        } else {
            $this->limit = ' LIMIT ' . $condition;
        }
        return $this;
    }

    /**
     * 单条数据查找
     * @param array $param 查询参数(一维数组)
     * @param str $fieldType 字段类型
     * @return array 返回一维数组结果
     */
    public function find($param = '', $fieldType = '') {
        $this->dealParam($param, $fieldType);

        $limit = ' LIMIT 1 ';
        $this->join = empty($this->join) ? array('') : $this->join;
        $this->getLastSql = 'SELECT ' . $this->field . ' FROM ' . $this->tableName . implode('', $this->join) . $this->where . $this->group . $this->order . $limit;
        $sth = $this->PDOBindArray();
        $result = $sth->fetch();
        return $result;
    }

    /**
     * 数据查找
     * @param array $param 查询参数(一维数组)
     * @param str $fieldType 字段类型
     * @return array 返回二维数组结果
     */
    public function select($param = '', $fieldType = '') {
        $this->dealParam($param, $fieldType);
        $this->join = empty($this->join) ? array('') : $this->join;
        $this->getLastSql = 'SELECT ' . $this->field . ' FROM ' . $this->tableName . implode('', $this->join) . $this->where . $this->group . $this->order . $this->limit;
        $sth = $this->PDOBindArray();
        $result = $sth->fetchALL();
        return $result;
    }

    /**
     * 单例数据插入
     * @param array $param 插入参数(一维数组)
     * @param string $fieldType 字段类型
     * @return string 返回最后插入的ID
     */
    public function insert($param = '', $fieldType = '') {
        $this->dealParam($param, $fieldType);
        foreach ($this->param as $key => $value) {
            $field[] = "`{$key}`";
            $namedPlaceholders[] = ':' . $key;
        }
        $this->getLastSql = 'INSERT INTO ' . $this->tableName . ' (' . implode(',', $field) . ' ) VALUES (' . implode(',', $namedPlaceholders) . ' )';
        $sth = $this->PDOBindArray();
        if ($this->dbh->lastInsertId() === false) {
            return false;
        } else {
            return $this->dbh->lastInsertId();
        }
    }

    /**
     * 数据保存
     * @param array $param 插入参数(一维数组)
     * @param string $fieldType 字段类型
     * @return string 返回影响行数
     */
    public function update($param = '', $fieldType = '') {
        $noset = $param['noset'];
        unset($param['noset']);
        foreach ($param as $key => $value) {
            $content[] = "`{$key}` = :{$key}";
        }

        if (!empty($noset)) {
            $param = array_merge($param, $noset);
        }

        $this->dealParam($param, $fieldType);

        $this->getLastSql = 'UPDATE ' . $this->tableName . ' SET ' . implode(',', $content) . $this->where;

        $sth = $this->PDOBindArray();
        $result = $sth->rowCount();
        if ($result >= 0) {
            return $result;
        } else {
            return false;
        }
    }

    /**
     * 删除单条数据
     * @param array $param 插入参数(一维数组)
     * @param str $fieldType 字段类型
     * @return str 返回影响行数
     */
    public function delete($param = '', $fieldType = '') {
        $this->dealParam($param, $fieldType);
        $this->getLastSql = 'DELETE FROM ' . $this->tableName . $this->where;
        $sth = $this->PDOBindArray();
        $result = $sth->rowCount();

        if ($result >= 0) {
            return $result;
        } else {
            return false;
        }
    }

    /**
     * 执行查询SQL语句
     * @param str $sql SQL语句
     * @param array $param 插入参数(二维数组)
     * @param str $fieldType 字段类型
     * @return str 返回一维数组
     */
    public function fetch($sql, $param = '', $fieldType = '') {
        $this->dealParam($param, $fieldType);
        $this->getLastSql = $sql;
        $sth = $this->PDOBindArray();
        $result = $sth->fetch();
        if (!empty($result)) {
            return $result;
        } else {
            return false;
        }
    }

    /**
     * 暴露一个仅执行了却没有取出数据的对象方法。
     * 本方法用法类似mysql中的mysql_fetch_array();
     * 具体参考如下的链接
     * @link URL http://php.net/manual/en/function.mysql-fetch-array.php
     * @param type $sql
     * @param type $param
     * @param type $fieldType
     * @return type
     */
    public function fetchArray($sql, $param = '', $fieldType = '') {
        $this->dealParam($param, $fieldType);
        $this->getLastSql = $sql;
        $sth = $this->PDOBindArray();
        return $sth;
    }

    /**
     * 执行查询SQL语句
     * @param string $sql SQL语句
     * @param array $param 插入参数(二维数组)
     * @param string $fieldType 字段类型
     * @return array | boolean 返回二维数组
     */
    public function getAll($sql, $param = '', $fieldType = '') {
        $this->dealParam($param, $fieldType);
        $this->getLastSql = $sql;
        $sth = $this->PDOBindArray();
        $result = $sth->fetchALL();
        if (!empty($result)) {
            return $result;
        } else {
            return false;
        }
    }

    /**
     * 执行插入/更新/删除SQL语句
     * @param string $sql SQL语句
     * @param array $param 插入参数(二维数组)
     * @param string $fieldType 字段类型
     * @return string 返回影响行数
     */
    public function query($sql, $param = '', $fieldType = '') {
        $this->dealParam($param, $fieldType);
        $this->getLastSql = $sql;
        $sth = $this->PDOBindArray();
        $statistics = $sth->rowCount();
        $lastInsertId = $this->dbh->lastInsertId();
        if (!empty($lastInsertId)) {
            return $lastInsertId;
        } elseif ($statistics >= 0) {
            return $statistics;
        } else {
            return false;
        }
    }

    /**
     * 用于执行数据库操作
     * @param array $param 插入参数(二维数组)
     * @param str $fieldType 字段类型
     * @return str 返回影响行数
     */
    public function alter($sql, $param = '', $fieldType = '') {
        $this->dealParam($param, $fieldType);
        $this->getLastSql = $sql;
        $sth = $this->PDOBindArray();
        if ($sth === false) {
            return false;
        } else {
            return $sth;
        }
    }

    /**
     * 处理一维参数
     * @param type $param 参数
     * @param type $fieldType 字段类型
     * @return boolean 返回一个数组变量
     */
    private function dealParam($param = '', $fieldType = '') {
        //分析参数绑定
        if (is_array($param)) {
            $array = $param;
        } elseif (empty($param)) {
            return true;
        } else {
            exit('参数绑定只能为数组');
        }
        //分析字段设置
        if (is_string($fieldType)) {
            $fieldTypeArray = explode(',', $fieldType);
        } else {
            exit('字段类型只能为字符串');
        }

        if (empty($fieldType)) {
            foreach ($array as $key => $value) {
                $this->param[$key]['value'] = $value;
                $this->param[$key]['fieldType'] = 2;
            }
            return $this->param;
        }

        $arrayLength = count($array);
        if ($arrayLength != count($fieldTypeArray)) {
            exit('参数绑定与字段设置长度不一致');
        }

        $i = 0;
        foreach ($array as $key => $value) {
            $this->param[$key]['value'] = $value;
            $this->param[$key]['fieldType'] = $fieldTypeArray[$i];
            $i++;
        }
        return $this->param;
    }

    /**
     * 处理二维参数
     * @param type $param 参数
     * @param type $fieldType 字段类型
     * @return boolean 返回一个数组变量
     */
    private function dealMoreParam($param = '', $fieldType = '') {
        //分析参数绑定
        if (is_array($param)) {
            $array = $param;
        } elseif (empty($param)) {
            return true;
        } else {
            exit('参数绑定只能为数组');
        }
        //分析字段设置
        if (is_string($fieldType)) {
            $fieldTypeArray = explode(',', $fieldType);
        } else {
            exit('字段类型只能为字符串');
        }

        if (empty($fieldType)) {
            foreach ($array as $key => $value) {
                foreach ($value as $key_2 => $value_2) {
                    $this->param[$key][$key_2]['value'] = $value_2;
                    $this->param[$key][$key_2]['fieldType'] = 2;
                }
            }
            return $this->param;
        }

        $i = 0;
        foreach ($array as $key => $value) {
            foreach ($value as $key_2 => $value_2) {
                $this->param[$key][$key_2]['value'] = $value_2;
                $this->param[$key][$key_2]['fieldType'] = $fieldTypeArray[$i];
                $i++;
            }
        }
        //由于转换三维数组，顾使用$i 来做判断
        if ($i != count($fieldTypeArray)) {
            exit('参数绑定与字段设置长度不一致');
        }
        return $this->param;
    }

    /**
     * 对SQL进行预处理
     * @return type 返回PDO预处理的对象
     */
    public function PDOBindArray() {
        try {
            $sth = $this->dbh->prepare($this->getLastSql);
            if (!empty($this->param)) {
                foreach ($this->param as $key => $value) {
                    $placeholder[] = ":{$key}";
                    $paramValue[] = "'{$value['value']}'";
                    $sth->bindValue(':' . $key, $value['value'], $value['fieldType']);
                }
                $this->getLastSql = str_replace($placeholder, $paramValue, $this->getLastSql);
            }
            $sth->execute();
            $this->emptyParam();
            return $sth;
        } catch (\PDOException $e) {
            if (!empty($this->param)) {
                foreach ($this->param as $key => $value) {
                    $placeholder[] = ":{$key}";
                    $paramValue[] = "'{$value['value']}'";
                }
                $this->getLastSql = str_replace($placeholder, $paramValue, $this->getLastSql);
            }
            die("<b>Last SQL</b>:{$this->getLastSql}<br/> <b>Error!</b>: {$e->getMessage()} <br/>");
        }
    }

    /**
     * 清空绑定的参数
     */
    public function emptyParam() {
        $this->field = '*';
        $this->where = '';
        $this->join = array();
        $this->order = '';
        $this->group = '';
        $this->limit = '';
        $this->param = array();
    }

    /**
     * 启动事务
     */
    public function transaction() {
        return $this->dbh->beginTransaction();
    }

    /**
     * 事务回滚
     */
    public function rollBack() {
        return $this->dbh->rollBack();
    }

    /**
     * 提交事务
     */
    public function commit() {
        return $this->dbh->commit();
    }

}
