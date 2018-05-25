# Spliter

UTF-8转ASCII整数类

## 使用

复制 `Spliter.php` 文件至CakePHP项目 `src/Lib` 下

### 引入

```` php
use App\Lib\Spliter;
````

### 转换

```` php
$spliter = new Spliter();

$ascii = $spliter->ord2UTF8('你好，world'); // 返回转换数据

$isLetter = $spliter->isLetter('a'); // 对单个字符判断是否属于白名单范围内的非拓展ASCII字符

$formatter = $spliter->formatterLetter('>'); // 对单个非拓展ASCII字符进行过滤并格式化返回

````

输出:
```` 
$ascii =>
    Array
    (
        [dict] => Array
            (
                [20320] => 你
                [22909] => 好
                [65292] => ，
            )
    
        [words] =>  20320 22909 65292 __world
    );
    
$isLetter => true;   

$formatter => |>|;   //对特殊字符前后添加|,防止出错

````

## 全文检索的应用示例

创建全文检索表

````sql
CREATE TABLE `ad_search_index` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `obj_type` varchar(20) NOT NULL COMMENT '模块类型',
  `obj_id` int(11) unsigned NOT NULL COMMENT '关联id',
  `title` text NOT NULL COMMENT '标题',
  `content` text NOT NULL COMMENT '内容',
  `params` text COMMENT '拓展字段',
  `status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '状态',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `object` (`obj_type`,`obj_id`),
  FULLTEXT KEY `content` (`title`,`content`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='全文索引'

````

对需要全文检索的内容转换为ASCII整数保存至 `title` 、`content` 字段中,并通过`obj_type`,`obj_id`字段进行关联。

**注意: 对于富文本内容请先使用 ``strip_tags()`` 函数对 `html` 标签进行过滤**

CakePHP 3.6 查询示例

```` php
    //...
    use App\Lib\Spliter;
    //...
    
    public function searchInfo($string, $condition = [])
    {
        $spliter = new Spliter();
        $string = $spliter->ord2UTF8($string)['words'];
        
        $query = $this->find()
            ->where([
                "MATCH(title, content) AGAINST(:search IN BOOLEAN MODE)"
            ])
            ->where($condition)
            ->bind(':search', $string, 'string')->all();

        return $query;
    }
````

