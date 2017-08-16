<?php
/**
 * 后台首页
 */
namespace Admin\Controller;

use Common\Controller\AdminbaseController;

class CategoryController extends AdminbaseController {


    public function index(){
        $where['pid'] = 0;

        if(!empty($_POST['cname'])){
            $where['cname'] = array("like","%".$_POST['cname']."%");
        }

        $count=M('category')->where($where)->count();
        $page = $this->page($count, 20);
        $rs = M('category')->where($where)->limit($page->firstRow . ',' . $page->listRows)->select();
        $show = $page->show('Admin');
        $this->assign("page", $show);

        $this->assign('tree', $rs);
        $this ->display();
    }

  public function fadd(){
    $this->display();
  }

  public function zfadd(){
    $this->display();
  }

    /*修改分类方法*/
    public function modd(){
        $cate = M('category');
        $id = $_GET['id'];
        $list = $cate->where("id = {$id}")->find();
        $this->assign("list",$list);
        $this->display();
    }

    /*添加分类方法*/
    public function finsert(){
        $cate = M('category');
        $cname = $_POST['cname'];
        $status = $_POST['status'];
        $name = $cate->where("cname = '{$cname}'")->find();
        if($name){
            $this -> error("此分类已存在");
        }else{
            if(empty($_FILES)){
                $this->error("图标不能为空");
            }
            $upload = new \Think\Upload();// 实例化上传类
            $upload->maxSize   =     3145728 ;// 设置附件上传大小
            $upload->exts      =     ['jpg', 'gif', 'png', 'jpeg'];// 设置附件上传类型
            $upload->rootPath  =      'data/upload/'; // 设置附件上传根目录
            // 上传单个文件
            $info   =   $upload->uploadOne($_FILES['icon_url']);
            if(!$info) {// 上传错误提示错误信息
                $this->error($upload->getError());
            }else{// 上传成功 获取上传文件信息
                $_POST['icon_url'] = "/data/Upload/".$info['savepath'].$info['savename'];
            }

            $_POST['pid'] = '0';
            $_POST['status'] = $_POST['status'];
            if($cate -> create($_POST)){
                $rees = $cate -> add();
                $this -> success("添加成功",U('Admin/Category/index'),1);
            }else{
               $this -> error("添加失败");
            }
        }
    }

    /*添加子分类方法*/
    public function zfinsert(){
        $cate = M('category');
        $cname = $_POST['cname'];
        $name = $cate->where("cname = '{$cname}'")->find();
        if($name){
            $this -> error("此分类已存在");
        }else{
            $_POST['pid'] = $_POST['pid'];
            $_POST['status'] = $_POST['status'];
            if($cate -> create($_POST)){
                $rees = $cate -> add();
                $this -> success("添加成功",U('Admin/Category/index'),1);
            }else{
               $this -> error("添加失败");
            }
        }
    }
   
     /*修改分类方法*/
    public function mod(){
        $cate = M('category');
        $cname = $_POST['cname'];
        $id = $_POST['id'];
        $status = $_POST['status'];
        $name = $cate->where("cname ='{$cname}' AND id!='{$id}'")->find();
        if(!empty($name)){
            $this -> error("此名称已存在");
        }else{
            if(!empty($_FILES['icon_url']['name'])){
                $upload = new \Think\Upload();// 实例化上传类
                $upload->maxSize   =     3145728 ;// 设置附件上传大小
                $upload->exts      =     ['jpg', 'gif', 'png', 'jpeg'];// 设置附件上传类型
                $upload->rootPath  =      'data/upload/'; // 设置附件上传根目录
                // 上传单个文件
                $info   =   $upload->uploadOne($_FILES['icon_url']);
                if(!$info) {// 上传错误提示错误信息
                    $this->error($upload->getError());
                }else{// 上传成功 获取上传文件信息
                    $data['icon_url'] = "/data/Upload/".$info['savepath'].$info['savename'];
                }
            }


            $data['cname'] = $_POST['cname'];
            $data['status'] = $_POST['status'];
            if($cate -> create($data)){
                $data1 = $cate -> where("id ={$id}") -> save();
                if($data1!==false){
                     $this -> success("修改成功",U('Admin/category/index'),1);
                }else{
                    $this -> error("修改失败");
                }
            }
        }
    }
    /*删除分类*/
    public function del(){
        $id = $_GET['id'];
        $cate = M('category');
        $name = $cate->where("pid = {$id}")->find();
        if(!empty($name)){
            $this -> error("删除失败,当前分类包含子分类");
        }
        $tu = $cate->field('icon_url')->find($id);
        //将图片的地址已‘/’分隔成数组再用/hc_将数组连接成字符串 
        $thimgg = implode('/hc_',explode('/',$tu['icon_url']));
        $rootpath = './Public/Upload/fenlei/';
        //删除文件夹中的图片和缩略图
        unlink($rootpath.$thimgg);
        unlink($rootpath.$tu['icon_url']);
        $res = $cate -> delete($id);
        if ($res){
                $this -> success("删除成功",U('Admin/Category/index'),1);
        }else {
            $this -> error("删除失败");
        }
    }
}

