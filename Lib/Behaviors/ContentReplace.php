<?php
namespace Think\Behaviors;

use Think\Behavior as Behavior;

class ContentReplace extends Behavior {
    // 行为参数定义
    protected $options   =  array(
        'TMPL_PARSE_STRING' =>  array(),
    );

    // 行为扩展的执行入口必须是run
    public function run(&$content){
        $content = $this->templateContentReplace($content);
    }

    /**
     * 模板内容替换
     * @access protected
     * @param string $content 模板内容
     * @return string
     */
    protected function templateContentReplace($content) {
        // 系统默认的特殊变量替换
        $replace =  array(
            // '__TMPL__'      =>  THEME_PATH,  // 项目模板目录
            // '__ROOT__'      =>  __ROOT__,       // 当前网站地址
            '__APP__'       =>  '',        // 当前项目地址
            '__GROUP__'     =>  __GROUP__,
            '__ACTION__'    =>  __ACTION__,     // 当前操作地址
            '__SELF__'      =>  __SELF__,       // 当前页面地址
            '__URL__'       =>  __URL__,
            // '../Public'     =>  APP_TMPL_PATH.'Public',// 项目公共模板目录
            // '__PUBLIC__'    =>  __ROOT__.'/Public',// 站点公共目录
        );
        // 允许用户自定义模板的字符串替换
        if(is_array(C('TMPL_PARSE_STRING')) )
            $replace =  array_merge($replace,C('TMPL_PARSE_STRING'));
        $content = str_replace(array_keys($replace),array_values($replace),$content);
        return $content;
    }

}