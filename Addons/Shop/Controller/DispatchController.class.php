<?php

namespace Addons\Shop\Controller;

use Home\Controller\AddonsController;

class DispatchController extends AddonsController {
    //配送首页
    public function dispatch(){
        session('shop_id',I('shop_id'));
        $rs = M("Shop_goods_dispatch")->select();
        $this->assign('lists',$rs);
        $this->display('Goods/dispatch');
    }
    //添加配送
    public function dispatch_add()
    {
        $rs = M("Shop_express")->select();
        $this->assign('lists',$rs);
        $this->display('Goods/dispatch_add');
    }

    public function dispatch_add_do()
    {
        $data['shop_id'] =  I("shop_id");
        $data['dispatch_type'] =  I("dispatch_type");
        $data['is_show'] =  I("is_show");
        $data['express_id'] =  I("express_id");
        $rs = M("Shop_goods_dispatch")->add($data);
        if($rs){
            $this->success('添加成功',addons_url("Shop://dispatch/dispatch",array('shop_id'=>session('shop_id'))),1);
        }
    }
    //编辑配送
    public function dispatch_edit()
    {
        $id['id'] = I("id");
        $data = M("Shop_goods_dispatch")->where($id)->find();
        $rs = M("Shop_express")->select();
        $this->assign('lists',$rs);
        $this->assign('data',$data);
        $this->display('Goods/dispatch_edit');
    }

    public function dispatch_edit_do()
    {
        $id['id'] =  I("id");
        $data['dispatch_type'] =  I("dispatch_type");
        $data['is_show'] =  I("is_show");
        $data['express_id'] =  I("express_id");
        $rs = M("Shop_goods_dispatch")->where($id)->save($data);
        if($rs){
            $this->success('修改成功',addons_url("Shop://dispatch/dispatch",array('shop_id'=>session('shop_id'))),1);
        }


    }

    public function dispatch_del()
    {
        $id['id'] = I("id");
        $rs = M("Shop_goods_dispatch")->where($id)->delete();
        if($rs){
            $this->success('添加成功',addons_url("Shop://dispatch/dispatch",array('shop_id'=>session('shop_id'))),1);
        }
    }
}
