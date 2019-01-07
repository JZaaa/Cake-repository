<?php
/**
 * Created by PhpStorm.
 * User: jzaaa
 * Date: 2019/1/4
 * Time: 15:29
 */

namespace App\Lib\Beanstalkd;

use Cake\Log\Log;
use Pheanstalk\Pheanstalk;

class BeanstalkdWorker
{
    private $client;

    function __construct($host = '127.0.0.1', $port = '11300')
    {
        $this->client = new Pheanstalk($host, $port);
    }


    public function run($watch = 'pdefault')
    {
        $this->client
            ->watch($watch)
            ->ignore('default');

        $ctr = 0;

        Log::write('info', "run start");

        while ($job = $this->client->reserve()) {

            $job_data = $job->getData();
            Log::write('info', "Worker: 开始循环");
            $ctr++;

            $received = json_decode($job_data, true);
            $jobAction = $received['job'];
            $received['counter'] = $ctr;
            if ($this->$jobAction($received)) {
                $this->client->delete($job);
                Log::info("运行成功:");
            } else {
                $this->client->delete($job);
                Log::error("运行失败:");
            }
            Log::write('info', "Worker: 结束循环");
        }
    }


    public function send_email($data)
    {
        Log::write('info', "Worker: 发送邮件: {$data['time']}");
        return true;
    }



}
