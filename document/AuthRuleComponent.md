# Cake-AuthRule 权限控制组件
基于CakePHP 3.6 基于 action 的 基本权限控制组件

## 开始使用


### 使用


**插件引入**

复制本项目 `Component` 文件夹至 `src/Controller` 下

**添加数据表**

`mysql`为例

```` sql
 CREATE TABLE `ad_auth_rules` (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `parent_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '父级id',
        `name` varchar(80) NOT NULL COMMENT '规则唯一标识',
        `title` varchar(20) NOT NULL COMMENT '规则中文名称',
       `condition` varchar(100) DEFAULT NULL COMMENT '规则表达式',
       PRIMARY KEY (`id`),
       UNIQUE KEY `name` (`name`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='规则表'

````

**引入组件**

```` php
// AppController.php
public function initialize()
{
    parent::initialize();
    $this->loadComponent('AuthRule', $options);

}

````

**$options 配置项说明**

```` php
    [
         'authModel' => 'Admin.AuthRules', // 指定表Model(*必填)
         /* 数据表字段关联配置 */
         'fields' => [
             'keywords' => 'name', // (*必填)验证规则方法字段名,输入规则为 plugin/controller/action
             'extend' => 'condition', // 拓展字段名(*必填，后期功能添加，暂无用)
             'name' => 'title', // (*必填)验证规则名
         ],
         'sessionKey' => 'AuthRules', // 保存session名，默认AuthRules
         'ids' => [], // 用户允许规则的id数组
         'statusCode' => 405, // 无权限返回状态码,默认405
         'enable' => true // 是否开启权限认证,默认 true
    ]

````

**方法**

设置验证白名单
```` php
/* 在具体 controller下 */

public function initialize()
{
    parent::initialize();
    $this->AuthRule->allow(); // 允许所有
    
    // $this->AuthRule->allow('index'); // 允许index action
    
    // $this->AuthRule->allow(['index', 'add']); // 允许多个action
}
````

销毁验证数据 / 清除验证缓存
```` php

$this->AuthRule->destroy();

````

## 与CakePHP Auth组件共同使用

请在`Auth`组件之后调用本组件，登录后将用户允许的规则id写入Auth.Session中，并赋值给$options['ids']

**注意：在 $this->Auth->logout() 之前需调用 $this->AuthRule->destroy() 清除验证缓存**

