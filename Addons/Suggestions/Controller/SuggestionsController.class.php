<?php

namespace Addons\Suggestions\Controller;
use Home\Controller\AddonsController;

class SuggestionsController extends AddonsController{
    function lists(){
        //获取模型信息
        $model = $this->getModel();
        //获取模型数据列表
        $list_data = $this->_get_model_list($model);
        //获取相关的用户信息
        $uids = getSubByKey($list_data['list_data'],'uid');
        $uids = array_filter($uids);
        $uids = array_unique($uids);
        if(!empty($uids)){
            $map['uid'] = array('in', $uids);
            $members = M('member')->where($map)->field('uid,nickname,truename,mobile')->select();
            foreach ($members as $m ){
                !empty($m['truename']) || $m['truename'] = $m['nickname'];
                $user[$m['uid']] = $m;
            }
            foreach ($list_data['list_data'] as &$vo){
                $vo['mobile'] = $user[$vo['uid']]['mobile'];
                $vo['uid'] = $user[$vo['uid']]['truename'];
            }
        }
        $this->assign($list_data);
        $this->display($model['template_list']);
    }
}
