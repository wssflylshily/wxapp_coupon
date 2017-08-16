<?php
/**
 * Created by PhpStorm.
 * User: sunfan
 * Date: 2017/7/24
 * Time: 10:26
 */

namespace Admin\Controller;


use Common\Controller\AdminbaseController;

class CouponController extends AdminbaseController
{
    public function index()
    {
        $db = M('Coupon');
        $where=[];
        if(!empty($_POST['cname'])){
            $where['cname'] = array("like","%".$_POST['cname']."%");
        }

        $count=$db->join('LEFT JOIN yhq_category ON yhq_category.id=yhq_coupon.category_id')
            ->where($where)->count();
        $page = $this->page($count, 20);
        $rs = $db->join('LEFT JOIN yhq_category ON yhq_category.id=yhq_coupon.category_id')
            ->where($where)->field('*, yhq_coupon.id as cid')->limit($page->firstRow . ',' . $page->listRows)->select();
        $show = $page->show('Admin');
        $this->assign("page", $show);

        $this->assign('tree', $rs);
        $this ->display();
    }

    //查看核销员
    public function salesperson()
    {
        $db = M('Salesperson');
        $where['yhq_salesperson.coupon_id'] = $_GET['id'];

        $title = M('Coupon')->where(['id'=>$_GET['id']])->getField('title');
        $this->assign('title', $title);

        $count=$db
            ->join('LEFT JOIN yhq_member ON yhq_member.id=yhq_salesperson.user_id')
            ->where($where)->count();
        $page = $this->page($count, 20);
        $rs = $db ->join('LEFT JOIN yhq_member ON yhq_member.id=yhq_salesperson.user_id')
            ->where($where)->limit($page->firstRow . ',' . $page->listRows)
            ->field('yhq_salesperson.id, yhq_member.nickname, yhq_salesperson.sp_time')
            ->select();
        $show = $page->show('Admin');
        $this->assign("page", $show);
        $this->assign('rs', $rs);
        $this->display();
    }

    //删除核销员
    public function delsale()
    {
        $db = M('Salesperson');
        $rs = $db->where(['id'=>$_GET['id']])->delete();
        $this -> success("删除成功",U('Admin/Coupon/index'),1);
    }

    //删除优惠券
    public function del()
    {
        $db = M('Coupon');
        $rs = $db->where(['id'=>$_GET['id']])->delete();
        $this -> success("删除成功",U('Admin/Coupon/index'),1);
    }

}