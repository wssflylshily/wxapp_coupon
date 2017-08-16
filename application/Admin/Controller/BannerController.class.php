<?php
/**
 * Created by PhpStorm.
 * User: sunfan
 * Date: 2017/7/24
 * Time: 17:29
 */

namespace Admin\Controller;


use Common\Controller\AdminbaseController;

class BannerController extends AdminbaseController
{

    public function index()
    {
        $db = M('Banner');

        $count=$db->count();
        $page = $this->page($count, 20);
        $rs = $db->limit($page->firstRow . ',' . $page->listRows)->select();
        $show = $page->show('Admin');
        $this->assign("page", $show);

        $this->assign('rs', $rs);
        $this->display();
    }

    public function del()
    {
        $db = M('Banner');
        $url = $db->where(['id'=>$_GET['id']])->getField('img');
        unlink("data/upload/banner/".$url);
        $rs = $db->where(['id'=>$_GET['id']])->delete();
        $this -> success("删除成功",U('Admin/Banner/index'),1);
    }

    /*添加分类方法*/
    public function add(){
        if (!empty($_POST))
        {
            $db = M('Banner');

            if(empty($_FILES)){
                $this->error("图片不能为空");
            }
            $upload = new \Think\Upload();// 实例化上传类
            $upload->maxSize   =     3145728 ;// 设置附件上传大小
            $upload->exts      =     ['jpg', 'gif', 'png', 'jpeg'];// 设置附件上传类型
            $upload->rootPath  =      'data/upload/banner/'; // 设置附件上传根目录
            // 上传单个文件
            $info   =   $upload->uploadOne($_FILES['img']);
            if(!$info) {// 上传错误提示错误信息
                $this->error($upload->getError());
            }else{// 上传成功 获取上传文件信息
                $data['img'] = "/data/Upload/banner/".$info['savepath'].$info['savename'];
            }

            $data['url'] = $_POST['url'];
            $rees = $db -> add($data);
            $this -> success("添加成功",U('Admin/Banner/index'),1);
        }else{
            $this->display();
        }

    }
}