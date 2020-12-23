<?php

namespace CorporateTrainingBundle\Biz\Post\Util;

class ChineseFirstCharter
{
    public function getFirstCharters($string)
    {
        $text = '';
        $string1 = iconv('UTF-8', 'GBK', $string);
        $string2 = iconv('GBk', 'UTF-8', $string1);
        if ($string2 == $string) {
            $string = $string1;
        }
        for ($i = 0; $i < strlen($string); ++$i) {
            $string1 = substr($string, $i, 1);
            $asc = ord($string1);
            if ($asc > 160) {
                $string2 = substr($string, $i++, 2);
                $text .= $this->getFirstCharter($string2);
            } else {
                $text .= $string1;
            }
            if (mb_strlen($text, 'utf-8') >= 30) {
                return $text;
            }
        }

        return $text;
    }

    protected function getFirstCharter($str)
    {
        if (empty($str)) {
            return '';
        }
        $char = ord($str[0]);
        if (($char >= ord('A') && $char <= ord('z')) || ($char >= ord('0') && $char <= ord('9'))) {
            return strtoupper($str[0]);
        }
        $asc = ord($str[0]) * 256 + ord($str[1]) - 65536;

        return $this->getCharterByASCII($asc);
    }

    protected function getCharterByASCII($asc)
    {
        $charterArr = array(
            'A' => array(-20319, -20284),
            'B' => array(-20283, -19776),
            'C' => array(-19775, -19219),
            'D' => array(-19218, -18711),
            'E' => array(-18710, -18527),
            'F' => array(-18526, -18240),
            'G' => array(-18239, -17923),
            'H' => array(-17922, -17418),
            'J' => array(-17417, -16475),
            'K' => array(-16474, -16213),
            'L' => array(-16212, -15641),
            'M' => array(-15640, -15166),
            'N' => array(-15165, -14923),
            'O' => array(-14922, -14915),
            'P' => array(-14914, -14631),
            'Q' => array(-14630, -14150),
            'R' => array(-14149, -14091),
            'S' => array(-14090, -13319),
            'T' => array(-13318, -12839),
            'W' => array(-12838, -12557),
            'X' => array(-12556, -11848),
            'Y' => array(-11847, -11056),
            'Z' => array(-11055, -10247),
        );
        foreach ($charterArr as $key => $item) {
            if ($asc >= $item[0] && $asc <= $item[1]) {
                return $key;
            }
        }

        return $this->getCharterByASCII(-rand(11055, 20319));
    }
}
