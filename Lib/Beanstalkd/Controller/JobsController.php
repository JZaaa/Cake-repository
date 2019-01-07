<?php
/**
 * Created by PhpStorm.
 * User: jzaaa
 * Date: 2019/1/4
 * Time: 14:28
 */

namespace App\Controller;


use App\Lib\Beanstalkd\BeanstalkdJobs;
use Cake\Http\Exception\InternalErrorException;
use Cake\Log\Log;

class JobsController extends AppController
{

    public function initialize()
    {
        $this->autoRender = false;
    }

    protected function getBeanstalk()
    {
        return new BeanstalkdJobs('192.168.114.134');
    }

    public function index()
    {
        $beanstalk = $this->getBeanstalk();

        $client = $beanstalk->getClient();

        debug($client->stats());
    }

    public function product()
    {
        $beanstalk = $this->getBeanstalk();

        $data = [
            'job' => 'send_email',
            'subject' => 'My Test Subject',
            'body' => "A body",
            'to_target' => 'james@jmits.com.au',
            'more_stuff' => '... Limited by your imagination & requirements',
            'time' => date('Y-m-d H:i:s')
        ];

        $id = $beanstalk->set($data, [
            'delay' => 10
        ]);
        if ($id === false) {
            throw new InternalErrorException($beanstalk->getError());
        } else {
            echo '任务添加成功' . PHP_EOL;
            echo '任务ID:' . $id . PHP_EOL;
            echo '任务详情:' . PHP_EOL;
            debug($data);
        }
    }

}
