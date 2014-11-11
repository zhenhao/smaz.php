<?php
/**
 * SMAZ - compression for very small strings
 *
 * <pre>Smaz is a simple compression library suiBook for compressing very short
 * strings. General purpose compression libraries will build the state needed
 * for compressing data dynamically, in order to be able to compress every kind
 * of data. This is a very good idea, but not for a specific problem: compressing
 * small strings will not work.</pre>
 */

/**
 * Class Smaz.
 */
class Smaz
{
    /**
     * compress function.
     * @param $str
     * @return string
     */
    public static function encode($str)
    {
        $inLen = strlen($str);
        $inIdx = 0;
        $encodeBook = SmazConf::getEncodeBook();

        $output = '';
        $verbatim = '';
        while($inIdx < $inLen)
        {
            $encode = false;
            for($j = min(7, $inLen - $inIdx); $j > 0; $j--)
            {// 从7个字符开始查找encode book, book里最长串长度是7
                $code = isset($encodeBook[substr($str, $inIdx, $j)]) ? 
                    $encodeBook[substr($str, $inIdx, $j)] : null;
                if($code != null)
                {
                    if(strlen($verbatim))
                    {// 将字面值暂存区内容写到输出里并清空暂存区
                        $output .= self::flush_verbatim($verbatim);
                        $verbatim = '';
                    }
                    $output .= chr($code);
                    $inIdx += $j;
                    $encode = true;
                    break;
                }
            }
            if(!$encode)
            {// 如果没有从 encode book中找到，就放到字面值暂存区里
                $verbatim .= $str[$inIdx];
                $inIdx++;
                if(strlen($verbatim) == 255)
                {// 如果字面值暂存区达到255个字符，写到输出并清空
                    $output .= self::flush_verbatim($verbatim);
                    $verbatim = '';
                }
            }
        }
        if(strlen($verbatim))
        { //输入串已经遍历一遍，处理暂存区
            $output .= self::flush_verbatim($verbatim);
        }
        return $output;
    }

    /**
     * decompress funciton.
     * @param $str
     * @return string
     */
    public static function decode($str)
    {
        $decodeBook = SmazConf::getDecodeBook();
        $output = '';
        $i = 0;
        while($i < strlen($str))
        {
            $code = ord($str[$i]);
            if($code == 254)
            {// 读到特殊值254，直接拼下一个字面值
                $output .= $str[$i + 1];
                $i += 2;
            }
            else if($code == 255)
            {// 读到特殊值255，先读一个长度，再读字面值串
                $len = ord($str[$i + 1]);
                $output .= substr($str, $i + 2, $len);
                $i += 2 + $len;
            }
            else
            {// 从decode book 中查找数值对应的串
                $output .= $decodeBook[$code];
                $i++;
            }
        }
        return $output;
    }

    private static function flush_verbatim($verbatim)
    {
        $output = '';
        if(!strlen($verbatim))
        {
            return $output;
        }
        
        if(strlen($verbatim) > 1)
        { // 加特殊值255前缀，先存长度，再存串
            $output .= chr(255);
            $output .= chr(strlen($verbatim));
        }
        else
        { // 加特殊值254前缀
            $output .= chr(254);
        }
        $output .= $verbatim;
        return $output;
    }
}

/**
 * Class SmazConf.
 *
 * define smaz encode book and decode book.
 */
class SmazConf
{
    private static $_encodeBook = null;
    private static $_decodeBook = array(
        " ", "the", "e", "t", "a", "of", "o", "and", "i", "n", "s", "e ", "r", " th",
        " t", "in", "he", "th", "h", "he ", "to", "\r\n", "l", "s ", "d", " a", "an",
        "er", "c", " o", "d ", "on", " of", "re", "of ", "t ", ", ", "is", "u", "at",
        "   ", "n ", "or", "which", "f", "m", "as", "it", "that", "\n", "was", "en",
        "  ", " w", "es", " an", " i", "\r", "f ", "g", "p", "nd", " s", "nd ", "ed ",
        "w", "ed", "http://", "for", "te", "ing", "y ", "The", " c", "ti", "r ", "his",
        "st", " in", "ar", "nt", ",", " to", "y", "ng", " h", "with", "le", "al", "to ",
        "b", "ou", "be", "were", " b", "se", "o ", "ent", "ha", "ng ", "their", "\"",
        "hi", "from", " f", "in ", "de", "ion", "me", "v", ".", "ve", "all", "re ",
        "ri", "ro", "is ", "co", "f t", "are", "ea", ". ", "her", " m", "er ", " p",
        "es ", "by", "they", "di", "ra", "ic", "not", "s, ", "d t", "at ", "ce", "la",
        "h ", "ne", "as ", "tio", "on ", "n t", "io", "we", " a ", "om", ", a", "s o",
        "ur", "li", "ll", "ch", "had", "this", "e t", "g ", "e\r\n", " wh", "ere",
        " co", "e o", "a ", "us", " d", "ss", "\n\r\n", "\r\n\r", "=\"", " be", " e",
        "s a", "ma", "one", "t t", "or ", "but", "el", "so", "l ", "e s", "s,", "no",
        "ter", " wa", "iv", "ho", "e a", " r", "hat", "s t", "ns", "ch ", "wh", "tr",
        "ut", "/", "have", "ly ", "ta", " ha", " on", "tha", "-", " l", "ati", "en ",
        "pe", " re", "there", "ass", "si", " fo", "wa", "ec", "our", "who", "its", "z",
        "fo", "rs", ">", "ot", "un", "<", "im", "th ", "nc", "ate", "><", "ver", "ad",
        " we", "ly", "ee", " n", "id", " cl", "ac", "il", "</", "rt", " wi", "div",
        "e, ", " it", "whi", " ma", "ge", "x", "e c", "men", ".com"
    );

    public static function getEncodeBook()
    {
        if(!self::$_encodeBook)
        {
            self::$_encodeBook = array_flip(self::$_decodeBook);
        }
        return self::$_encodeBook;
    }

    public static function getDecodeBook()
    {
        return self::$_decodeBook;
    }
}
