<?php
/**
 * Created by PhpStorm.
 * User: jzaaa
 * Date: 2018/11/20
 * Time: 14:24
 */

namespace App\Controller\Component;


use Cake\Controller\Component;
use Cake\Core\Exception\Exception;
use EasyWeChat\Foundation\Application;

class WeChatComponent extends Component
{

    protected $_defaultConfig = [

        /**
         * Debug 模式，bool 值：true/false
         *
         * 当值为 false 时，所有的日志都不会记录
         */
        'debug' => true,

        /**
         * 账号基本信息，请从微信公众平台/开放平台获取
         */
        'app_id' => null,
        'secret' => null,
        'token' => null,
        'aes_key' => null, // 可选

        /**
         * 日志配置
         *
         * level: 日志级别, 可选为：
         *         debug/info/notice/warning/error/critical/alert/emergency
         * permission：日志文件权限(可选)，默认为null（若为null值,monolog会取0644）
         * file：日志文件位置(绝对路径!!!)，要求可写权限
         */
        'log' => [
            'level' => 'debug',
            'file' => LOGS . 'wechat.log',
        ],

    ];

    protected $_config = [];

    /**
     * @var Application
     */
    private static $_app;

    public function initialize(array $config = [])
    {
        $this->_checkOptions();
    }

    /**
     * 检查基本配置是否合法
     */
    protected function _checkOptions()
    {
        $required = [
            'app_id', 'secret', 'token'
        ];

        foreach ($required as $item) {
            if (empty($this->_config[$item])) {
                throw new Exception('微信 ' . $item . ' 未设置.');
            }
        }

    }


    /**
     * 获取初始化
     * @return Application
     */
    protected function _getApp()
    {
        if (!self::$_app instanceof Application) {
            self::$_app = new Application($this->_config);
        }

        return self::$_app;
    }


    public function getApp()
    {
        return $this->_getApp();
    }


    /**
     * magic accessor
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (parent::__get($name) === null) {
            if ($this->_getApp()->$name) {
                return $this->_getApp()->$name;
            } else {
                throw new Exception('未找到该方法');
            }
        } else {
            return parent::__get($name);
        }

    }


}