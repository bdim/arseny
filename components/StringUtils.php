<?php
namespace app\components;

use yii\web\Controller;

class StringUtils
{
    const PADEG_IMEN = 1;
    const PADEG_RODIT = 10;
    const PADEG_VINIT = 20;
    const PHONE_CODE_LENGTH = 3; //дефолтный размер кода города
    const PHONE_ADDITIONS_SEPARATOR = "/"; // разделяет телефон и доп номер и т.п.

    protected static $stringTranslit = array(
        'а' => 'a',
        'б' => 'b',
        'в' => 'v',
        'г' => 'g',
        'д' => 'd',
        'е' => 'e',
        'ё' => 'yo',
        'ж' => 'zh',
        'з' => 'z',
        'и' => 'i',
        'й' => 'jj',
        'к' => 'k',
        'л' => 'l',
        'м' => 'm',
        'н' => 'n',
        'о' => 'o',
        'п' => 'p',
        'р' => 'r',
        'с' => 's',
        'т' => 't',
        'у' => 'u',
        'ф' => 'f',
        'х' => 'kh',
        'ц' => 'tc',
        'ч' => 'ch',
        'ш' => 'sh',
        'щ' => 'shch',
        'ъ' => '',
        'ы' => 'y',
        'ь' => '',
        'э' => 'eh',
        'ю' => 'iu',
        'я' => 'ia',
        'ç' => 'с',
        'ə' => 'e',
        'ğ' => 'g',
        'ı' => 'i',
        'ö' => 'o',
        'ş' => 's',
        'ü' => 'u',
        ' ' => '_',
        '-' => '-',
        '_' => '_'
    );

    protected static $urlTranslit = array(
        ' ' => '-',
        '-' => '-',
        '_' => '-',
        '(' => '-',
        ')' => '-',
        '[' => '-',
        ']' => '-',
        '=' => '-',
        '+' => '-',
        '%' => '-',
        '^' => '-',
        '$' => '-',
        '/' => '',
        '.' => '-'
    );

    public static function convertWinToUTF8($inStr)
    {
        return @iconv('windows-1251', 'utf-8', $inStr);
    }

    public static function recode($string)
    {
        return CHtml::encode(CHtml::decode($string));
    }

    public static function encode($string)
    {
        return CHtml::encode($string);
    }

    public static function escapeQuotes($string)
    {
        return strtr($string, array('"' => '\\"',
                                    "'" => "\\'"));
    }

    public static function decode($string, $filter = false)
    {
        if ($filter !== false) $string = str_replace($filter, "", $string);
        return CHtml::decode($string);
    }

    /**
     *
     * @param $number int числовое значение
     * @param $words   array массив окончаний Array("1 значение","2 значения","5 значений"[, "нет значений"]);
     *
     * @return string;
     * Формат фраз окончаний sprintf(String, $amount)
     */
    public static function pluralEnd($number, $words = array("",
        "",
        ""))
    {
        if (empty($words[3]))
            $words[3] = $words[2];

        return \Yii::$app->i18n->messageFormatter->format(
            '{n, plural, one{'.$words[0].'} few{'.$words[1].'} many{'.$words[2].'} other{'.$words[3].'}}',
            ['n' => $number],
            \Yii::$app->language
        );
    }

    public static function normalize($str)
    {
        return html_entity_decode(strval($str), ENT_QUOTES);
    }

    public static function safe($str, $normalize = true)
    {
        $str = str_replace('\\', '\\\\', $str);

        if ($normalize)
            $str = self::normalize($str);

        $str = htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
        $str = preg_replace('@\&amp;#\d+;@', '&mdash;', $str);

        return $str;
    }

    public static function safeAttributes($attr = array())
    {

        return array_map(array('StringUtils', 'safe'), $attr);
    }

    //переконвертировать строку или строковые значения массива(ов) содержащие символы в кодировке utf16 (\u0410\u043b...)
    //в нормальный UTF-8
    public static function utf16beToUTF8(&$target)
    {
        if (is_string($target))
            return preg_replace("/\\\\u([a-f0-9]{4})/e", "iconv('UCS-4LE','UTF-8',pack('V',hexdec('U$1')))", $target);
    }


    public static function txt($str)
    {
        $str = strval($str);
        $str = nl2br($str);

        $str = preg_replace('#&amp;\#[0-9]+;#', '- ', $str);

        return $str;
    }

    // для яндекса
    public static function yml($str)
    {
        $str = CHtml::encode(strip_tags($str));
        // Стандарт YML не допускает использования непечатаемых символов с ASCII-кодами от 0 до 31
        // (за исключением символов с кодами 9, 10, 13 — табуляция, перевод строки, возврат каретки).
        $str = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/iu', '', $str);
        $str = iconv('UTF-8', 'UTF-8//IGNORE', $str);
        return $str;
    }

    public static function printable($str)
    {
        $str = preg_replace('/[\x00-\x1F]/iu', '', $str);
        $str = iconv('UTF-8', 'UTF-8//IGNORE', $str);
        return $str;
    }

    public static function integer_unformat($str)
    {
        return preg_replace('/[^0-9]+/', '', $str);
    }

    public static function phone($phone)
    {
        return nl2br($phone);
    }

