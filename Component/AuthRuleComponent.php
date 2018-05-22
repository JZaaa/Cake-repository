<?php
/**
 *
 * User: jzaaa
 * Date: 2018/5/18
 * Time: 10:32
 *
 */

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\Controller;
use Cake\Core\Exception\Exception;
use Cake\Event\Event;
use Cake\Event\EventDispatcherTrait;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\Http\Response;

/**
 * 对单个方法的权限判定
 *
 * 基本表:
 * CREATE TABLE `ad_auth_rules_bak` (
 *       `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
 *       `parent_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '父级id',
 *       `name` varchar(80) NOT NULL COMMENT '规则唯一标识',
 *       `title` varchar(20) NOT NULL COMMENT '规则中文名称',
 *       `condition` varchar(100) DEFAULT NULL COMMENT '规则表达式',
 *       PRIMARY KEY (`id`),
 *       UNIQUE KEY `name` (`name`)
 *   ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='规则表'
 *
 *
 *   * 基础配置:
 *  authModel[必须]  string  指定验证规则表的 Model, plugin为 Plugin.Model
 *  fields[必须]  array
 *      ---         'keywords' => 'name',  验证规则方法字段名,输入规则为 plugin/controller/action
 *      ---         'name' => 'title',     规则中文名字段名
 *      ---         'extend' => 'condition'  拓展字段名[暂无实际功能]
 *
 *  sessionKey[可选]  string  保存的session关键字, 默认为 AuthRules
 *  ids[必须]  array  获取规则的id数组集
 *  statusCode[可选]  int  无权限返回的header头, 默认为 405
 *  enable[可选]  bool  是否开启校验,默认true
 *
 *  使用方法:
 *              $this->loadComponent('AuthRules', [
 *                   'authModel' => 'Admin.AuthRules',
 *                   'fields' => [
 *                      'keywords' => 'name',
 *                      'extend' => 'condition',
 *                      'name' => 'title'
 *                   ],
 *                   'sessionKey' => 'Admin.AuthRules',
 *                   'ids' => [1,2,3], //此处请动态获取
 *                   'statusCode' => 405,
 *                   'enable' => true, //是否开启权限验证
 *               ]);
 *
 *
 *  设置验证白名单
 *          与Auth相同  在对应controller 的 initialize() 初始化函数 添加
 *           $this->AuthRules->allow()
 *
 */
class AuthRuleComponent extends Component
{

    use EventDispatcherTrait;
    use LocatorAwareTrait;


    /**
     * 基础配置:
     *  authModel[必须]  string  指定验证规则表的 Model, plugin为 Plugin.Model
     *  fields[必须]  array
     *      ---         'keywords' => 'name',  验证规则方法字段名,输入规则为 plugin/controller/action
     *      ---         'name' => 'title',     规则中文名字段名
     *      ---         'extend' => 'condition'  拓展字段名[暂无实际功能]
     *
     *  sessionKey[可选]  string  保存的session关键字, 默认为 AuthRules
     *  ids[必须]  array  获取规则的id数组集
     *  statusCode[可选]  int  无权限返回的header头, 默认为 405
     *  enable[可选]  bool  是否开启校验,默认true
     *
     * @var array
     */
    protected $_defaultConfig = [
        'authModel' => null,
        'fields' => null,
        'sessionKey' => null,
        'ids' => [],
        'statusCode' => null,
        'enable' => true
    ];



    /**
     * Request 对象
     *
     * @var \Cake\Http\ServerRequest
     */
    public $request;

    /**
     * Session 对象
     *
     * @var \Cake\Http\Session
     */
    public $session;

    /**
     * Response object
     *
     * @var \Cake\Http\Response
     */
    public $response;


    /**
     * 不校验action
     *
     * @var array
     */
    public $allowedActions = [];


    /**
     * 初始化
     * @param array $config
     */
    public function initialize(array $config)
    {
        $controller = $this->_registry->getController();
        $this->setEventManager($controller->getEventManager());

        $this->response =& $controller->response;
        $this->session = $controller->getRequest()->getSession();

    }


    /**
     * 配置数据，用$this->_config获取
     */
    protected function _setDefaults()
    {
        $defaults = [
            'authModel' => 'AuthRules',
            'fields' => [
                'keywords' => 'name',
                'name' => 'title',
                'extend' => 'condition'
            ],
            'sessionKey' => 'AuthRules',
            'ids' => [],
            'statusCode' => 405,
            'enable' => true
        ];
        $config = $this->getConfig();

        foreach ($config as $key => $value) {
            if ($value !== null) {
                unset($defaults[$key]);
            }
        }
        $this->setConfig($defaults);
    }

    /**
     * 获取并设置规则数据
     */
    protected function _setAuthRules()
    {
        $authRules = $this->_findAuthRules();
        $this->_writeAuthRules($authRules);
        return $authRules;
    }





    /**
     * 写入规则数据
     */
    protected function _writeAuthRules($authRules)
    {
        $this->session->write($this->_config['sessionKey'], $authRules);
    }

