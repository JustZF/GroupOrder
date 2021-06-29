<?php
/*
具体我用到3张表：
    拼团商品表（关联商品，是否单独购买，开团人数，开团期限等）shop_group_goods / shop_goods
    开团信息表（关联拼团商品，关联购买订单，团开始时间，团结束时间，总人数，当前参团人数，开团人，参与人等）shop_group_order_detail
    开团会员的购买订单表（也可在普通商品订单表基础上增加字段区分）shop_order

需要注意的基本就是：
    超时未成团的自动退款（可能需要支付宝、微信等退款接口）暂时设定30分钟
    未成团前不能发起退款，只有成团后才能发起退款
    未支付成功不算参团，需要注意超卖的情况

以下代码仅供参考 暂时在Controller层实现全部操作 不适用生产环境
*/

declare(strict_types=1);
namespace Controllers;

use Service\Say;

class OrderGroupBy extends Base
{
    protected $orderFirst = false;
    
    //开团
    public function OrderGroupStart()
    {
        $groupGoodId = $_POST['pid'];
        //是否首次开团
    	$this->checkOrderGroupFirst();
        //检验当前库存
        $this->checkGoodStock($groupGoodId);
        //生成拼团
        $this->addGroupOrder($groupGoodId, 1);

        echo 1;

    }
    
    //记录订单信息
    protected function addGroupOrder($pid, $type) 
    {
        //获取拼团商品信息
        $GroupGoodsInfo = $this->getGroupGoods($pid);

        switch ($type) {
            //开团
            case 1:
                # 开启事务等操作 保持数据库一致性
                
                break;
            //拼团
            default:
                # code...
                break;
        }
    }

    //获取拼团商品信息
    private function getGroupGoods($pid) 
    {
        $GroupGoodsInfo = $this->pdo->tableName('shop_group_goods')->where('id = :goods_id')->find( array('goods_id' => $pid) );
        //获取开团价 参团价
        return [
            'startPay'     => $GroupGoodsInfo['start_pay'],
            'goPay'        => $GroupGoodsInfo['go_pay'],
            'memberCount'  => $GroupGoodsInfo['member_count'],
            //...关联商品信息 同时返回
        ]
    }

    //是否首次开团
    protected function checkOrderGroupFirst()
    {
        $userIp = (getIP() == '::1') ? '127.0.0.1' : getIP();

        //用户表字段判断或单表记录首次开团
        $where = "ip = '" . $userIp . "'";
        $OrderGroupFirst = $this->pdo->tableName('shop_ip_first')->where($where)->find();
        if($OrderGroupFirst == false) {
            $this->orderFirst = true;
        }
    }

    //检验当前库存
    protected function checkGoodStock($pid)
    {
        $goodsInfo = $this->pdo->tableName('shop_goods')->where('id = :goods_id')->find( array('goods_id' => $pid) );
        //暂时只检验库存 不存在等请自行添加
        if($goodsInfo['stock_total'] <= 0) {
            echo $this->output(404, 'no stock');
            exit;
        } 
    }
}