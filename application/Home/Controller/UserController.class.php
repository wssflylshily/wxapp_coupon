<?php
/**
 * Created by PhpStorm.
 * User: sunfan
 * Date: 2017/7/25
 * Time: 16:29
 */

namespace Home\Controller;


class UserController extends UserApiBaseController
{
    /**
     * 发布优惠券
     */
    public function publish()
    {
        $data = $this->data;
        $id = S($data['token']);
//        $id = 20;
        $url = $data['url']?$data['url']:$this->ApiReturn(-1, '缩略图不能为空');
        $title = $data['title']?$data['title']:$this->ApiReturn(-1, '优惠券名称不能为空');
        $category_id = $data['category_id']?$data['category_id']:$this->ApiReturn(-1, '行业类别不能为空');
        $money = $data['money']?$data['money']:$this->ApiReturn(-1, '优惠金额/折扣不能为空');
        $unit = $data['unit']?$data['unit']:$this->ApiReturn(-1, '单位不能为空');
        $num = $data['num']?$data['num']:$this->ApiReturn(-1, '优惠券数量不能为空');
        $s_time = $data['s_time']?$data['s_time']:$this->ApiReturn(-1, '开始时间不能为空');
        $e_time = $data['e_time']?$data['e_time']:$this->ApiReturn(-1, '结束时间不能为空');
        $desc = $data['desc']?$data['desc']:"无限制";
        $keyword = $data['keyword']?$data['keyword']:$this->ApiReturn(-1, '关键字不能为空');
        $shop_id = $data['shop_id']?$data['shop_id']:$this->ApiReturn(-1, '门店id不能为空');

        $shopinfo = M('MemberAddress')->where(['id'=>$shop_id, 'user_id'=>$id])->find();
        if (empty($shopinfo))$this->ApiReturn(-1, '门店不存在');
        $map = [
            'url'   =>  $url,
            'user_id'   =>  $id,
            'title'   =>  $title,
            'category_id'   =>  $category_id,
            'money'   =>  $money,
            'unit'   =>  $unit,
            'num'   =>  $num,
            's_time'   =>  $s_time,
            'e_time'   =>  $e_time,
            'desc'   =>  $desc,
            'keyword'   =>  $keyword,
            'shop_name'   =>  $shopinfo['shop_name'],
            'mobile'   =>  $shopinfo['mobile'],
            'address'   =>  $shopinfo['address'],
            'longitude'   =>  $shopinfo['longitude'],
            'latitude'   =>  $shopinfo['latitude']
        ];

        $coupon_id = M('Coupon')->add($map);

        //添加一条核销员信息
        M('Salesperson')->add(['user_id'=>$id, 'coupon_id'=>$coupon_id, 'sp_time'=>time()]);

        $this->ApiReturn(1, '成功');

    }

    public function getMyAddress()
    {
        $data = $this->data;
        $id = S($data['token']);
//        $id = 20;
        $rs = M('MemberAddress')->where(['user_id'=>$id])->select();
        if (empty($rs))$this->ApiReturn(0, '没有数据');
        $this->ApiReturn(1, '成功', $rs);
    }

    /**
     * 生成小程序二维码
     */
    public function getSalesQrcode()
    {
        $data = $this->data;
        $cid = $data['coupon_id'];

        $appid = C('WxAppPayConf_pub.APPID');    //我配置在config文件里了
        $secret = C('WxAppPayConf_pub.APPSECRET');
        $tokenUrl="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$secret";
        $getArr=[];
        $tokenArr = json_decode(send_post($tokenUrl, $getArr, 'GET'));
        $access_token = $tokenArr->access_token;

        $path=$cid;
        $post_data = json_encode(['scene'=>$path]);
        $url="http://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=".$access_token;
        $result=api_notice_increment($url,$post_data);
        echo $result;
    }

    /**
     * 生成添加核销员二维码
     */
    public function addSalespersonQrcode()
    {
        $data = $this->data;
        $id = S($data['token']);
//        $id = 123;

        $appid = C('WxAppPayConf_pub.APPID');    //我配置在config文件里了
        $secret = C('WxAppPayConf_pub.APPSECRET');
        $tokenUrl="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$secret";
        $getArr=[];
        $tokenArr = json_decode(send_post($tokenUrl, $getArr, 'GET'));
        $access_token = $tokenArr->access_token;

        $path=$id;
        $post_data = json_encode(['scene'=>$path]);
        $url="http://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=".$access_token;
        $result=api_notice_increment($url,$post_data);
        echo $result;
    }