    /**
     * 读取规则数据
     * @return array|null|string
     */
    protected function _readAuthRules()
    {
        return $this->session->read($this->_config['sessionKey']);
    }

    /**
     * 删除规则数据
     */

    protected function _deleteAuthRules()
    {
        $this->session->delete($this->_config['sessionKey']);
    }


    /**
     * 查询规则
     * @return array
     */
    protected function _findAuthRules()
    {
        $ids = $this->_config['ids'];
        $rules = [];

        if (!is_array($ids)) {
            throw new Exception('AuthRulesComponent config.id must be Array');
        }

        if (empty($ids)) {
            return $rules;
        }

        $result = $this->_query($this->_config['ids'])->all();

        $config = $this->_config;

        foreach ($result as $item) {
            $key = strtolower($item[$config['fields']['keywords']]);
            $rules[$key] = [
                'extend' => $item[$config['fields']['extend']],
                'name' => $item[$config['fields']['name']]
            ];
        }

        return $rules;
    }


    /**
     * 数据库查询操作
     * @param $ids
     * @return \Cake\ORM\Query
     */
    protected function _query($ids)
    {
        $config = $this->_config;
        $table = $this->getTableLocator()->get($config['authModel']);

        $options = [
            'conditions' => [
                $table->aliasField('id in') => $ids
            ]
        ];

        return $table->find('all', $options);
    }



    /**
     * check 事件
     * @param Event $event
     */
    public function check(Event $event)
    {
        if ($this->_config['enable'] === false) {
            return null;
        }
        $controller = $event->getSubject();

        $action = strtolower($controller->getRequest()->getParam('action'));

        if (!$controller->isAction($action)) {
            return null;
        }
        $this->_setDefaults();

        if ($this->_isAllowed($controller)) {
            return null;
        }

        $rules = $this->_getRules();
        if ($rules !== false) {
            $result = $this->_checkAuthRules($controller, $rules);
            if ($result instanceof Response) {
                $event->stopPropagation();
            }
            return $result;
        }

        return $this->_unauthrules($controller);

    }


    /**
     * 返回检测结果
     * @param Controller $controller
     * @param $rules
     * @return bool|\Cake\Http\Response
     */
    protected function _checkAuthRules(Controller $controller, $rules)
    {
        if ($rules === false) {
            throw new Exception('can\'t read/write session.');
        }


        $request = $controller->getRequest();


        $route = strtolower($request->getParam('controller') . '/' . $request->getParam('action'));

        $plugin = strtolower($request->getParam('plugin'));

        if ($plugin !== false && $plugin !== null) {
            $route = $plugin . '/' . $route;
        }

        if (empty($rules) || !isset($rules[$route])) {
            return $this->_unauthrules($controller);
        }

        return true;
    }

    /**
     *
     * @return \Cake\Http\Response
     */
    protected function _unauthrules(Controller $controller)
    {
        $response = $this->response;
        return $response->withStatus($this->_config['statusCode']);
    }

    /**
     * startup 事件
     * Callback for Controller.startup event.
     *
     * @param \Cake\Event\Event $event Event instance.
     * @return \Cake\Http\Response|null
     */
    public function startup(Event $event)
    {
        return $this->check($event);
    }


    /**
     *
     * Events supported by this component.
     *
     * @return array
     */
    public function implementedEvents()
    {
        return [
            'Controller.initialize' => 'check',
            'Controller.startup' => 'startup',
        ];
    }


    /**
     *
     * 添加不验证规则
     * 支持字符串与数组
     *
     * ```
     * $this->AuthRules->allow('view');
     * $this->AuthRules->allow(['edit', 'add']);
     * ```
     * 或允许所有
     * ```
     * $this->AuthRules->allow();
     * ```
     *
     * @param string|array|null $actions Controller action 字符串或数组
     * @return void
     */
    public function allow($actions = null)
    {
        if ($actions === null) {
            $controller = $this->_registry->getController();
            $this->allowedActions = get_class_methods($controller);

            return;
        }
        $this->allowedActions = array_merge($this->allowedActions, (array)$actions);
    }


    /**
     * Checks whether current action is accessible without authentication.
     *
     * @param \Cake\Controller\Controller $controller A reference to the instantiating
     *   controller object
     * @return bool True if action is accessible without authentication else false
     */
    protected function _isAllowed(Controller $controller)
    {
        $action = strtolower($controller->getRequest()->getParam('action'));

        return in_array($action, array_map('strtolower', $this->allowedActions));
    }


    /**
     * 获取 rules 数据
     * @return array|null|string
     */
    public function rules()
    {
        $rules = $this->_readAuthRules();
        if ($rules === null) {
            return false;
        }

        return $rules;
    }


    /**
     * 删除rules 数据
     */
    public function destroy()
    {
        $this->_deleteAuthRules();
    }

    /**
     * 获取规则
     * @return array|null|string
     */
    protected function _getRules()
    {
        $rules = $this->rules();
        if ($rules !== false) {
            return $rules;
        }
        $this->_setAuthRules();
        $rules = $this->rules();
        if ($rules !== false) {
            return $rules;
        }
        throw new Exception('can\'t read/write session');
    }






}