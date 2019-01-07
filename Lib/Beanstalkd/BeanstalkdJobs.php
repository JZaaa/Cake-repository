<?php
/**
 * Created by PhpStorm.
 * User: jzaaa
 * Date: 2019/1/4
 * Time: 14:03
 */

namespace App\Lib\Beanstalkd;

use Pheanstalk\Pheanstalk;

class BeanstalkdJobs
{
    private $client;

    private $errorCode = 0;

    private $defaultPutConfig = [
        'tube' => 'pdefault', // 队列名称,请勿用 `default`
        'priority' => 1024, //优先级,0~4294967295, 数字越小优先级越高
        'delay' => 0, // 延迟(秒), 0为无延迟
        'ttr' => 60 // 任务超时时间(秒)
    ];



    function __construct($host = '127.0.0.1', $port = '11300')
    {
        $this->client = new Pheanstalk($host, $port);
    }


    public function getClient()
    {
        return $this->client;
    }

    /**
     * 创建任务
     * @param $data array|string 任务信息
     * @param array $config 配置
     * @return bool|int
     */
    function set($data, $config = [])
    {
        if ($this->client->getConnection()->isServiceListening()) {
            $config = $this->setPutConfig($config);
            $data = (is_array($data)) ? json_encode($data) : $data;
            return $this->client
                ->useTube($config['tube'])
                ->put($data, $config['priority'], $config['delay'], $config['ttr']);

        } else {
            $this->errorCode = 1;
            return false;
        }

    }

    /**
     * 创建任务配置项
     * @param $config
     * @return array
     */
    private function setPutConfig($config)
    {
        $newConfig = [];
        foreach ($this->defaultPutConfig as $key => $item) {
            $newConfig[$key] = isset($config[$key]) ? $config[$key] : $item;
        }

        return $newConfig;

    }


    /**
     * 获取错误信息
     * @return string
     */
    function getError()
    {
        $errorMaps = [
            -1 => '未知错误',
            0 => '成功',
            1 => 'beanstalkd服务不可用'
        ];

        return isset($errorMaps[$this->errorCode]) ? $errorMaps[$this->errorCode] : $errorMaps[-1];

    }

}
