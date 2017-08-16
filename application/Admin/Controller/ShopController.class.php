<?php
/**
 * Created by PhpStorm.
 * User: sunfan
 * Date: 2017/7/28
 * Time: 19:15
 */

namespace Admin\Controller;


use Common\Controller\AdminbaseController;

class ShopController extends AdminbaseController
{
    public function index()
    {
        $where=[];
        if ($_POST['keyword'])
        {
            $where['shop_name'] = array('like', "%".$_POST['keyword']."%");
        }

        $count=M('MemberAddress')->where($where)->count();
        $page = $this->page($count, 20);
        $rs = M('MemberAddress')->where($where)->limit($page->firstRow . ',' . $page->listRows)->select();
        $show = $page->show('Admin');
        $this->assign("page", $show);

        $this->assign('rs', $rs);
        $this->display();
    }

}