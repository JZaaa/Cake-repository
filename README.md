# Cake-repository

CakePHP 3.x 自用相关组件

## 说明

````
/Behavior  /* 自定义行为组件，放在 /src/Model/Behavior 下 */
/Rule /* 自定义验证组件，放在 /src/Model/Rule 下 */
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

 - [基于 easyWeChat 3.x 的 CakePHP 3.x 微信组件](./document/WeChatComponent.md)
 
 - [上传行为类，用于对本地文件上传的数据进行格式化](./Behavior/UploadBehavior.php)
 
 - [枚举字段验证](./Rule/EnumRule.php)
 
 - [文件上传组件](./Lib/Upload.php)
 - [验证码插件](https://github.com/JZaaa/CakeCaptcha)

 
## 封装类

未标注一般为通用类，可用于任意``CakePHP3.x``版本

 - [中文UTF-8转ASCII码](./document/Spliter.md) - 可用于实现全文检索
 - [CakePHP Beanstalkd 队列使用示例](./document/Beanstalkd.md)
 - [PHP计算解析库](https://github.com/JZaaa/string-calc) - 可用于excel公式计算
