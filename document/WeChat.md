# WeChat 组件
基于 [easyWeChat 3.x](https://github.com/overtrue/wechat) 的 CakePHP 3.x 微信组件

## 环境要求
[x] PHP >= 5.6

## 安装

````
composer require overtrue/wechat:~3.1 -vvv
````

## 组件引入

复制 `WeChatComponent.php` 文件至CakePHP项目 `src/Controller/Component` 下

## 引入组件

```` php
// AppController.php
public function initialize()
{
    parent::initialize();
    $this->loadComponent('WeChat', $options);

}

````

## 配置项说明

[更多请参考](https://www.easywechat.com/docs/3.x/configuration)

````
[
    /**
     * Debug 模式，bool 值：true/false
     *
     * 当值为 false 时，所有的日志都不会记录
     */
    'debug'  => true,

    /**
     * 账号基本信息，请从微信公众平台/开放平台获取
     */
    'app_id'  => 'your-app-id',         // AppID
    'secret'  => 'your-app-secret',     // AppSecret
    'token'   => 'your-token',          // Token
    'aes_key' => '',                    // EncodingAESKey，安全模式与兼容模式下请一定要填写！！！
	
	/**
     * 
     *
     * 更多请参考： https://www.easywechat.com/docs/3.x/configuration
     */    
];
````

## 使用

[easyWeChat文档](https://www.easywechat.com/docs/3.x/overview)

### 获取初始化
````
$server = $this->WeChat->getApp();
// or 
$server = $this->WeChat->__get('server');
````

更多示例完善中...