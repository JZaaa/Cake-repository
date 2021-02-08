<?php
namespace App\Model\Behavior;


use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\ORM\Behavior;
use Cake\ORM\Query;
use Cake\Routing\Router;

/**
 * Class UploadBehavior
 *
 * 上传行为类
 * 在Model加入以下代码，uploadFields会在上传时对此字段进行格式化，去除域名信息并保存
        $this->addBehavior('Upload', [
            'uploadFields' => [
                'icon'
            ]
        ]);
 * $this->{Model}->find('uploadFormatter') 可对字段进行full域名格式化
 *
 *
 * 自定义库的配置
 * app.php中
 *
    'CustomLibConfig' => [
        // 上传行为类
        'UploadBehavior' => [
            'enabled' => true // 是否开启，若使用oss等请关闭此类
        ]
    ]
 *
 */
class UploadBehavior extends Behavior
{
    /**
     * Default config
     *
     * These are merged with user-provided config when the behavior is used.
     *
     * events - an event-name keyed array of which fields to update, and when, for a given event
     * possible values for when a field will be updated are "always", "new" or "existing", to set
     * the field value always, only when a new record or only when an existing record.
     *
     * refreshTimestamp - if true (the default) the timestamp used will be the current time when
     * the code is executed, to set to an explicit date time value - set refreshTimetamp to false
     * and call setTimestamp() on the behavior class before use.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'implementedFinders' => [
            'uploadFormatter' => 'findUploadFormatter'
        ],
        'implementedMethods' => [
            'uploadTouch' => 'touch',
        ],
        'events' => [
            'Model.beforeSave' => [],
        ],
        'uploadFields' => [], // 格式化字段
        'delimiter' => ',', // 多个信息的分隔符
        'enabled' => true // 是否开启，可在app.php中配置
    ];


    /**
     * Initialize hook
     *
     * If events are specified - do *not* merge them with existing events,
     * overwrite the events to listen on
     *
     * @param array $config The config for this behavior.
     * @return void
     */
    public function initialize(array $config)
    {
        $this->setConfig('enabled', Configure::read('CustomLibConfig.UploadBehavior.enabled') ? : false, false);
        if (isset($config['events'])) {
            $this->setConfig('events', $config['events'], false);
        }
    }

    /**
     * There is only one event handler, it can be configured to be called for any event
     *
     * @param \Cake\Event\Event $event Event instance.
     * @param \Cake\Datasource\EntityInterface $entity Entity instance.
     * @throws \UnexpectedValueException if a field's when value is misdefined
     * @return true Returns true irrespective of the behavior logic, the save will not be prevented.
     * @throws \UnexpectedValueException When the value for an event is not 'always', 'new' or 'existing'
     */
    public function handleEvent(Event $event, EntityInterface $entity)
    {
        $fields = $this->_config['uploadFields'];

        foreach ($fields as $field) {
            $this->_updateField($entity, $field);
        }

        return true;
    }

    /**
     * implementedEvents
     *
     * The implemented events of this behavior depend on configuration
     *
     * @return array
     */
    public function implementedEvents()
    {
        return array_fill_keys(array_keys($this->_config['events']), 'handleEvent');
    }

    /**
     * Touch an entity
     *
     * Bumps timestamp fields for an entity. For any fields configured to be updated
     * "always" or "existing", update the timestamp value. This method will overwrite
     * any pre-existing value.
     *
     * @param \Cake\Datasource\EntityInterface $entity Entity instance.
     * @param string $eventName Event name.
     * @return bool true if a field is updated, false if no action performed
     */
    public function touch(EntityInterface $entity, $eventName = 'Model.beforeSave')
    {
        $events = $this->_config['events'];
        if (empty($events[$eventName])) {
            return false;
        }

        $fields = $this->_config['uploadFields'];

        $return = false;
        foreach ($fields as $field) {
            $return = true;
            $this->_updateField($entity, $field);
        }

        return $return;

    }

    /**
     * Update a field, if it hasn't been updated already
     *
     * @param \Cake\Datasource\EntityInterface $entity Entity instance.
     * @param string $field Field name
     * @return void
     */
    protected function _updateField($entity, $field)
    {
        if (!$this->_config['enabled']) {
            return;
        }
        if (!$entity->isDirty($field)) {
            return;
        }

        $columnType = $this->getTable()->getSchema()->getColumnType($field);

        if (!$columnType) {
            return;
        }

        $original = $entity->get($field);

        if (empty($original)) {
            return;
        }

        $fullUrl = Router::fullBaseUrl();
        $base = Router::getRequest()->getAttribute('base') ? : Configure::read('App.base');

        $original = str_replace($fullUrl . $base, '', $original);

        $entity->set($field, $original);

    }

    /**
     * 用户基础 format
     * @param $results
     * @return mixed
     */
    public function _uploadFormatter($results)
    {
        if (!$this->_config['enabled']) {
            return $results;
        }
        $fields = $this->_config['uploadFields'];

        if (empty($fields)) {
            return $results;
        }
        $delimiter = $this->_config['delimiter'];

        return $results->map(function ($row) use ($fields, $delimiter) {
            $options = ['setter' => false, 'guard' => false];

            foreach ($fields as $field) {
                if (isset($row[$field]) && !empty($row[$field])) {
                    $arr = explode($delimiter, $row[$field]);
                    $newArr = [];
                    foreach ($arr as $item) {
                        $newArr[] = Router::url($item, true);
                    }
                    $row->set($field, implode($delimiter, $newArr), $options);
                }
            }

            $row->clean();
            return $row;
        });
    }

    /**
     * 格式化查询
     * $query->find('uploadFormatter');
     *
     *
     * @param \Cake\ORM\Query $query The original query to modify
     * @param array $options Options
     * @return \Cake\ORM\Query
     */
    public function findUploadFormatter(Query $query, array $options)
    {
        return $query
            ->formatResults([$this, '_uploadFormatter'], $query::PREPEND);
    }
}
