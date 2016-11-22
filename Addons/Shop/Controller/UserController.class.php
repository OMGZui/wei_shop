<?php
namespace Addons\Shop\Controller;

use Addons\Shop\Controller\BaseController;
use Think\Page;


class UserController extends BaseController
{
    var $model;
    function _initialize() {
        $this->model = $this->getModel ( 'User' );
        parent::_initialize ();
    }

    //会员列表
    public function lists()
    {
        $token = get_token();
        $where['token'] = array('eq',$token);
        $uid = M('Public_follow')->field('uid')->where($where)->select();
        foreach ($uid as $value){
            $uid_data[] = $value['uid'];
        }
        $map['uid'] = array('in', $uid_data);
/*        if(I('uid')){
            $map['uid'] = array('eq',I('uid'));
        }*/
        if(I('start') && I('end')){
            $map['reg_time'] = array(array('gt',I('start')),array('lt',I('end')));
        }
        if(I('info')){
            $map['_query'] = 'nickname'. "=". I('info') ."&".'truename'."=".I('info')."&".'mobile'."=".I('info')."&".' _logic' ."=". 'or';
        }

        if(I('group')){
            $map['group'] = array('eq',I('group'));
        }
        if(I('level')){
            $map['level'] = array('eq',I('level'));
        }
        //dump($map);
        //获取一个店家的粉丝
        $lists = M('User')->where($map)->select();
        $count = count($lists);
        $Page       = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数()
        $Page->setConfig('next','»');
        $Page->setConfig('prev','«');
        $show       = $Page->show();// 分页显示输出
        $firstRow = $Page->firstRow;
        $listRows = $Page->listRows;
        $lists = M("User")->where($map)->limit($firstRow,$listRows)->select();
        $group = M("User_group")->where($where)->order('id asc')->select();
        $level = M("User_level")->where($where)->select();
        foreach ($level as $val){
            $levels[] = $val['chance'];
        }
        sort($levels);
        foreach ($lists as $key=>$value){
            $choose['uid'] = $value['uid'];
            $choose['status_code'] = array(array("gt",4),array("lt",8));
            $order = M("Shop_order")->where($choose)->field('total_price,status_code')->select();
            $lists[$key]['order'] = $order;
            $lists[$key]['total_money'] = M("Shop_order")->where($choose)->field('total_price,status_code')->sum("total_price");
        }
        //dump($lists);
        //lastsql();
        $this->assign('page',$show);// 赋值分页输出
        $this->assign('lists',$lists);
        $this->assign('count',$count);
        $this->assign('group',$group);
        $this->assign('level',$level);
        $this->assign('levels',$levels);
        $this->display();
    }
    //会员等级页面
    public function level()
    {
        $token['token'] = get_token();
        $lists = M("User_level")->where($token)->order('id asc')->select();
        $this->assign('lists',$lists);
        $this->display();
    }
    //添加会员等级页面
    public function level_add()
    {
        $this->display();
    }
    //添加会员等级
    public function level_do()
    {
        if(IS_POST){
            $data['level'] = I('level');
            $data['chance'] = I('chance');
            $data['token'] = get_token();
            $rs = M("User_level")->add($data);
            if($rs){
                $this->success('添加成功',addons_url("Shop://User/level"),1);
            }
        }
    }
    //编辑分级页面
    public function level_edit()
    {
        $id['id'] = I('id');
        $rs = M("User_level")->where($id)->find();
        $this->assign('level',$rs);
        $this->display();
    }
    //编辑分级
    public function level_edit_do()
    {
        $id['id'] = I('id');
        $data['level'] = I('level');
        $data['chance'] = I('chance');
        $rs = M("User_level")->where($id)->save($data);
        if($rs){
            $this->success('编辑成功',addons_url("Shop://User/level"),1);
        }
    }
    //删除分级
    public function level_del()
    {
        $id['id'] = I("id");
        $rs = M("User_level")->where($id)->delete();
        if($rs){
            $this->success('删除成功',addons_url("Shop://User/level"),1);
        }
    }

    //会员分组
    public function group()
    {
        $token['token'] = get_token();
        $lists = M("User_group")->where($token)->order('id asc')->select();
        foreach($lists as $key=>$value){
            $group[] = $value['group_name'];
        }

        $token_uid = M("public_follow")->where($token)->getField("uid",true);
        for($i=0;$i<count($group);$i++){
            $group1['group'] = $group[$i];
            $group1['uid'] = array("in",$token_uid);
            $group_num[] =  M("User")->where($group1)->select();
        }
        $this->assign('group_num',$group_num);
        $this->assign('lists',$lists);
        $this->display();
    }
    //添加分组页面
    public function group_add()
    {
        $this->display();
    }
    //添加分组
    public function add_do()
    {
        if(IS_POST){
            $data['group_name'] = I('group');
            $data['token'] = get_token();
            $rs = M("User_group")->add($data);
            if($rs){
                $this->success('成功',addons_url("Shop://User/group"),1);
            }
        }
    }
    //编辑分组页面
    public function group_edit()
    {
        $id['id'] = I('id');
        $rs = M("User_group")->where($id)->find();
        $this->assign('group',$rs);
        $this->display();
    }
    //编辑分组
    public function group_edit_do()
    {
        $id['id'] = I('id');
        $data['group_name'] = I('group_name');
        $rs = M("User_group")->where($id)->save($data);
        if($rs){
            $this->success('编辑成功',addons_url("Shop://User/group"),1);
        }
    }
    //删除分组
    public function group_del()
    {
        $id['id'] = I("id");
        $rs = M("User_group")->where($id)->delete();
        if($rs){
            $this->success('删除成功',addons_url("Shop://User/group"),1);
        }
    }
    //会员详细页面
    public function detail()
    {
        $uid['uid'] = I('uid');
        $rs = M("User")->where($uid)->find();
        $data = M("Public_follow")->where($uid)->find();
        $where['token'] = get_token();
        $level = M("User_level")->where($where)->order('id asc')->select();
        $group = M("User_group")->where($where)->order('id asc')->select();
        $choose['uid'] = I('uid');
        $choose['status_code'] = array(array("gt",4),array("lt",8));
        $order = M("Shop_order")->where($choose)->field('total_price,status_code')->select();
        $total_money = M("Shop_order")->where($choose)->field('total_price,status_code')->sum("total_price");
        $this->assign("rs",$rs);
        $this->assign("data",$data);
        $this->assign("level",$level);
        $this->assign("group",$group);
        $this->assign("order",$order);
        $this->assign("total_money",$total_money);
        $this->display();
    }
    //更新用户信息
    public function info_update()
    {
        if(IS_POST){
            $data['level'] = I('level');
            $data['group'] = I('group');
            $data['truename'] = I('truename');
            $data['mobile'] = I('mobile');
            $data['weixin'] = I('weixin');
            $data['remarks'] = I('remarks');
            $uid['uid'] = I('uid');
            $rs = M("User")->where($uid)->save($data);
            if($rs){
                $this->success('成功',addons_url("Shop://User/lists"),1);
            }
        }
    }

}