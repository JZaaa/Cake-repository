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

### 函数方法
````
// 服务端验证
$this->WeChat->validator();

// 根据openId获取用户信息
$this->WeChat->getUser($openId);

// 获取用户列表
// @param null|string $nextOpenId 下一个OpenId
$this->WeChat->getUserList($nextOpenId);

// 查询菜单
// @param $type mixed 1为查询菜单，非1为自定义菜单
$this->WeChat->getMenus($type);

// 添加菜单
// @param $button 菜单配置
// @param array $matchRule 规则匹配
$this->WeChat->addMenu($button, $matchRule);

// Oauth 网页授权
$this->WeChat->oauth();

// 获取Oauth 授权结果用户信息 ,在oauth.callback对应的action使用
$this->WeChat->getOauthUser();

/**
* 发送模板消息
* @param $openID string
* @param $tplID string 模板id
* @param $data array 消息内容
* @param null $url 跳转链接
*/
$this->WeChat->sendTpl($openID, $tplID, $data, $url);



````

更多示例完善中...