    /**
     * 我发布的优惠券
     */
    public function myPublishCoupon()
    {
        $data = $this->data;
        $id = S($data['token']);
//        $id = 20;
        $where['user_id'] = $id;
        $page = $data['page']?$data['page']:1;
        $rs = M('Coupon')->where($where)->field('id as coupon_id, url, title, view, s_time, e_time, review, status')->page($page, 20)->select();
        if (empty($rs))$this->ApiReturn(0, '成功');

        $return=[];
        foreach ($rs as $k=>$item)
        {
            $return[$k]['coupon_id'] = $item['coupon_id'];
            $return[$k]['img'] = $item['url'];
            $return[$k]['title'] = $item['title'];
            $return[$k]['s_time'] = $item['s_time'];
            $return[$k]['e_time'] = $item['e_time'];
            if ($item['review']==1)
            {
                $return[$k]['state'] = 1;       //审核中
            }elseif($item['status']==1)
            {
                $return[$k]['state'] = 2;       //显示中
            }else{
                $return[$k]['state'] = 3;       //不显示
            }
        }
        $this->ApiReturn(1, '成功', $return);
    }

    /**
     * 我领取的优惠券
     */
    public function myReceiveCoupon()
    {
        $data = $this->data;
        $id = S($data['token']);
//        $id = 20;
        $page = $data['page']?$data['page']:1;
        $type = $data['status']?$data['status']:3; //1.未使用 2.已使用 3.浏览
        $rs = M('CouponLog')
            ->join('LEFT JOIN yhq_coupon ON yhq_coupon_log.coupon_id=yhq_coupon.id')
            ->where(['yhq_coupon_log.status'=>$type, 'yhq_coupon_log.user_id'=>$id])
            ->field('yhq_coupon_log.coupon_id, yhq_coupon.url as headimg, yhq_coupon.title as nickname, yhq_coupon_log.cl_time')
            ->page($page, 20)
            ->select();
        if (empty($rs))$this->ApiReturn(0, '没有数据');
        $return['count'] = count($rs);
        $return['list'] = $rs;
        $this->ApiReturn(1, '成功', $return);
    }

    /**
     * 添加核销员
     */

    public function addSalesperson()
    {
        $data = $this->data;
        $id = S($data['token']);
        $owner_id = $data['owner_id'];
//        $id = 20;
        $coupons = M('Coupon')->where(['user_id'=>$owner_id])->select();
        foreach ($coupons as $coupon)
        {
            M('Salesperson')->add(['user_id'=>$id, 'coupon_id'=>$coupon['id'], 'sp_time'=>time()]);
        }

        $this->ApiReturn(1, '成功');
    }

    /**
     * 删除核销员
     */
    public function delSalesperson()
    {
        $data = $this->data;
        $id = S($data['token']);
//        $id = 20;
        $salesperson_id = $data['salesperson_id'];

        $coupons = M('Coupon')->where(['user_id'=>$id])->select();
        foreach ($coupons as $coupon)
        {
            M('Salesperson')->where(['id'=>$coupon['id'], 'user_id'=>$salesperson_id])->delete();
        }

        $this->ApiReturn(1, '删除成功');

    }


    /**
     * 核销员列表
     */
    public function salespersonList()
    {
        $data = $this->data;
        $id = S($data['token']);
//        $id = 20;
        $page = $data['page']?$data['page']:1;
        $coupon_id = $data['coupon_id'];
        $rs = M('Salesperson')
            ->join('LEFT JOIN yhq_member ON yhq_member.id=yhq_salesperson.user_id')
            ->where(['coupon_id'=>$coupon_id])
            ->field('yhq_member.img as headimg, yhq_member.nickname, yhq_salesperson.sp_time')
            ->page($page, 20)
            ->select();
        if (empty($rs))$this->ApiReturn(0, '没有数据');
        $this->ApiReturn(1, '成功', $rs);
    }

    /**
     * 核销
     */
    public function useCoupon()
    {
        $data = $this->data;
        $id = S($data['token']);
//        $id = 20;
        $coupon_id = $data['coupon_id'];

        if (M('Salesperson')->where(['user_id'=>$id, 'coupon_id'=>$coupon_id])->find())$this->ApiReturn(-1, '您不是该优惠券的核销员');
        $couponlog = M('CouponLog')->where(['user_id'=>$id, 'coupon_id'=>$coupon_id])->find();
        if (!$couponlog)$this->ApiReturn(-1, '优惠券不存在');
        if ($couponlog['status']==2)$this->ApiReturn(-1,'该优惠券已使用');
        if ($couponlog['status']==3)$this->ApiReturn(-1,'请先领取优惠券');

        M('CouponLog')->where(['user_id'=>$id, 'coupon_id'=>$coupon_id])->save(['status'=>2]);
        $this->ApiReturn(1, '核销成功');
    }


