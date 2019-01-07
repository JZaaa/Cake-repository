<?php
/**
 * Created by PhpStorm.
 * User: jzaaa
 * Date: 2019/1/4
 * Time: 14:55
 */

namespace App\Command;


use App\Lib\Beanstalkd\BeanstalkdWorker;
use Cake\Console\Arguments;
use Cake\Console\Command;
use Cake\Console\ConsoleIo;

class BeanstalkdWorkerCommand extends Command
{
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $worker = new BeanstalkdWorker('192.168.114.134');

        $io->out('worker start');


        $worker->run();

    }

}
