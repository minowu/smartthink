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

// 系统默认的核心行为扩展列表文件
return array(
    'app_init'      =>  array(
    ),
    'app_begin'     =>  array(
        'Think\\Behaviors\\ReadHtmlCache', // 读取静态缓存
        // 'CheckLang' // 读取Lang
    ),
    'app_auth' => array(
        // 'CheckAuth'
    ),
    'route_check'   =>  array(
        'Think\\Behaviors\\CheckRoute', // 路由检测
    ), 
    'app_end'       =>  array(),
    'path_info'     =>  array(),
    'action_begin'  =>  array(),
    'action_end'    =>  array(),
    'view_begin'    =>  array(),
    'view_template' =>  array(
        'Think\\Behaviors\\LocationTemplate', // 自动定位模板文件
    ),
    'view_parse'    =>  array(
        'Think\\Behaviors\\ParseTemplate', // 模板解析 支持PHP、内置模板引擎和第三方模板引擎
    ),
    'view_filter'   =>  array(
        'Think\\Behaviors\\ContentReplace', // 模板输出替换
        'Think\\Behaviors\\TokenBuild',   // 表单令牌
        'Think\\Behaviors\\WriteHtmlCache', // 写入静态缓存
        'Think\\Behaviors\\ShowRuntime', // 运行时间显示
    ),
    'view_end'      =>  array(
        'Think\\Behaviors\\ShowPageTrace', // 页面Trace显示
    ),
);