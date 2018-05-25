<?php
namespace App\Lib;

/**
 * 
 *  0~127 为非中文
 *  ord()函数不能用于utf-8编码，ASCII编码0-127，128以上为多字节字符，它只是将字符串中的二进制字节解释为无符号整数。
 * 例如： ord('a') => 97, chr(97) => a ; ord('全') => 229 , chr(229) => 乱码;
 * 所以，对128以上编码需要额外处理
 *
 * 转换处理逻辑(utf-8)：
 * ASCII十进制    二进制              说明
 * 0-127        0xxxxxxx        返回单字节字符，直接返回
 * 128-191      10xxxxxx        
 * 192-223      110xxxxx        2字节
 * 224-239      1110xxxx        3字节,中文
 * 240-247      11110xxx        4字节
 * .......
 * 
 * 对于富文本编辑器等，请用 strip_tags() 过滤html字符后传入
 *
 * Class spliter
 *
 */

class Spliter
{

    /**
     * utf8分词转换acsii, 0-127 不转换直接返回
     * 
     * @param  string    $string 
     * @access public
     * @return array
     */
    public function ord2UTF8($string) {

        $string = trim($string); //过滤空格
        $length = strlen($string);
        $offset = 0;
        $prevlink = false; //连续字符标识
        $dict = []; //转换数组
        $words = ''; //返回查询字符串

        while ($offset < $length) {
            $current = $offset;
            $code = ord(substr($string, $offset,1));
            if ($code >= 128) {        
                $prevlink = false;
                if ($code < 224) $bytesnumber = 2;               
                else if ($code < 240) $bytesnumber = 3;       
                else if ($code < 248) $bytesnumber = 4;    
                $codetemp = $code - 192 - ($bytesnumber > 2 ? 32 : 0) - ($bytesnumber > 3 ? 16 : 0);

                for ($i = 2; $i <= $bytesnumber; $i++) {
                    $offset ++;
                    $code2 = ord(substr($string, $offset, 1)) - 128;        
                    $codetemp = $codetemp*64 + $code2;
                }
                $code = $codetemp;
                $dict[$code] = substr($string, $current, $bytesnumber);
                $words .= ' ' . $code;
            } else {
                $word = substr($string, $current, 1);
                //特殊字符白名单过滤,并格式化
                $word = $this->formatterLetter($word);
                if ($word !== false) {
                    if ($prevlink) {
                        $words .= $word;
                    } else {
                        $words .= ' ' . '__' . $word;
                    }
                    $prevlink = true;
                }
            }
            $offset += 1;
        }

        return [
          'dict' => $dict,
          'words' => $words
        ];

    }


    /**
     * 是否允许解析[acsii 0-127]
     * @param  string $letter
     * @access public
     * @return bool
     */
    public function isLetter($letter)
    {
        return ($this->formatterLetter($letter) !== false);
    }

    /**
     * 格式化
     * @param $letter
     * @param string $extend acsii 0-127的特殊字符
     * @return bool
     */
    public function formatterLetter($letter, $extend = '._/->:<?&')
    {
        $ord = ord($letter);
        if($ord >= ord('a') and $ord <= ord('z'))  return $letter;
        if($ord >= ord('A') and $ord <= ord('Z'))  return strtolower($letter);
        if($ord >= ord(0)   and $ord <= ord(9))    return $letter;
        if($letter and strpos($extend, $letter) !== false) {
            return '|' . $letter . '|';
        } 
        return false;
    }


}