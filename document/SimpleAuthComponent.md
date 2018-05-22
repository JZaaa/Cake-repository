# SimpleAuthComponent 权限控制组件

该组件基于CakePHP 3.6 Auth组件修改，用于在无法使用Url Rewrite情况下原Auth组件的替代

## 使用

**组件引入**

复制 `SimpleAuthComponent.php` 文件至CakePHP项目 `src/Controller/Component` 下

**引入组件**

```` php
// AppController.php
public function initialize()
{
    parent::initialize();
    $this->loadComponent('SimpleAuth', $options);

}

````

**$options 配置项说明(可参考Auth配置)**


```` php
    [
         authenticate' => [
            'Form' => [
               'userModel' => 'Admin.Users'
            ]
         ],
         'loginAction' => [
             'controller' => 'Users',
             'action' => 'login',
             'plugin' => 'Admin'
         ],
         'loginRedirect' => [
              'controller' => 'Welcome',
              'action' => 'index',
              'plugin' => 'Admin'
         ],
         'sessionKey' => 'Admin.Users'
    ]

````
配置属性与 [CakePHP-Auth组件](https://book.cakephp.org/3.0/en/controllers/components/authentication.html)基本相同，仅替换了`storage`的操作，配置时请删除 'storage' 配置项，并用 ``'sessionKey' 代替(sessionKey为储存关键字)``



**其他方法**
具体请参照原 [CakePHP-Auth组件](https://book.cakephp.org/3.0/en/controllers/components/authentication.html) 文档
```` php
$this->SimpleAuth->allow()
$this->SimpleAuth->logout()
$this->SimpleAuth->setUser()
````

