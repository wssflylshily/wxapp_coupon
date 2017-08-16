<?php
namespace User\Controller;

use Common\Controller\AdminbaseController;

class IndexadminController extends AdminbaseController {
    
    // 后台本站用户列表
    public function index(){
        $where=array();
        $request=I('request.');
        
        if(!empty($request['keyword'])){
            $where['nickname']=['like', '%'.$request['keyword'].'%'];
        }
        
    	$user = M("member");
    	
    	$count=$user->where($where)->count();
    	$page = $this->page($count, 20);
        $list = $user->where($where)->order("create_time DESC")->limit($page->firstRow . ',' . $page->listRows)->select();
    	$this->assign('list', $list);
    	$show = $page->show('Admin');
    	$this->assign("page", $show);


    	$this->display(":index");
    }
    
    public function add(){
        $this->display(":add");
    }

    public function mod(){
        $user = M("member");
        $id = $_GET['id'];
        $list = $user->where("id={$id}")->find();
        $this->assign('list',$list);
        $this->display(":mod");
    }

    public function modu(){
        $user = M('member');
        $name = $_POST['phone'];
        $id = $_POST['id'];
        $name = $user->where("phone ='{$name}' && id!='{$id}'")->find();
        if(!empty($name)){
            $this -> error("此账号已存在");
        }else{
            //dump($_FILES);exit;
            if(!$_FILES['img']['error']){
            //实例化上传文件的类
            $upload = new \Think\Upload();
            //选择上传文件的大小
            $upload->maxSize = 3145728 ;
            //选择上传文件的类型
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg');
            //将上传的图片存放在指定文件夹下
            $upload->rootPath = "./public/Upload/uimg/";
            $fileName=$_FILES["img"]['name'];//这样就能够取得上传的文件名  
            $fileExtensions=strrchr($fileName, '.');//通过对$fileName的处理，就能够取得上传的文件的后缀名  
            $serverFileName=basename($fileName,$fileExtensions)."_".uniqid().$fileExtensions;  
            $upload->saveRule=$serverFileName;
            //调用上传文件类中的upload方法
            $info = $upload -> upload();
            //判断调用是否成功
                if($info){
                  //实例化缩略图类
                  $image = new \Think\Image();
                  $pic = $info['img']["savepath"].$info['img']['savename'];
                  $image -> open($upload->rootPath.$pic);
                  $thumbpath = $upload ->rootPath.$info['img']['savepath'].'hc_'.$info['img']['savename'];
                  $image -> thumb(150,150) -> save($thumbpath);
                  //得到要存入数据库中的地址
                  $_POST['img'] = "/public/Upload/uimg/".$pic;
                  //查询出原来的图片
                  $coursepic = $user->field('img')->find($id);
                  //将图片的地址已‘/’分隔成数组再用/hc_将数组连接成字符串 
                  $thimg = implode('/hc_',explode('/',$coursepic['img']));
                  $rootpath = './Public/Upload/uimg/';
                  //删除文件夹中的图片和缩略图
                  unlink($rootpath.$thimg);
                  unlink($rootpath.$coursepic['img']);
                }else{
                    $this -> error('生成缩略图失败');
                }
                $data['img'] = $_POST['img'];
            }
            if(!empty($_POST['password'])){
                $data['password'] = md5($_POST['password']);
            }
            $data['nickname']=$_POST['nickname'];
            $data['integral']=$_POST['integral'];
            $data['status']=$_POST['status'];
            $data['phone'] = $_POST['phone'];
            if($user->create($data)){
                $data1 = $user->where("id ={$id}")->save();
                if($data1!==false){
                     $this->success("修改成功");
                }else{
                    $this->error("修改失败");
                }
            }
        }
    }

    public function del(){
        $id = $_GET['id'];
        $user = M("member");
        $tu = $user->field('img')->find($id);
        //将图片的地址已‘/’分隔成数组再用/hc_将数组连接成字符串 
        $thimg = implode('/hc_',explode('/',$tu['img']));
        $rootpath = './Public/Upload/uimg/';
        //删除文件夹中的图片和缩略图
        unlink($rootpath.$thimg);
        unlink($rootpath.$tu['img']);
        $res = $user -> delete($id);
        if($res){
            $this -> success("删除成功");
        }else{
            $this -> error("删除失败");
        }
    }

    /**
     * 启用/禁用
     * @author biawei
     * time:2017/7/6 17:08
     */
    public function special(){
        $id = $_GET['id'];
        $user = M("member");
        $result = $user->where("id={$id}")->setField('status',$_GET['status']);
        if($result){
            $this -> success("操作成功");
        }else{
            $this -> error("操作失败");
        }
    }

    public function insertu(){
        $user = M("member");
        $name = $_POST['phone'];
        $pass = md5($_POST['password']);
        $res = $user->where("phone = '{$name}'")->find();
        if($res){
            $this -> error("此用户已存在");
        }else{
            $_POST['password'] = $pass;
            $_POST['create_time'] = time();
            $_POST['img'] = $_POST['img'];
            if(!$_FILES['img']['error']){
            //实例化上传文件的类
            $upload = new \Think\Upload();
            //选择上传文件的大小
            $upload->maxSize = 3145728 ;
            //选择上传文件的类型
            $upload->exts = array('jpg', 'gif', 'png', 'jpeg');
            //将上传的图片存放在指定文件夹下
            $upload->rootPath = "./public/Upload/uimg/";
            $fileName=$_FILES["img"]['name'];//这样就能够取得上传的文件名  
            $fileExtensions=strrchr($fileName, '.');//通过对$fileName的处理，就能够取得上传的文件的后缀名  
            $serverFileName=basename($fileName,$fileExtensions)."_".uniqid().$fileExtensions;  
            $upload->saveRule=$serverFileName;
            //调用上传文件类中的upload方法
            $info = $upload -> upload();
            //判断调用是否成功
                if($info){
                  //实例化缩略图类
                  $image = new \Think\Image();
                  $pic = $info['img']["savepath"].$info['img']['savename'];
                  $image -> open($upload->rootPath.$pic);
                  $thumbpath = $upload ->rootPath.$info['img']['savepath'].'hc_'.$info['img']['savename'];
                  $image -> thumb(150,150) -> save($thumbpath);
                  //得到要存入数据库中的地址
                  $_POST['img'] = "/public/Upload/uimg/".$pic;
                }else{
                    $this -> error('生成缩略图失败');
                }
            }
            if($user -> create($_POST)){
                $rees = $user -> add();
                $this -> success("添加成功");
            }else{
                $this -> error("添加失败");
            }
        }
    }

    // 后台本站用户禁用
    public function ban(){
    	$id= I('get.id',0,'intval');
    	if ($id) {
    		$result = M("Users")->where(array("id"=>$id,"user_type"=>2))->setField('user_status',0);
    		if ($result) {
    			$this->success("会员拉黑成功！", U("indexadmin/index"));
    		} else {
    			$this->error('会员拉黑失败,会员不存在,或者是管理员！');
    		}
    	} else {
    		$this->error('数据传入失败！');
    	}
    }
    
    // 后台本站用户启用
    public function cancelban(){
    	$id= I('get.id',0,'intval');
    	if ($id) {
    		$result = M("Users")->where(array("id"=>$id,"user_type"=>2))->setField('user_status',1);
    		if ($result) {
    			$this->success("会员启用成功！", U("indexadmin/index"));
    		} else {
    			$this->error('会员启用失败！');
    		}
    	} else {
    		$this->error('数据传入失败！');
    	}
    }
}
