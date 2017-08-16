<?php
/**
 * Created by PhpStorm.
 * User: sunfan
 * Date: 2017/2/4
 * Time: 11:38
 */

namespace Home\Controller;

class UserApiBaseController extends MapiBaseController
{
    protected function _initialize(){
        header("Access-Control-Allow-Origin: *");
        $this->data = $_REQUEST;
        $this->writeLog("收到用户:".json_encode($this->data));
        if(is_null($this->data)){
            $this->ApiReturn(20000,'参数格式错误，除文件上传外所有参数格式为JSON格式');
        }
        $data = $this->data;
        $token = $data['token'];
        if(!S($token)){
            $this->ApiReturn(20003,'请先授权登陆','');
        }
    }

}