<?php
/**
 * Created by PhpStorm.
 * User: sunfan
 * Date: 2017/7/24
 * Time: 10:26
 */

namespace Admin\Controller;


use Common\Controller\AdminbaseController;

class CouponLogController extends AdminbaseController
{
    public function index()
    {
        $db = M('CouponLog');
        $where=[];
        if(!empty($_POST['cname'])){
            $where['yhq_coupon.title'] = array("like","%".$_POST['cname']."%");
        }
        if(!empty($_POST['start']) && !empty($_POST['end'])){
            $where['yhq_coupon_log.cl_time'] = ["between",[strtotime($_POST['start']), strtotime($_POST['end'])]];
        }

        $count=$db
            ->where($where)
            ->join('LEFT JOIN yhq_coupon ON yhq_coupon.id=yhq_coupon_log.coupon_id')
            ->join('LEFT JOIN yhq_member ON yhq_member.id=yhq_coupon_log.user_id')
            ->count();
        $page = $this->page($count, 20);
        $rs = $db
            ->join('LEFT JOIN yhq_coupon ON yhq_coupon.id=yhq_coupon_log.coupon_id')
            ->join('LEFT JOIN yhq_member ON yhq_member.id=yhq_coupon_log.user_id')
            ->where($where)
            ->order('yhq_coupon_log.cl_time DESC')
            ->limit($page->firstRow . ',' . $page->listRows)->select();
        $show = $page->show('Admin');
        $this->assign("page", $show);

        $this->assign('tree', $rs);
        $this ->display();
    }

}