    /**
     * 添加门店
     */
    public function addShop()
    {
        $data = $this->data;
        $id = S($data['token']);
//        $id = 20;
        $name = $data['name']?$data['name']:$this->ApiReturn(-1, '名字不能为空');
        $mobile = $data['mobile']?$data['mobile']:$this->ApiReturn(-1, '联系方式不能为空');
        $address = $data['address']?$data['address']:$this->ApiReturn(-1, '地址不能为空');
        $longitude = $data['longitude']?$data['longitude']:$this->ApiReturn(-1, '地址不能为空');
        $latitude = $data['latitude']?$data['latitude']:$this->ApiReturn(-1, '地址不能为空');
        $map = [
            'user_id'   =>  $id,
            'shop_name' =>  $name,
            'mobile' =>  $mobile,
            'address' =>  $address,
            'longitude'    =>  $longitude,
            'latitude'    =>  $latitude,
        ];

        M('MemberAddress')->add($map);

        $this->ApiReturn(1, '添加成功');
    }

    /**
     * 修改门店
     */
    public function modShop()
    {
        $data = $this->data;
        $id = S($data['token']);
//        $id = 20;
        $shop_id = $data['shop_id']?$data['shop_id']:$this->ApiReturn(-1, 'id不能为空');
        $name = $data['name']?$data['name']:$this->ApiReturn(-1, '名字不能为空');
        $mobile = $data['mobile']?$data['mobile']:$this->ApiReturn(-1, '联系方式不能为空');
        $address = $data['address']?$data['address']:$this->ApiReturn(-1, '地址不能为空');
        $longitude = $data['longitude']?$data['longitude']:$this->ApiReturn(-1, '地址不能为空');
        $latitude = $data['latitude']?$data['latitude']:$this->ApiReturn(-1, '地址不能为空');
        $map = [
            'shop_name' =>  $name,
            'mobile' =>  $mobile,
            'address' =>  $address,
            'longitude'    =>  $longitude,
            'latitude'    =>  $latitude,
        ];

        M('MemberAddress')->where(['id'=>$shop_id, 'user_id'=>$id])->save($map);

        $this->ApiReturn(1, '修改成功');
    }

    /**
     * 删除门店
     */
    public function delShop()
    {
        $data = $this->data;
        $id = S($data['token']);
//        $id = 20;
        $shop_id = $data['shop_id'];
        if (!M('MemberAddress')->where(['id'=>$shop_id, 'user_id'=>$id])->find())$this->ApiReturn(-1, '门店不存在');
        M('MemberAddress')->where(['id'=>$shop_id, 'user_id'=>$id])->delete();
        $this->ApiReturn(1, '删除成功');

    }

    /**
     * 上架 优惠券
     */
    public function onCoupon()
    {
        $data = $this->data;
        $id = S($data['token']);
        $coupon_id = $data['coupon_id']?$data['coupon_id']:$this->ApiReturn(-1, '优惠券id不能为空');
        M('Coupon')->where(['id'=>$coupon_id])->save(['status'=>1]);
        $this->ApiReturn(1, '上架成功');
    }

    /**
     * 下架 优惠券
     */
    public function offCoupon()
    {
        $data = $this->data;
        $id = S($data['token']);
        $coupon_id = $data['coupon_id']?$data['coupon_id']:$this->ApiReturn(-1, '优惠券id不能为空');
        M('Coupon')->where(['id'=>$coupon_id])->save(['status'=>2]);
        $this->ApiReturn(1, '下架成功');
    }

    /**
     * 删除 优惠券
     */
    public function delCoupon()
    {
        $data = $this->data;
        $id = S($data['token']);
        $coupon_id = $data['coupon_id']?$data['coupon_id']:$this->ApiReturn(-1, '优惠券id不能为空');
        M('Coupon')->where(['id'=>$coupon_id])->delete();
        $this->ApiReturn(1, '删除成功');
    }

    /**
     * 数据统计
     */
    public function statistics()
    {
        $data = $this->data;
        $id = S($data['token']);
//        $id = 20;
        $coupon_id = $data['coupon_id'];
        $page = $data['page']?$data['page']:1;
        $type = $data['status']?$data['status']:3; //1.未使用 2.已使用 3.浏览
        $rs = M('CouponLog')
            ->join('LEFT JOIN yhq_member ON yhq_coupon_log.user_id=yhq_member.id')
            ->where(['yhq_coupon_log.status'=>$type, 'yhq_coupon_log.coupon_id'=>$coupon_id])
            ->field('yhq_coupon_log.coupon_id, yhq_member.img as headimg, yhq_member.nickname, yhq_coupon_log.cl_time')
            ->page($page, 20)
            ->select();
        if (empty($rs))$this->ApiReturn(0, '没有数据');
        $return['count'] = count($rs);
        $return['list'] = $rs;
        $this->ApiReturn(1, '成功', $return);
    }


}