    public static function phoneLink($phone, $implodeTemplate = ",<br> ")
    {
        $phone = explode(',',$phone);

        $res = array();
        if (!empty($phone)){
            foreach ($phone as $_phone){
                $res[] = '<span onclick="location.href=$(this).data(\'href\')" data-href="tel:'.preg_replace('![^0-9+]+!', '', $_phone).'">'.$_phone.'</span>';
            }
        }
        return implode($implodeTemplate, $res);
    }


    /*
     * Красиво форматирует дату
     */
    public static function date($date_id, $pattern = 'd.m.Y H:i')
    {
        return date($pattern, DateUtils::getTime($date_id));
    }

    /**
     * Красиво форматирует дату
     * $date_id string unix, mysql, false
     * $date_type (full, short, false)
     * Возвращаемые форматы даты.
     *   10 Января 2010
     *   10 Января
     *   10.01.2010
     * $set_time bool = false
     * Формат времени
     *   11:14
     */
    public static function formatDate($date_id, $set_day = true, $set_time = false, $date_type = 'full')
    {
        $time_id = DateUtils::getTime($date_id);
        $months = DateUtils::months();
        $months_wo_days = DateUtils::months_wo_days();
        $date = date('d.m.Y', $time_id);

        if ($date_type !== false) {
            list($day, $month, $year) = @explode('.', $date);
            if ($set_day) {
                $date = $day . ' ' . $months[intval($month)] . ($date_type === 'full' ? ' ' . $year : '');
            } else {
                $date = $months_wo_days[intval($month)] . ($date_type === 'full' ? ' ' . $year : '');
            }
        }

        if ($set_time) $date .= ' ' . date('H:i', DateUtils::getTime($date_id));

        return $date;
    }

    /*
     * Красиво форматирует сумму
     */
    public static function summ($summ, $tag = 'b', $prefix = true)
    {
        $htmlOptions = array();
        if (is_array($tag)) {
            if ($tag['pClass'] && $summ > 0) $htmlOptions = array('class' => $tag['pClass']);
            if ($tag['nClass'] && $summ < 0) $htmlOptions = array('class' => $tag['nClass']);

            $tag = $tag['tag'];
        }
        $precision = ceil($summ) == $summ ? 0 : 2; //не показываем дробную часть, если она нулевая : 25.00 => 25
        $prefix = $summ > 0 && $prefix ? '+' : '';
        $print = $prefix . number_format($summ, $precision, '.', ' ');

        if ($tag) $print = CHtml::tag($tag, $htmlOptions, $print);
        return $print;
    }

    /*
     * Красиво форматирует EMail
     */
    public static function email($email, $options = array('target' => '_blank'))
    {
        return CHtml::link($email, 'mailto:' . $email, $options);
    }

    /*
     * Красиво форматирует сумму
     * @var $round - округлять до двух знаков после точки
     */
    public static function sum($sum, $opts = false, $cur = true, $nowrap = true, $round = false)
    {
        $opts = is_array($opts) ? $opts : array('',
            ' ',
            '',
            'подключен');

        $cur_array = !$cur ? false : ($cur === true ?
            array(Yii::$app->currencies->currentCurrencyData['full']
            ,
                Yii::$app->currencies->currentCurrencyData['full_2']
            ,
                Yii::$app->currencies->currentCurrencyData['full_3'])
            : (is_array($cur) ? $cur : array($cur,
                $cur,
                $cur))
        );
        $round_sum = number_format(floor($sum), 0, '', ' ');
        if ($round)
            $round_sum = number_format($sum, 2);

        $sum_str = $sum == 0 ? $opts[3] : $round_sum . $opts[1] . ($cur ? self::pluralEnd($sum, $cur_array) : '');
        $sum_str = ($nowrap ? '<span style="white-space:nowrap;">' : '') . $opts[0] . $sum_str . $opts[2] . ($nowrap ? '</span>' : '');
        return $sum_str;
    }

    /**
     * Выводит сумму прописью
     * @param $sum
     */
    public static function sumInWords($sum = 0, $cur = true)
    {
        $cur_array = !$cur ? false : ($cur === true ?
            array(Yii::$app->currencies->currentCurrencyData['full']
            ,
                Yii::$app->currencies->currentCurrencyData['full_2']
            ,
                Yii::$app->currencies->currentCurrencyData['full_3'])
            : (is_array($cur) ? $cur : array($cur,
                $cur,
                $cur))
        );
        $cop_array = array('копейка', 'копеек', 'копеек');

        $sum_arr = explode('.', $sum);
        $rub = !empty($sum_arr[0]) ? $sum_arr[0] : 0;
        $cop = !empty($sum_arr[1]) ? $sum_arr[1] : '00';
        $sum_str = self::inWords($rub) . ' ';

        $sum_str .= ' ' . $rub ? self::pluralEnd($rub, $cur_array) . ' ' . $cop . ' ' . self::pluralEnd($cop, $cop_array) : '';
        return $sum_str;
    }

