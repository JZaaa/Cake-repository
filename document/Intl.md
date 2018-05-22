# [CakeDC Intl](https://github.com/CakeDC/Intl)

CakePHP 3.4+ Intl拓展的简单替代 

## 使用

### 安装

composer
````
composer require cakedc/intl --ignore-platform-reqs
````

### 在CakePHP中修改

在config/requirements.php中找到并**注释**掉

```` php
if (!extension_loaded('intl')) {
     trigger_error('You must enable the intl extension to use CakePHP.', E_USER_ERROR);
}
````

### 其他

在部分环境下，安装了该插件后使用CakePHP会报错误
```` php
PHP Fatal error: Cannot use IntlCalendar as IntlCalendar because the name is already in use in /vendor/cakedc/intl/src/IntlGregorianCalendar.php on line 14
````

**这时候你需要注释掉/vendor/cakedc/intl/src/IntlGregorianCalendar.php文件中 use IntlCalendar;**