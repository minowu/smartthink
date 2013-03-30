<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2012 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

/**
 * ThinkPHP 应用程序类 执行应用过程管理
 * 可以在模式扩展中重新定义 但是必须具有Run方法接口
 * @category   Think
 * @package  Think
 * @subpackage  Core
 * @author    liu21st <liu21st@gmail.com>
 */
class App {

    /**
     * 应用程序初始化
     * @access public
     * @return void
     */
    static public function init() {

        // 设置系统时区
        date_default_timezone_set(C('DEFAULT_TIMEZONE'));

        // 定义当前请求的系统常量
        define('NOW_TIME',      $_SERVER['REQUEST_TIME']);
        define('REQUEST_METHOD',$_SERVER['REQUEST_METHOD']);
        define('IS_GET',        REQUEST_METHOD =='GET' ? true : false);
        define('IS_POST',       REQUEST_METHOD =='POST' ? true : false);
        define('IS_PUT',        REQUEST_METHOD =='PUT' ? true : false);
        define('IS_DELETE',     REQUEST_METHOD =='DELETE' ? true : false);
        define('IS_AJAX',       ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || !empty($_POST[C('VAR_AJAX_SUBMIT')]) || !empty($_GET[C('VAR_AJAX_SUBMIT')])) ? true : false);

        // URL调度结束标签
        Tag::mark('url_dispatch');

        // 页面压缩输出支持
        if(Config::get('OUTPUT_ENCODE')) {
            $zlib = ini_get('zlib.output_compression');
            if(empty($zlib)) {
                ob_start('ob_gzhandler');
            }
        }

        // 系统变量安全过滤
        if(Config::get('VAR_FILTERS')) {
            $filters = explode(',',Config::get('VAR_FILTERS'));
            foreach($filters as $filter){
                // 全局参数过滤
                array_walk_recursive($_POST,$filter);
                array_walk_recursive($_GET,$filter);
            }
        }

        // 配置主题目录
        define('THEME_PATH', TMPL_PATH . GROUP_NAME . '/');
        define('APP_TMPL_PATH' , __ROOT__ . '/' . APP_NAME . (APP_NAME ? '/' : '') . basename(TMPL_PATH) . '/' . GROUP_NAME . '/');

        // 缓存路径
        Config::set('CACHE_PATH', CACHE_PATH . GROUP_NAME . '/');

        // 动态配置 TMPL_EXCEPTION_FILE，改为绝对地址
        Config::set('TMPL_EXCEPTION_FILE', realpath(Config::get('TMPL_EXCEPTION_FILE')));
    }

    /**
     * 执行应用程序
     * @access public
     * @return void
     */
    static public function exec() {

        // 针对MODULE_NAME进行安全检测
        if(!preg_match('/^[A-Za-z](\w)*$/', MODULE_NAME)) {
            $module = false;
        }
        //创建Action控制器实例
        else {
            $group = defined('GROUP_NAME') && C('APP_GROUP_MODE') == 0 ? GROUP_NAME . '/' : '';
            $module = A($group . MODULE_NAME);
        }
        // dump($module);
        // 不存在当前Module
        if(!$module) {
            if('4e5e5d7364f443e28fbf0d3ae744a59a' == MODULE_NAME) {
                exit('exec, App.class.php in line 108');
            }

            // hack 方式定义扩展模块 返回Action对象
            if(function_exists('__hack_module')) {
                $module = __hack_module();
                if(!is_object($module)) {
                    // 不再继续执行 直接返回
                    return ;
                }
            }
            // 是否定义Empty模块
            else {
                $module = A($group.'Empty');
                if(!$module){
                    Http::_404(L('_MODULE_NOT_EXIST_').':'.MODULE_NAME);
                }
            }
        }

        // 获取当前操作名 支持动态路由
        
        $action = C('ACTION_NAME') ? C('ACTION_NAME') : ACTION_NAME;
        C('TEMPLATE_NAME', THEME_PATH . MODULE_NAME . C('TMPL_FILE_DEPR') . $action . C('TMPL_TEMPLATE_SUFFIX'));
        $action .= C('ACTION_SUFFIX');

        try {
            // 非法操作
            if(!preg_match('/^[A-Za-z](\w)*$/', $action)) {
                throw new ReflectionException();
            }
            //执行当前操作
            $method = new ReflectionMethod($module, $action);
            
            if($method->isPublic()) {
                $class = new ReflectionClass($module);
                // 前置操作
                if($class->hasMethod('_before_'.$action)) {
                    $before =   $class->getMethod('_before_'.$action);
                    if($before->isPublic()) {
                        $before->invoke($module);
                    }
                }
                // URL参数绑定检测
                if(C('URL_PARAMS_BIND') && $method->getNumberOfParameters()>0){
                    switch($_SERVER['REQUEST_METHOD']) {
                        case 'POST':
                            $vars    =  $_POST;
                            break;
                        case 'PUT':
                            parse_str(file_get_contents('php://input'), $vars);
                            break;
                        default:
                            $vars  =  $_GET;
                    }
                    $params =  $method->getParameters();
                    foreach ($params as $param){
                        $name = $param->getName();
                        if(isset($vars[$name])) {
                            $args[] =  $vars[$name];
                        }elseif($param->isDefaultValueAvailable()){
                            $args[] = $param->getDefaultValue();
                        }else{
                            Debug::throw_exception(L('_PARAM_ERROR_').':'.$name);
                        }
                    }
                    $method->invokeArgs($module,$args);
                }else{
                    $method->invoke($module);
                }
                // 后置操作
                if($class->hasMethod('_after_'.$action)) {
                    $after =   $class->getMethod('_after_'.$action);
                    if($after->isPublic()) {
                        $after->invoke($module);
                    }
                }
            }else{
                // 操作方法不是Public 抛出异常
                throw new ReflectionException();
            }
        } catch (ReflectionException $e) { 
            // 方法调用发生异常后 引导到__call方法处理
            $method = new ReflectionMethod($module,'__call');
            $method->invokeArgs($module,array($action,''));
        }
        return ;
    }

    /**
     * 运行应用实例 入口文件使用的快捷方法
     *
     * @return void
     */
    static public function run() {

        // 项目初始化标签
        Tag::mark('app_init');

        // 初始化
        App::init();

        // 项目开始标签
        Tag::mark('app_begin');

        // Session初始化
        Session::config(C('SESSION_OPTIONS'));

        // 项目session初始化
        Tag::mark('app_session_begin');

        // 记录应用初始化时间
        Debug::mark('initTime');

        // 抛出异常
        App::exec();

        // 项目结束标签
        Tag::mark('app_end');

        // 保存日志记录
        if(Config::get('LOG_RECORD')) {
            Log::save();
        }
    }
}