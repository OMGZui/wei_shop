<?php

namespace Addons\Message\Controller;
use Home\Controller\AddonsController;

class MessageController extends AddonsController{
    public function index()
    {
        $rs = M('Messageinfo')->field('name,content,ctime')->select();
        $this->assign('list',$rs);
        $this->display();
    }

    public function message()
    {
        $user = get_followinfo($this->mid);
        //dump($user);
        $this->assign('user',$user);

        $this->display();
    }

    public function add()
    {
        if(IS_POST){
            $data['token'] = get_token();
            $data['uid'] = $this->mid;
            $data['name'] = I('name') ? I('name') : '匿名';
            $data['content'] = I('content') ? I('content') : '加油看好你';
            $data['ctime'] = time();
            $rs = M("Messageinfo")->add($data);
            if($rs){
/*                $jump_url =addons_url('Message://Message/index');
                redirect($jump_url);*/
                $this->success('留言成功');
            }else{
                $this->error('留言失败');
            }
        }
    }
}
