<?php
namespace Think\Drivers\Template;
/**
 * Driver/Template/TemplateSmarty.class.php
 * Smart ThinkPHP
 * Copyright (c) 2004-2013 Methink
 *
 * @copyright     Copyright (c) Methink
 * @link          https://github.com/minowu/thinkphp
 * @package       Driver/Template/TemplateSmarty
 * @since         Smart ThinkPHP 2.0.0
 * @license       Apache License (http://www.apache.org/licenses/LICENSE-2.0)
 */

use \Smarty;
use Think\Import as Import;
use Think\Config as Config;

Import::load(CORE_PATH . 'Library/Smarty/Smarty' . EXT);

/**
 * TemplateSmarty Class
 * Smarty模板引擎的驱动
 */
class Smarty {

    /**
     * 渲染模板输出
     * @access public
     * @param string $templateFile 模板文件名
     * @param array $var 模板变量
     * @return void
     */
    public function fetch($templateFile, $var) {

        // 实例化
        $tpl = new Smarty();

        // 是否开启缓存, 模板目录, 编译目录, 缓存目录
        $tpl->caching           = Config::get('TMPL_CACHE_ON');
        $tpl->template_dir      = THEME_PATH;
        $tpl->compile_dir       = CACHE_PATH;
        $tpl->cache_dir         = TEMP_PATH;
        $tpl->debugging         = false;
        $tpl->left_delimiter    = '{{';
        $tpl->right_delimiter   = '}}';

        // 自定义配置        
        if(C('TMPL_ENGINE_CONFIG')) {
            $config  =  C('TMPL_ENGINE_CONFIG');
            foreach ($config as $key => $val){
                $tpl->{$key} = $val;
            }
        }

        // 输出
        $tpl->assign($var);
        $tpl->display($templateFile);
    }
}