<?php
/**
 * Created by PhpStorm.
 * User: zui
 * Date: 2016/10/18
 * Time: 16:08
 */

namespace Addons\Shop\Model;

use Think\Model;

class ShopuserModel extends Model {
    protected $tableName = 'Shop_user';
    function getLists($map){
        $rs = $this->where($map)->select();
        return $rs;
    }
}