    /*
     * Красиво форматирует интревал сумм
     * @param int - сумма от
     * @param int - сумма до
     * @param array - оформление ( слово_от, слово_до, до_первой_суммы, после_первой_сумы, перед_второй_суммой, после_второй_суммы, текст_если_суммы_нулевые )
     * @param array - склонения слова валюты
     */
    public static function sumInterval($sum_from, $sum_to, $opts = false, $cur = true, $nowrap = true)
    {
        $str = '';
        $opts = is_array($opts) ? $opts : array('от ',
            'до',
            '',
            '',
            '',
            ' ',
            '');
        $cur_array = !$cur ? false : ($cur === true ? array('рубль',
            'рубля',
            'рублей') : (is_array($cur) ? $cur : array($cur,
            $cur,
            $cur)));

        if ($sum_from == $sum_to) {
            $str = self::sum($sum_from, array($opts[4],
                $opts[5],
                '',
                $opts[6]), $cur, $nowrap);
        } elseif ($sum_from && !$sum_to) {
            $str = $opts[0] . self::sum($sum_from, array($opts[4],
                    $opts[5],
                    '',
                    $opts[6]), $cur, $nowrap);
        } elseif (!$sum_from && $sum_to) {
            $str = $opts[1] . '&nbsp;' . self::sum($sum_to, array($opts[4],
                    $opts[5],
                    '',
                    $opts[6]), false, $nowrap);
        } elseif ($sum_from && $sum_to) {
            $str = $opts[0] . self::sum($sum_from, array($opts[2],
                    $opts[3],
                    '',
                    $opts[6]), false, $nowrap) . '&nbsp;' . $opts[1] . '&nbsp;' . self::sum($sum_to, array($opts[4],
                    $opts[5],
                    '',
                    $opts[6]), $cur, $nowrap);
        }

        return $str;
    }

    //реализация number_format с $thousands_sep произвольной длины
    public static function number_format($number, $decimals = 0, $dec_point = '.', $thousands_sep = '&nbsp;')
    {
        if (strlen($thousands_sep) > 1) {
            return str_replace('#', $thousands_sep, number_format($number, $decimals, $dec_point, '#'));
        } else
            return number_format($number, $decimals, $dec_point, $thousands_sep);
    }

    public static function ageInterval($age_from, $age_to, $opts = false, $end = true)
    {
        $opts = is_array($opts) ? $opts : array('от ',
            'до',
            '',
            '',
            '',
            ' ',
            '');
        $words = !$end ? false : ($end === true ? array('год',
            'года',
            'лет') : (is_array($end) ? $end : array($end,
            $end,
            $end)));

        return self::sumInterval($age_from, $age_to, $opts, $words);
    }


    /*
     *
     */
    public static function num($amount)
    {
        return '<span style="white-space:nowrap;">' . number_format($amount, 0, '', ' ') . '</span>';
    }


    /*
     * Красиво форматирует пароль ))
     */
    public static function generate_pass($count = 6, $str = null)
    {
        if (is_null($str)) $str = '0,1,2,3,4,5,6,7,8,9';
        $arr = explode(',', preg_replace('#[\n\r\t\s]#', '', $str));
        $c = count($arr);

        $password = '';

        for ($i = 0; $i <= $count; $i++)
            $password .= $arr[mt_rand(0, $c - 1)];

        return $password;
    }

    public static function unserialize($serial_str)
    {
        $out = preg_replace('!s:(\d+):"(.*?)";!se', "'s:'.strlen('$2').':\"$2\";'", $serial_str);
        return unserialize($out);
    }

    static public function textCut($text, $cut = '<!--cut-->')
    {
        $str_cut = mb_strpos($text, $cut, 0, 'UTF-8');
        if ($str_cut !== false) $text = mb_substr($text, 0, $str_cut, 'UTF-8');

        return trim($text);
    }

    /**
     * Вырезание заданного количества символов с начала строки с учётом HTML тегов
     * @param string  $text   Исходный текст
     * @param integer $length Длина вырезаемого куска
     * @param string  $tail   Текст, дописываемый к вырезаемому куску, если он меньше всего текста
     * @return string Кусок текста заданной длины
     */
    static public function shrink($text, $length, $tail = '…')
    {
        if (mb_strlen($text, 'UTF-8') > $length) {
            $text = nl2br(trim(html_entity_decode(CHtml::encode($text))));
            $whiteSpacePosition = mb_strpos($text, ' ', $length, 'UTF-8') - 1;
            if ($whiteSpacePosition > 0) {
                $chars = count_chars(mb_substr($text, 0, ($whiteSpacePosition + 1)), 1);
                if (isset($chars[ord('<')]) && isset($chars[ord('>')]) && ($chars[ord('<')] > $chars[ord('>')])) {
                    $whiteSpacePosition = mb_strpos($text, '>', $whiteSpacePosition, 'UTF-8') - 1;
                }
                $text = mb_substr($text, 0, ($whiteSpacePosition + 1), 'UTF-8');
            }
            // close unclosed html tags
            if (preg_match_all('|<([a-zA-Z]+)|', $text, $aBuffer)) {
                if (!empty($aBuffer[1])) {
                    preg_match_all('|</([a-zA-Z]+)>|', $text, $aBuffer2);
                    if (count($aBuffer[1]) != count($aBuffer2[1])) {
                        foreach ($aBuffer[1] as $index => $tag) {
                            if (empty($aBuffer2[1][$index]) || $aBuffer2[1][$index] != $tag) {
                                $text .= '</' . $tag . '>';
                            }
                        }
                    }
                }
            }
            $text .= $tail;
        }
        return $text;
    }

