# Cake-repository

CakePHP 3.x 的自用版仓库

## 说明

````
/Behavior  /* 自定义行为组件，放在 /src/Model/Behavior 下 */
/Component   /* 自定义组件，放在 /src/Controller 下 */
/Lib        /* 自定义类， 放在 /src/Lib 下 */
/docunment  /* 说明文档 */
````

## 组件

组件标注CakePHP 版本一般为最低支持的CakePHP版本

#### CakePHP 3.4
  
 - [Intl拓展的简单替代](./document/Intl.md)

#### CakePHP 3.6

 - [action级别的auth权限控制组件](./document/AuthRuleComponent.md)
 
 - ~~[不启用url rewrite时，Auth权限控制组件](./document/SimpleAuthComponent.md)~~(已弃用)

 - [基于 easyWeChat 3.x 的 CakePHP 3.x 微信组件](./document/WeChatComponent.md)
 
 - [上传行为类，用于对本地文件上传的数据进行格式化](./Behavior/UploadBehavior.php)

 
## 封装类

未标注一般为通用类，可用于任意``CakePHP3.x``版本

 - [中文UTF-8转ASCII码](./document/Spliter.md) - 可用于实现全文检索
 - [CakePHP Beanstalkd 队列使用示例](./document/Beanstalkd.md)
