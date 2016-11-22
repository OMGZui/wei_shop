<?php

namespace Addons\HelloWorld;
use Common\Controller\Addon;

/**
 * 微信示列插件
 * @author 无名
 */

    class HelloWorldAddon extends Addon{

        public $info = array(
            'name'=>'HelloWorld',
            'title'=>'微信示列',
            'description'=>'这是一个临时描述',
            'status'=>1,
            'author'=>'无名',
            'version'=>'0.1',
            'has_adminlist'=>0
        );

	public function install() {
		$install_sql = './Addons/HelloWorld/install.sql';
		if (file_exists ( $install_sql )) {
			execute_sql_file ( $install_sql );
		}
		return true;
	}
	public function uninstall() {
		$uninstall_sql = './Addons/HelloWorld/uninstall.sql';
		if (file_exists ( $uninstall_sql )) {
			execute_sql_file ( $uninstall_sql );
		}
		return true;
	}

        //实现的weixin钩子方法
        public function weixin($param){

        }

    }