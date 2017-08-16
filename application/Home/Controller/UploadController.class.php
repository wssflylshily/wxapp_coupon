<?php
/**
 * Created by PhpStorm.
 * User: sunfan
 * Date: 2017/7/31
 * Time: 20:31
 */

namespace Home\Controller;


class UploadController extends MapiBaseController
{

    public function view()
    {
        $this->display();
    }

    /**
     * 上传图片
     */
    public function uploadImg()
    {
//        $this->ApiReturn(1, '成功', $_FILES);
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     3145728 ;// 设置附件上传大小
        $upload->exts      =     ['jpg', 'gif', 'png', 'jpeg'];// 设置附件上传类型
        $upload->rootPath  =      'data/upload/coupon/'; // 设置附件上传根目录
        // 上传单个文件
        $info   =   $upload->uploadOne($_FILES['touxiang']);
        if(!$info) {// 上传错误提示错误信息
            $this->ApiReturn(-1, $upload->getError());
        }else{// 上传成功 获取上传文件信息
            $map['url'] = "/data/Upload/coupon/".$info['savepath'].$info['savename'];
        }
        $this->ApiReturn(1, '成功', $map);
    }
}