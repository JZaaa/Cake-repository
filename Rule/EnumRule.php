<?php


namespace App\Model\Rule;


use Cake\Datasource\EntityInterface;
use Cake\Datasource\RulesChecker;
use Cake\Http\Exception\InternalErrorException;

/**
 * 枚举字段验证
 * $rules->add(new EnumRule(), 'showStatus',
        [
            'enum' => [0, 1], // 枚举类型
            'errorField' => 'show_status', // 错误字段
            'message' => '显示状态设置不正确', // 错误信息
            'requirePresence' => false // 验证方式， create: 创建验证(不能为空), true: 创建修改时验证(不能为空) ,false 【默认】 仅当不为空时验证
        ]
    );
 * Class EnumRule
 * @package App\Model\Rule
 */
class EnumRule extends RulesChecker
{
    /**
     * @var $__options
     */
    private $__options;
    
    private function isBlank($value)
    {
        return empty($value) && !is_numeric($value);
    }

    public function __invoke(EntityInterface $entity, array $options)
    {
        if (!(isset($options['enum']) && is_array($options['enum']))) {
            throw new InternalErrorException('验证内容不正确');
        }
        if (!(isset($options['errorField']) && is_string($options['errorField']))) {
            throw new InternalErrorException('验证字段不正确');
        }
        $this->__options = $options;
            // 是否必须
        $required = isset($options['requirePresence']) ? $options['requirePresence'] : false;

        $val = $entity->get($options['errorField']);

        if ($required === false) {
            if (!$this->isBlank($val)) {
                return $this->__checkField($val);
            }
            return true;
        } elseif ($required == 'create' && $entity->isNew()) {
            return $this->__checkField($val);
        } else {
            return $this->__checkField($val);
        }

    }


    private function __checkField($val)
    {
        $options = $this->__options;
        if (is_null($val)) {
            return false;
        }
        return in_array($val, $options['enum']);
    }

}