    /**
     * truncateHtml can truncate a string up to a number of characters while preserving whole words and HTML tags
     *
     * @param string  $text         String to truncate.
     * @param integer $length       Length of returned string, including ellipsis.
     * @param string  $ending       Ending to be appended to the trimmed string.
     * @param boolean $exact        If false, $text will not be cut mid-word
     * @param boolean $considerHtml If true, HTML tags would be handled correctly
     *
     * @return string Trimmed string.
     */
    public static function truncateHtml($text, $length = 100, $ending = '...', $exact = false, $considerHtml = true)
    {
        if ($considerHtml) {
            // if the plain text is shorter than the maximum length, return the whole text
            if (strlen(preg_replace('/<.*?>/u', '', $text)) <= $length) {
                return $text;
            }
            //$text = nl2br(trim(html_entity_decode(CHtml::encode($text))));
            // splits all html-tags to scanable lines
            preg_match_all('/(<.+?>)?([^<>]*)/su', $text, $lines, PREG_SET_ORDER);
            $total_length = mb_strlen($ending, 'UTF-8');
            $open_tags = array();
            $truncate = '';
            foreach ($lines as $line_matchings) {
                // if there is any html-tag in this line, handle it and add it (uncounted) to the output
                if (!empty($line_matchings[1])) {
                    // if it's an "empty element" with or without xhtml-conform closing slash
                    if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/isu', $line_matchings[1])) {
                        // do nothing
                        // if tag is a closing tag
                    } else if (preg_match('/^<\s*\/([^\s]+?)\s*>$/su', $line_matchings[1], $tag_matchings)) {
                        // delete tag from $open_tags list
                        $pos = array_search($tag_matchings[1], $open_tags);
                        if ($pos !== false) {
                            unset($open_tags[$pos]);
                        }
                        // if tag is an opening tag
                    } else if (preg_match('/^<\s*([^\s>!]+).*?>$/su', $line_matchings[1], $tag_matchings)) {
                        // add tag to the beginning of $open_tags list
                        array_unshift($open_tags, mb_convert_case($tag_matchings[1], MB_CASE_LOWER, 'UTF-8'));
                    }
                    // add html-tag to $truncate'd text
                    $truncate .= $line_matchings[1];
                }
                // calculate the length of the plain text part of the line; handle entities as one character
                $content_length = strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/iu', ' ', $line_matchings[2]));
                if ($total_length + $content_length > $length) {
                    // the number of characters which are left
                    $left = $length - $total_length;
                    $entities_length = 0;
                    // search for html entities
                    if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/iu', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)) {
                        // calculate the real length of all entities in the legal range
                        foreach ($entities[0] as $entity) {
                            if ($entity[1] + 1 - $entities_length <= $left) {
                                $left--;
                                $entities_length += mb_strlen($entity[0], 'UTF-8');
                            } else {
                                // no more characters left
                                break;
                            }
                        }
                    }
                    $truncate .= mb_substr($line_matchings[2], 0, $left + $entities_length, 'UTF-8');
                    // maximum lenght is reached, so get off the loop
                    break;
                } else {
                    $truncate .= $line_matchings[2];
                    $total_length += $content_length;
                }
                // if the maximum length is reached, get off the loop
                if ($total_length >= $length) {
                    break;
                }
            }
        } else {
            if (strlen($text) <= $length) {
                return $text;
            } else {
                $truncate = mb_substr($text, 0, $length - mb_strlen($ending, 'UTF-8'), 'UTF-8');
            }
        }
        // if the words shouldn't be cut in the middle...
        if (!$exact) {
            // ...search the last occurance of a space...
            $spacepos = mb_strrpos($truncate, ' ', 0, 'UTF-8');
            if (isset($spacepos)) {
                // ...and cut the text in this position
                $truncate = mb_substr($truncate, 0, $spacepos, 'UTF-8');
            }
        }
        // add the defined ending to the text
        $truncate .= $ending;
        if ($considerHtml) {
            // close all unclosed html-tags
            foreach ($open_tags as $tag) {
                $truncate .= '</' . $tag . '>';
            }
        }
        return $truncate;
    }


    /*
     * Вырезает все лишнее из заданой строки. Используется для поиска дублей
     */
    public static function baseName($str = '', $min_len = 4)
    {
        if (empty($str))
            return '';
        $str = str_replace(array('"',
            '&laquo;',
            '&raquo;',
            '«',
            '»'), array('',
            '',
            '',
            '',
            ''), $str);
        $tmp = explode(' ', $str);

        $parts = array();
        foreach ($tmp as $part) {
            if (strlen($part) < ($min_len * 2))
                continue;
            $parts[] = trim($part);
        }
        if (count($parts))
            return implode(' ', $parts);
        return trim(implode(' ', $tmp));
    }


    public static function translit($str, $type = 'string')
    {
        $translitArray = self::$stringTranslit;
        if ($type == 'url') {
            $translitArray = array_merge($translitArray, self::$urlTranslit);
        }
        $result = mb_convert_case($str, MB_CASE_LOWER, "utf-8");
        $result = strtr($result, $translitArray);
        $result = preg_replace('/[^a-z0-9\-\_\.\/]/u', '', $result);

        return $result;
    }

    /**
     * Поддержка мультибайтных кодировок
     * @author bonerdelli
     */


    public static function mb_ucfirst($string, $encoding = null)
    {
        if (is_null($encoding)) $encoding = 'UTF-8';
        $strlen = mb_strlen($string, $encoding);
        $first = mb_substr($string, 0, 1, $encoding);
        $then = mb_substr($string, 1, $strlen - 1, $encoding);
        return mb_strtoupper($first, $encoding) . $then;
    }

    public static function mb_lcfirst($string, $encoding = null)
    {
        if (is_null($encoding)) $encoding = 'UTF-8';
        $strlen = mb_strlen($string, $encoding);
        $first = mb_substr($string, 0, 1, $encoding);
        $then = mb_substr($string, 1, $strlen - 1, $encoding);
        return mb_strtolower($first, $encoding) . $then;
    }

    public static function format_metric($num, $unit, $prec = 2, $fexp = null)
    {
        $prefs[0] = array(null,

            'м',
            // милли 10^-3
            'мк',
            // микро 10^-6
            'н',
            // нано  10^-9
            'п',
            // пико  10^-12
            'ф',
            // фемто 10^-15
            'а',
            // атто  10^-18
            'з',
            // зепто 10^-21
            'й',
            // йокто 10^-24

        );
        $prefs[1] = array(null,

            'к',
            // кило  10^3
            'М',
            // мега  10^6
            'Г',
            // гига  10^9
            'Т',
            // тера  10^12
            'П',
            // пета  10^15
            'Э',
            // экса  10^18
            'З',
            // зетта 10^21
            'Й',
            // йотта 10^24

        );

        if (is_string($num)) {
            $num = (double)preg_replace('/\s+/', '', $num);
        }

        $exp = (int)end(explode('e', sprintf('%.1e', $num)));

        if (is_null($fexp)) {
            $exp >= 0 ? $res_exp = $exp - $exp % 3
                : $res_exp = floor($exp / 3) * 3;
        } else {
            $fexp >= 0 ? $res_exp = $fexp - $fexp % 3
                : $res_exp = floor($fexp / 3) * 3;
        }

        if ($res_exp != 0) $res_exp > 0
            ? $unit = $prefs[1][$res_exp / 3] . $unit
            : $unit = $prefs[0][-$res_exp / 3] . $unit;

        return self::format_decimal(
                round($num / pow(10, $res_exp), $prec)
            ) . ' ' . $unit;

    }

    public static function format_decimal($num, $psign = false)
    {
        if ((float)$num == 0) return "0";  // избавляемся от -0
        if ($psign && $num > 0) $num = '+' . $num;

        return strtr($num, array(
            '-' => '&minus;',
            '.' => ','
        ));

    }

    public static function set_default_city($string, $defCity = '')
    {
        if (empty($defCity)) {
            $defCity = Yii::$app->params['siteOptions']['defaultCityName'];
        }
        $skip_labels = array(
            'г.',
            ' п.',
            'пос.',
            'поселок ',
            'город ',
            'екатеринбург',
            'свердловск',
            'пышма',
            'среднеуральск',
            'березовский',
            'берёзовский',
            'каменск',
            'уральский',
            'тагил',
            'челябинск',
            'первоуральск',
            'санкт',
            'петербург',
            'область',
            'москва',
            'баку',
            'азербайджан'
        );

        if (strlen($string) == strlen(str_replace($skip_labels, '', mb_strtolower($string, 'UTF-8')))) {
            $string = 'г. ' . $defCity . ', ' . $string;
        }

        return $string;

    }

    public static function time_elapsed($timeStart, $timeEnd = null, $needAdd1Day = true, $needAdd1Month = true)
    {
        $timeStart = new DateTime($timeStart);
        if (empty($timeStart)) {
            return '';
        }
        if (empty($timeEnd)) {
            $timeEnd = new DateTime();
        } else {
            $timeEnd = new DateTime($timeEnd);
            if ((int)$timeEnd->format('Y') < 1900) {
                $timeEnd = new DateTime();
            }
        }
        if ($needAdd1Day) {
            $timeEnd->add(new DateInterval('P1D'));
        }
        if ($needAdd1Month) {
            $timeEnd->add(new DateInterval('P1M'));
        }
        $interval = $timeEnd->diff($timeStart);
        if ($interval->days < 1) {
            return '';
        }
        $result = array();
        if ($interval->y > 0) {
            $result[] = self::pluralEnd($interval->y, array('%d год',
                '%d года',
                '%d лет'));
        }
        if ($interval->m > 0) {
            $result[] = self::pluralEnd($interval->m, array('%d месяц',
                '%d месяца',
                '%d месяцев'));
        }
        return implode(', ', $result);
    }

    public static function prepareForXml($string)
    {
        $string = preg_replace('/[\/\&\s+\(\)\[\]\;\\\]/iu', ' ', strip_tags(html_entity_decode($string)));
        $string = preg_replace('/\s+/iu', ' ', $string);
        $string = preg_replace('/[\x01-\x1f\"]/iu', '', $string);
        return $string; //preg_replace('/[^a-zёЁа-я0-9\,\.\-\s]/iu', '', $string);
    }

    public static function changeWrongKeyboard($wrongText)
    {
        $changeMap = array('q'  => 'й',
                           'й'  => 'q',
                           'w'  => 'ц',
                           'ц'  => 'w',
                           'e'  => 'у',
                           'у'  => 'e',
                           'r'  => 'к',
                           'к'  => 'r',
                           't'  => 'е',
                           'е'  => 't',
                           'y'  => 'н',
                           'н'  => 'y',
                           'u'  => 'г',
                           'г'  => 'u',
                           'i'  => 'ш',
                           'ш'  => 'i',
                           'o'  => 'щ',
                           'щ'  => 'o',
                           'p'  => 'з',
                           'з'  => 'p',
                           '['  => 'х',
                           '{'  => 'Х',
                           'х'  => ']',
                           '}'  => 'Ъ',
                           ']'  => 'ъ',
                           'ъ'  => ']',
                           'a'  => 'ф',
                           'ф'  => 'a',
                           's'  => 'ы',
                           'ы'  => 's',
                           'd'  => 'в',
                           'в'  => 'd',
                           'f'  => 'а',
                           'а'  => 'f',
                           'g'  => 'п',
                           'п'  => 'g',
                           'h'  => 'р',
                           'р'  => 'h',
                           'j'  => 'о',
                           'о'  => 'j',
                           'k'  => 'л',
                           'л'  => 'k',
                           'l'  => 'д',
                           'д'  => 'l',
                           ';'  => 'ж',
                           ':'  => 'Ж',
                           'ж'  => ';',
                           '"'  => 'Э',
                           '\'' => 'э',
                           'э'  => '\'',
                           'z'  => 'я',
                           'я'  => 'z',
                           'x'  => 'ч',
                           'ч'  => 'x',
                           'c'  => 'с',
                           'с'  => 'c',
                           'v'  => 'м',
                           'м'  => 'v',
                           'b'  => 'и',
                           'и'  => 'b',
                           'n'  => 'т',
                           'т'  => 'n',
                           'm'  => 'ь',
                           'ь'  => 'm',
                           ','  => 'б',
                           'б'  => ',',
                           '.'  => 'ю',
                           'ю'  => '.',
                           '`'  => 'ё',
                           'ё'  => '`',
                           '>'  => 'Ю',
                           '<'  => 'Б'

        );
        $value = strtr(mb_strtolower($wrongText, 'UTF-8'), $changeMap);
        return $value;
    }

    public static function trimAndStripTags($string, $allowableTags = null)
    {
        if (strpos($allowableTags, '<youTubeIFrame>') !== false) {
            $matches = array();
            preg_match_all('/(?:youtube\.com\/(?:[^\/]+\/[^\/]+\/|(?:v|e(?:mbed)?)\/|[^#]*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/i', $string, $matches);
            if (!empty($matches[1])) {
                $allowableTags = str_replace('<youTubeIFrame>', '<iframe>', $allowableTags);
            }
        }
        return trim(strip_tags($string, $allowableTags));
    }

    public static function tokenTruncate($string, $your_desired_width)
    {
        $parts = preg_split('/([\s\n\r]+)/iu', $string, null, PREG_SPLIT_DELIM_CAPTURE);
        $parts_count = count($parts);

        $length = 0;
        $last_part = 0;
        for (; $last_part < $parts_count; ++$last_part) {
            $length += mb_strlen($parts[$last_part], 'UTF-8');
            if ($length > $your_desired_width) {
                break;
            }
        }

        return implode(array_slice($parts, 0, $last_part));
    }

    // Очистка имени файла от запрещенных символов
    public static function cleaningFilename($filename)
    {
        $text = str_replace(array(','), array('_'), $filename);
        $text = preg_replace('/(["\'\\\?!:^~|@№$–=+*&%`,;\[\]<>()«»#\/]+)/u', '', $text);
        return $text;
    }

    /**
     * @param        $text
     * @param string $inCharset
     *
     * @return string
     */
    public static function getUnicodeEntities($text, $inCharset = 'UTF-8')
    {
        $text = preg_replace("/&#?[a-z0-9]{2,8};/i", " ", $text);
        $text = preg_replace('/<br(\s+)?\/?>/i', "\n", $text);
        if ($inCharset != 'UTF-8') {
            $text = iconv($inCharset, 'UTF-8//TRANSLIT', $text);
        }
        $text = self::utf8ToUnicode($text);
        return self::convertBRsToRtf(self::unicodeToEntitiesPreservingAscii($text));
    }


    /**
     * gets unicode for each character
     * @see http://www.randomchaos.com/documents/?source=php_and_unicode
     *
     * @return array
     */
    private static function utf8ToUnicode($str)
    {
        $unicode = array();
        $values = array();
        $lookingFor = 1;

        for ($i = 0; $i < strlen($str); $i++) {
            $thisValue = ord($str[$i]);

            if ($thisValue < 128) {
                $unicode[] = $thisValue;
            } else {
                if (count($values) == 0) {
                    $lookingFor = $thisValue < 224 ? 2 : 3;
                }

                $values[] = $thisValue;

                if (count($values) == $lookingFor) {
                    $number = $lookingFor == 3
                        ? (($values[0] % 16) * 4096) + (($values[1] % 64) * 64) + ($values[2] % 64)
                        : (($values[0] % 32) * 64) + ($values[1] % 64);

                    $unicode[] = $number;
                    $values = array();
                    $lookingFor = 1;
                }
            }
        }

        return $unicode;
    }


    /**
     * converts text with utf8 characters into rtf utf8 entites preserving ascii
     *
     * @param  string $unicode
     *
     * @return string
     */
    private static function unicodeToEntitiesPreservingAscii($unicode)
    {
        $entities = '';

        foreach ($unicode as $value) {
            if ($value != 65279) {
                $entities .= $value > 127
                    ? '\uc0{\u' . $value . '}'
                    : chr($value);
            }
        }

        return $entities;
    }

    public static function getBase64ImageString($pathToImage)
    {
        $imgBinary = fread(fopen($pathToImage, "r"), filesize($pathToImage));
        return 'data:' . image_type_to_mime_type(exif_imagetype($pathToImage)) . ';base64,' . base64_encode($imgBinary);
    }

    public static function convertToRtfParagraph($text, $bold = false, $underline = false)
    {
        return self::convertBRsToRtf('{\i0' . ($underline ? '\ul\ulc1' : '') . ($bold ? '\b' : '') . '\dbch\af12\alang1025\ab\rtlch \ltrch\loch\loch\f5' . self::getUnicodeEntities($text) . '\line }');
    }

    public static function convertToRtfHeader($text)
    {
        return '\pard \ltrpar\ql \li0\ri0\widctlpar\brdrb\brdrs\brdrw30\brsp20 \wrapdefault\faauto\rin0\lin0\itap0 {\rtlch\fcs1 \ab\af1\afs32 \ltrch\fcs0 \b\f1\fs32\lang1033\langfe1049\langnp1033\insrsid13587245 ' . self::getUnicodeEntities($text) . '}{\rtlch\fcs1 \ab\af39\afs32
\ltrch\fcs0 \b\f39\fs32\insrsid13587245
\par }\pard \ltrpar\ql \li0\ri0\widctlpar\wrapdefault\faauto\rin0\lin0\itap0 {\rtlch\fcs1 \af39\afs20 \ltrch\fcs0 \f39\fs20\insrsid13587245
\par {\pntext\pard\plain\ltrpar \rtlch\fcs1 \ab\af1\afs20 \ltrch\fcs0 \f3\fs20\lang1033\langfe1049\langnp1033\insrsid13587245 \loch\af3\dbch\af0\hich\f3 \'b7\tab}}';

        return '\pard \ltrpar\ql \li0\ri0\nowidctlpar\brdrb\brdrs\brdrw30\brsp20 \wrapdefault\faauto\rin0\lin0\itap0 {\rtlch\fcs1 \ab\af1\afs32
    \ltrch\fcs0 \b\f1\fs32\lang1033\langfe1049\langnp1033\insrsid10831240 ' . self::getUnicodeEntities($text) . '}{\rtlch\fcs1 \ab\af39\afs32 \ltrch\fcs0 \b\f39\fs32\insrsid10831240
    \par \ltrrow}';
    }


    public static function convertBRsToRtf($text)
    {
        return preg_replace('/\n/', ' \line ', $text);
    }

    public static function convertLogoStringToParts()
    {
        return explode('_', preg_replace(array('/\<span class\=\"trade\-orange\"\>/i',
            '/\<\/span\>/i'), '_', Yii::$app->params['mobileLogoString']));
    }

    public static function patternReplace($pattern, $params = array())
    {

        preg_match_all("/\{{([_a-zа-яёЁ0-9,#\s+]+?)\}}/iu", $pattern, $matches);
        if (!empty($matches[1]))
            foreach ($matches[1] as $match) {
                if (array_key_exists($match, $params)) {
                    $resultValue = $params[$match];
                }
                $pattern = str_replace('{{' . $match . '}}', $resultValue, $pattern);
            }
        return $pattern;
    }

    // заменить в тексте все найденные урлы ссылками
    // За регулярку спасибо http://xpoint.ru/know-how/PHP/GotovyieResheniya?14#AvtoopredelenieURLVStroke
    public static function replaceUrlToLink($text, $replacement = "<a href=\"\${0}\" target=\"_blank\">\${0}</a>")
    {
        $mask = '/\b(?:(?:[a-z]+:\/\/|www\.)(?:[a-z0-9-]+\.)*[a-z0-9]+|(?:[a-z0-9][a-z0-9-]*\.)+(?:ru|com|net|org|kg|az|uz|su)(?![\w-]))[a-z0-9-_\/\?=&+\.%]*[a-z0-9-_\/\?=&+]/i';
        return preg_replace($mask, $replacement, $text);
    }

    public static function clearWrongSpaces($text)
    {
        $text = htmlspecialchars_decode($text);
        return preg_replace('/\s+/iu', ' ', $text);
    }

    public static function clearText($text)
    {
        $text = htmlspecialchars_decode($text);
        $text = preg_replace('/[^\w+\s+\@\.\_\-]/iu', ' ', $text);
        return preg_replace('/\s+/iu', ' ', $text);
    }

    //наложить explode на индексированный массив
    public static function explodeIdxArray($separator = "###", $content = "", $idxArray = array())
    {
        $result = explode($separator, $content);
        if (empty($idxArray)) return $result; //индексный массив пуст - работаем как обычный explode
        if (empty($result)) return $result;

        $idxResult = array();
        foreach ($idxArray as $idx => $key) {
            $idxResult[$key] = $result[$idx];
        }

        return $idxResult;
    }

    // вставка разрыва строки <br> вместо пробела не позже, чем через $n символов
    public static function insertLineBreak($string, $n, $break = "<br>", $encoding = 'UTF-8')
    {
        $strlen = mb_strlen($string, $encoding);
        if ($strlen <= $n) return $string;

        $pos = $n - 1;
        while (mb_substr($string, $pos, 1, $encoding) != ' ' && $pos > 0) $pos--;

        if ($pos > 0) {
            $res = mb_substr($string, 0, $pos, $encoding) . $break . mb_substr($string, $pos + 1, $strlen, $encoding);
        } else {
            $res = $string;
        }
        return $res;
    }

    public static function repairUrlParams($url)
    {
        $url = preg_replace('/\?/', '', $url);
        return preg_replace('/\&/', '?', $url, 1);
    }

    public static function xml2array($xmlObject, $out = array())
    {
        foreach ((array)$xmlObject as $index => $node) {
            $out[$index] = (is_object($node)) ? xml2array($node) : $node;
        }

        return $out;
    }

    /**
     * @static Число прописью
     * @param $num int Целое число
     * @return string
     *             https://github.com/stden/yii-x/blob/master/RussianNumbers.php
     */
    public static function inWords($num)
    {
        // Все варианты написания чисел прописью от 0 до 999 скомпануем в один небольшой массив
        $m = [
            ['ноль'],
            ['-', 'один', 'два', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять'],
            ['десять', 'одиннадцать', 'двенадцать', 'тринадцать', 'четырнадцать', 'пятнадцать', 'шестнадцать', 'семнадцать', 'восемнадцать', 'девятнадцать'],
            ['-', '-', 'двадцать', 'тридцать', 'сорок', 'пятьдесят', 'шестьдесят', 'семьдесят', 'восемьдесят', 'девяносто'],
            ['-', 'сто', 'двести', 'триста', 'четыреста', 'пятьсот', 'шестьсот', 'семьсот', 'восемьсот', 'девятьсот'],
            ['-', 'одна', 'две']
        ];
        // Все варианты написания разрядов прописью скомпануем в один небольшой массив
        $r = [
            ['...ллион', '', 'а', 'ов'], // используется для всех неизвестно больших разрядов
            ['тысяч', 'а', 'и', ''],
            ['миллион', '', 'а', 'ов'],
            ['миллиард', '', 'а', 'ов'],
            ['триллион', '', 'а', 'ов'],
            ['квадриллион', '', 'а', 'ов'],
            ['квинтиллион', '', 'а', 'ов']
            // ,array(... список можно продолжить
        ];
        if ($num == 0) return $m[0][0]; # Если число ноль, сразу сообщить об этом и выйти
        $o = []; # Сюда записываем все получаемые результаты преобразования
        # Разложим исходное число на несколько трехзначных чисел и каждое полученное такое число обработаем отдельно
        foreach (array_reverse(str_split(str_pad($num, ceil(strlen($num) / 3) * 3, '0', STR_PAD_LEFT), 3)) as $k => $p) {
            $o[$k] = [];
            # Алгоритм, преобразующий трехзначное число в строку прописью
            foreach ($n = str_split($p) as $kk => $pp)
                if (!$pp) continue; else
                    switch ($kk) {
                        case 0:
                            $o[$k][] = $m[4][$pp];
                            break;
                        case 1:
                            if ($pp == 1) {
                                $o[$k][] = $m[2][$n[2]];
                                break 2;
                            } else$o[$k][] = $m[3][$pp];
                            break;
                        case 2:
                            if (($k == 1) && ($pp <= 2)) $o[$k][] = $m[5][$pp]; else$o[$k][] = $m[1][$pp];
                            break;
                    }
            $p *= 1;
            if (!$r[$k]) $r[$k] = reset($r);
            # Алгоритм, добавляющий разряд, учитывающий окончание руского языка
            if ($p && $k) switch (true) {
                case preg_match("/^[1]$|^\d*[0,2-9][1]$/", $p):
                    $o[$k][] = $r[$k][0] . $r[$k][1];
                    break;
                case preg_match("/^[2-4]$|\d*[0,2-9][2-4]$/", $p):
                    $o[$k][] = $r[$k][0] . $r[$k][2];
                    break;
                default:
                    $o[$k][] = $r[$k][0] . $r[$k][3];
                    break;
            }
            $o[$k] = implode(' ', $o[$k]);
        }
        return implode(' ', array_reverse($o));
    }
}
