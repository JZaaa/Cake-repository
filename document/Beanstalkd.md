# CakePHP Beanstalkd 队列

## Beanstalkd 下 ``Command``与``Controller`` 文件夹可移至``src``下测试

## 环境

- Apache/PHP 5.6 +
- Beanstalkd
- CakePHP 3.*
- pheanstalk 3.*


## 安装

### Beanstalkd 

````
yum install beanstalkd
````

#### 启动Beanstalkd

*由于 ``beanstalkd`` 默认无权限验证，所以在生产环境下服务与程序应在同一主机下，``beanstalkd``应侦听``127.0.0.1``*

````
beanstalkd -l 0.0.0.0 -p 11300 -b /var/lib/beanstalkd/binlog -F &

````
#### Beanstalkd参数
````
beanstalkd -h

Options:
-b 开启binlog，断电后重启会自动恢复任务。
-f MS fsync最多每MS毫秒
-F 从不fsync（默认）
-l ADDR侦听地址（默认为0.0.0.0）
-p 端口侦听端口（默认为11300）
-u USER成为用户和组
-z BYTES设置最大作业大小（以字节为单位）（默认值为65535）
-s BYTES设置每个wal文件的大小（默认为10485760） （将被舍入到512字节的倍数）
-c 压缩binlog（默认）
-n 不要压缩binlog
-v 显示版本信息
-V 增加冗长度
-h 帮助

````

### 安装 pheanstalk
``3.2版本会出现socket timeout错误，此类错误需要编码者重新实例化pheanstalk类，为避免出现与之相关的消息丢失问题，请务必使用3.1版本,4.*版本请自行测试``

````
composer require pda/pheanstalk:"3.1.*"
````

## 使用（测试）

利用CakePHP 编写 Command/Shell 文件, 本项目测试用例Command文件为`src/Command/BeanstalkdWorkerCommand.php`，

控制台输入
````
// linux下可以利用 Supervisord 等实现后台守护进程
bin/cake beanstalkd_worker
```` 
*约定*

创建任务时需要指定job名称,即参数传递
````
[
    'job' => 'xxx', // 必须
    ....
]
````

在`src/Lib/Beanstalkd/BeanstalkWorker.php`中需要自行编写工作函数(对应job名称)，如：
````
// set job

$beanstalk = new BeanstalkdJobs();
$data = [
    'job' => 'send_email', // job名称
     ... // 其他信息
];

$id = $beanstalk->set($data, [
    'delay' => 10
]);

if ($id === false) {
      throw new InternalErrorException($beanstalk->getError());
}

...

// in BeanstalkdWorker.php
// 添加对应job的方法

...

public function send_email($data)
{
    // $data 为创建的任务, $data = [ 'job' => 'send_email', ... ];
    
    // do something
    
    // success
    return true;
    
    // error
    return false;
} 

 
````




