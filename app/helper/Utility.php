<?php

/**
 * Utility Helper Class
 * @author Amit
 */

namespace App\Helper;

use App\Helper\Date;
use Phalcon\Filter;

class Utility {

    const DEFAULT_PAGE_SIZE = 24;
    const DEFAULT_CURRENCY_CODE = 'USD';
    const DEFAULT_CURRENCY_SYMBOL = '$';

    /**
     * Print an Array in PRE format
     * @param array $array
     * @param boolean $exit
     * @param string $message
     */
    public static function printR($array, $exit = true, $message = '') {
        echo "<pre>";
        print_r($array);
        echo "</pre>";
        if ($exit) {
            exit($message);
        }
    }

    /**
     * Encrypt Password
     * Used method "md5"
     * @param type $string
     * @return mixed
     */
    public static function encryptPassword($string) {
        return md5($string);
    }

    /**
     * Generating Unique Id
     * @return mix
     */
    public static function getUid() {
        $urand = @fopen('/dev/urandom', 'rb');
        $pr_bits = false;
        if (is_resource($urand)) {
            $pr_bits .= @fread($urand, 16);
        }
        if (!$pr_bits) {
            $fp = @fopen('/dev/urandom', 'rb');
            if ($fp !== false) {
                $pr_bits .= @fread($fp, 16);
                @fclose($fp);
            } else {
                $pr_bits = "";
                for ($cnt = 0; $cnt < 16; $cnt ++) {
                    $pr_bits .= chr(mt_rand(0, 255));
                }
            }
        }
        $time_low = bin2hex(substr($pr_bits, 0, 4));
        $time_mid = bin2hex(substr($pr_bits, 4, 2));
        $time_hi_and_version = bin2hex(substr($pr_bits, 6, 2));
        $clock_seq_hi_and_reserved = bin2hex(substr($pr_bits, 8, 2));
        $node = bin2hex(substr($pr_bits, 10, 6));

        $time_hi_and_version = hexdec($time_hi_and_version);
        $time_hi_and_version = $time_hi_and_version >> 4;
        $time_hi_and_version = $time_hi_and_version | 0x4000;

        $clock_seq_hi_and_reserved = hexdec($clock_seq_hi_and_reserved);
        $clock_seq_hi_and_reserved = $clock_seq_hi_and_reserved >> 2;
        $clock_seq_hi_and_reserved = $clock_seq_hi_and_reserved | 0x8000;

        return strtolower(sprintf('%08s-%04s-%04x-%04x-%012s', $time_low, $time_mid, $time_hi_and_version, $clock_seq_hi_and_reserved, $node));
    }

    /**
     * Remove special character and spaces with $replace
     * Lowercasing the string
     * @param string $string
     * @return string
     */
    public static function slug($string) {
        $slug = strtolower($string);
        /* TODO: convert accent chars to the ascii basic char equivalent */
        $slug = preg_replace('#[\']#u', '', $slug);  // strip out chars that you don't want to consume space (ie strip apostrophe like words)
        $slug = preg_replace('#[^a-z0-9-]+#u', '-', $slug);  // only allow basic chars, letters or numbers
        $slug = trim($slug, '-');  // don't allow leading and trailing dashes

        return $slug;
    }

    /**
     * Currency formatter
     * Adding currency symbol at the beginning
     * @param type $number
     * @param type $currencyCode
     * @param type $stripCents 
     * @return mix
     */
    public static function formatCurrency($number, $currencyCode = '', $stripCents = true) {
        $decimal = (!$stripCents) ? 2: 0;
        
        $return = ($currencyCode == '' ? self::DEFAULT_CURRENCY_SYMBOL : $currencyCode) . self::formatNumber($number, $decimal);
        
        if ($stripCents) {
            $stripNumber = ($stripCents === -1 ? '[0-9]' : '0');
            $return = preg_replace("#\.$stripNumber$stripNumber#", '', $return);
        }
        
        return $return;
    }

    /**
     * Get Currency symbol
     * @param type $currencyCode
     * @return currency symbol
     */
    public static function Currencysymbol( $currencyCode = '') {
        
        return ($currencyCode == '' ? self::DEFAULT_CURRENCY_SYMBOL : $currencyCode);
    }

    /**
     * Format number
     * @param number $number
     * @return float
     */
    public static function formatNumber($number, $decimal = 0) {
        return number_format($number, $decimal);
    }

    /**
     * Encode String
     * @param string $string
     * @return string
     */
    public static function encodeString($string) {
        return htmlspecialchars($string, ENT_QUOTES);
    }

    /**
     * Decode string
     * @param string $string
     * @return string
     */
    public static function decodeString($string) {
        return htmlspecialchars_decode($string, ENT_QUOTES);
    }

    /**
     * Generate the days (1 - 31) for a month
     * Use for HTML Dropdown list
     * @return array
     */
    public static function getDays() {
        $days = array();
        for ($d = 1; $d <= 31; $d ++) :
            $days[$d] = $d;
        endfor;
        return $days;
    }

    /**
     * Week Days
     * Compatible with PHP date() function
     * 0 for Sunday, 6 for Saturday
     * @param int $wd
     * @param boolean $full
     * @return Ambigous <multitype:string , array>
     */
    public static function weekDays($wd = NULL, $full = TRUE) {
        $weekDays = array(
            1 => ($full ? 'Monday' : 'Mon'),
            2 => ($full ? 'Tuesday' : 'Tue'),
            3 => ($full ? 'Wednesday' : 'Wed'),
            4 => ($full ? 'Thursday' : 'Thu'),
            5 => ($full ? 'Friday' : 'Fri'),
            6 => ($full ? 'Saturday' : 'Sat'),
            0 => ($full ? 'Sunday' : 'Sun')
        );
        return $wd != NULL ? (isset($weekDays[$wd]) ? $weekDays[$wd] : '') : $weekDays;
    }

    /**
     * Generate the months list of an year
     * Use for HTML Dropdown list
     * @return array
     */
    public static function getMonths($includeMonthNumber = FALSE, $monthNumber = null) {
        $months = array(
            '1'  => ($includeMonthNumber ? self::padInt( 1 ) . ' ' : '') . self::encodeString('January'  ),
            '2'  => ($includeMonthNumber ? self::padInt( 2 ) . ' ' : '') . self::encodeString('February' ),
            '3'  => ($includeMonthNumber ? self::padInt( 3 ) . ' ' : '') . self::encodeString('March'    ),
            '4'  => ($includeMonthNumber ? self::padInt( 4 ) . ' ' : '') . self::encodeString('April'    ),
            '5'  => ($includeMonthNumber ? self::padInt( 5 ) . ' ' : '') . self::encodeString('May'      ),
            '6'  => ($includeMonthNumber ? self::padInt( 6 ) . ' ' : '') . self::encodeString('June'     ),
            '7'  => ($includeMonthNumber ? self::padInt( 7 ) . ' ' : '') . self::encodeString('July'     ),
            '8'  => ($includeMonthNumber ? self::padInt( 8 ) . ' ' : '') . self::encodeString('August'   ),
            '9'  => ($includeMonthNumber ? self::padInt( 9 ) . ' ' : '') . self::encodeString('September'),
            '10' => ($includeMonthNumber ? self::padInt( 10) . ' ' : '') . self::encodeString('October'  ),
            '11' => ($includeMonthNumber ? self::padInt( 11) . ' ' : '') . self::encodeString('November' ),
            '12' => ($includeMonthNumber ? self::padInt( 12) . ' ' : '') . self::encodeString('December' )
        );
        
        return $monthNumber ? (isset($months[$monthNumber]) ? $months[$monthNumber] : "Invalid Month") : $months;
    }

    /**
     * Generate a range of year
     */
    public static function getYears($htmlOptions = array()) {
        $years = array();
        $start = 1900;
        if (isset($htmlOptions['start'])) {
            $start = $htmlOptions['start'];
        }
        $end = Date::getCurrentYear();
        if (isset($htmlOptions['end'])) {
            $end = $htmlOptions['end'];
        }
        if (isset($htmlOptions['range'])) {
            if (is_array($htmlOptions['range'])) {
                $start = Date::getCurrentYear() - (isset($htmlOptions['range']['start']) ? $htmlOptions['range']['start'] : 0);
                $end = Date::getCurrentYear() + (isset($htmlOptions['range']['end']) ? $htmlOptions['range']['end'] : 0);
            } else {
                $start = Date::getCurrentYear() - $htmlOptions['range'];
                $end = Date::getCurrentYear() + $htmlOptions['range'];
            }
        }

        for ($y = $start; $y <= $end; $y ++) {
            $years[$y] = $y;
        }
        return $years;
    }

    /**
     * Padding integer with leading 0(zero)
     * @param int $int
     * @return string|int
     */
    public static function padInt($int) {
        return strlen($int) > 1 ? $int : '0' . $int;
    }

    /**
     * Display Decoreated Flash Message
     */
    public static function flashMessage() {
        $messages = \Phalcon\Di::getDefault()->get('flash')->getMessages();
        $messageTypeCss = array(
            'error' => 'danger',
            'success' => 'success',
            'notice' => 'info',
            'warning' => 'warning',
            'topbar' => 'topbar',
            'popup' => 'popup',
        );
        if ($messages && count($messages) > 0) {
            foreach ($messages as $messageType => $messageArray) {
                foreach ($messageArray as $message) {
                    switch ($messageType) {
                        case 'popup':
                            \Phalcon\Di::getDefault()->get('flash')->message('popup',$message);
                            break;

                        case 'topbar':
                            echo '<div class="message_bar_wrap general_message type2">
                                <div class="message_bar">
                                    <div class="message_con_wrap">
                                        <div class="message_bar_logo">
                                            '.\Phalcon\Tag::image(array("src" => "/img/svg/logo_blue_for_message_bar.svg")).'
                                        </div>
                                    <p class="message_bar_txt">'.$message.'</p>
                                    </div>
                                </div>
                                <div class="messagebar_close">'.\Phalcon\Tag::image(array("src" => "/img/login_pop_close.png")).'</div>
                            </div>
                            <script type="text/javascript">
                                $(document).ready(function(e){
                                    topbar_position();
                                    $(".message_bar_wrap.general_message").delay(2000).fadeOut(300);
                                });
                            </script>
                            ';
                            break;
                        
                        default:
                            echo "<div class=\"alert alert-" . (isset($messageTypeCss[$messageType]) ? $messageTypeCss[$messageType] : "popup") . " alert-dismissible\" role=\"alert\">";
                            echo $message;
                            echo "</div>";
                            break;
                    }
                }
            }
        }
    }

    /**
     * Replace part of card number with 'x'
     * @param int $cardNumber
     * @return string
     * @example '4040xxxxxxxx4040' replace 8 digit from the middle with 'x'
     */
    public static function encodeCardNubmer($cardNumber) {
        $cardNumberLength = strlen($cardNumber);
        $cardNumberXLength = $cardNumberLength - 8;
        $encodedCardNumber = substr($cardNumber, 0, 4);
        for ($i = 1; $i <= $cardNumberXLength; $i++) {
            $encodedCardNumber .= 'x';
        }
        $encodedCardNumber .= substr($cardNumber, -4);
        return $encodedCardNumber;
    }

    /**
     * Convert Camel Case String into Snake Case String
     * @example CamelCase/camelCase/CAMELCase/camelCASE/CamelCASE to camel_case 
     * @return string
     */
    public static function convertCamelCaseToSnakeCase($camelCase) {
        return strtolower(preg_replace(['/([a-z\d])([A-Z])/', '/([^_])([A-Z][a-z])/'], '$1_$2', $camelCase));
    }

    /**
     * get arry of alphabetical range
     * @return array
     */
    public static function getAZRange($step = 1,$separator = '-') {
        
        $arrReturn = array();
        $arrAlpha = range('A', 'Z');
        $key = 0;
        $i = 0;
        if(is_numeric($step)) {
            while(count($arrAlpha) > $key) {
                $arrReturn[$i]['start'] = $arrAlpha[$key];
                $arrReturn[$i]['end'] = isset($arrAlpha[$key+$step])?$arrAlpha[$key+$step]:'Z';
                $arrReturn[$i]['strRange'] = $arrReturn[$i]['start'].$separator.$arrReturn[$i]['end'];
                $key += $step+1;
                $i++;
            }
        }
        return $arrReturn;
    }
    
    /*
     * Cleans HTML tags and characters
     * @return string
     */
    public static function cleanHtml($content){
        $content = strip_tags($content);
        $content = html_entity_decode($content, ENT_QUOTES);
        $content = preg_replace("/([\s+])(\n\r|\t)/", ' ', $content); //converts all new lines, tabs to single space
        return $content;
    }

    /**
     * get truncate string
     * @return string
     */
    public static function truncateString($str, $chars = "50", $replacement="...", $cleanHtml = TRUE) {
        if($cleanHtml) $str = self::cleanHtml($str);
        if($chars > mb_strlen($str)) return $str;
        $str = mb_substr($str, 0, $chars);
        return ($str . $replacement);
    }

    public static function formatOrdinal($int) {
        
        $suffix = ['th','st','nd','rd','th','th','th','th','th','th'];
        
        if ((($int % 100) >= 11) && (($int % 100) <= 13)) return $int. 'th';    // exceptions for x11th - x13th
        else return $int. $suffix[$int % 10];
    }

    public static function fileExists($path) {
        $fullPath = ROOTPATH . '/public' . $path;
        return $path && file_exists($fullPath); // True && False => False, True && True => True
    }

    /**
     * Checks if is an array
     * @param array
     * @return Boolean True/ False
     */
    public static function arrayAccessible($value)
    {
        return is_array($value) || $value instanceof ArrayAccess;
    }
    
    /**
     * Checks if key exists in an array
     * @param array, key to be found
     * @return Boolean True/ False
     */
    public static function keyExists($array, $key)
    {
        if ($array instanceof ArrayAccess) {
            return $array->offsetExists($key);
        } else if (is_object($array)) {
            return isset($array->$key);
        } else {
            return array_key_exists($key, $array);
        }
    }
    
    /**
     * Checks if method exists in an object
     * @param array, the object
     * @param key, method to be found
     * @param onlyGetters, only allow "get..." methods so we don't let all functions get called
     * @return Boolean True/ False
     */
    protected static function _arrayGetDotMethodExists($array, $key, $onlyGetters = true)
    {
        if (is_object($array) && (!$onlyGetters || \Phalcon\Text::startsWith($key, 'get'))) {
            return method_exists($array, $key);
        }
    }
    
    /**
     * Returns the value from array matching with key or object
     * @param Array containing data to be found, String as key to find value
     * @return data
     */
    public static function arrayGetDot($array, $key, $default = null)
    {
        if (is_null($key)) return $array;
        if (self::keyExists($array, $key)) {
            return $array[$key];
        }
        if (filter_var($key, FILTER_VALIDATE_URL)) {
            return $key;
        }else{
            foreach (explode('.', $key) as $segment) {
                if (self::arrayAccessible($array) && self::keyExists($array, $segment)) {
                    $array = $array[$segment];
                } else if (is_object($array) && self::keyExists($array, $segment)) {
                    $array = $array->$segment;
                } else if (is_object($array) && self::_arrayGetDotMethodExists($array, $segment)) {
                    $array = $array->$segment();
                } else {
                    return $default ? $default : $key;
                }
            }
        }
        return $array;
    }
    
    /**
     * sets array value based on $key dot notation, so if $key = 'my.nested.array'; then $array will be ['my' => ['nested' => ['array' => $value]]]
     * 
     * @param Array $array the array reference to set
     * @param String $key the key to use for $array
     * @param mixed $value the value set
     * @param mixed $empty_value_for_create_array should be either '' or *
     *      allows appending to array if the $key part === this value. my..key will be ['my' => [...,n => ['key' => $value]]]
     *      if you change $empty_value_for_create_array = '*'; then you would do my.*.key instead (allows for empty string as the explicit hash value if you need it)
     * 
     * @return data the reference to the value that was set
     */
    public static function &arraySetDot(&$array, $key, $value, $empty_value_for_create_array = '')
    {
        if (!is_array($array)) $array = [];
        
        if ($key === null || $key === $empty_value_for_create_array) return $array = [$value];    // if its null then just do 1 element with $value
        
        foreach (explode('.', $key) as $segment) {
            
            // if its blank then just add a new element to the array, this will allow doing "my..key" which will return ['my' => [...,n => ['key' => $value]]]
            if ($segment === $empty_value_for_create_array) {
                $tmp = [];  // create an element to reference
                $array[] = &$tmp;   // add it to the array
                $array = &$tmp;     // point the $array on that new sub-element
                unset($tmp);        // remove the $tmp reference so if we excecute this block again we won't end setting that to this reference
            } else {
                if (!array_key_exists($segment, $array) || !is_array($array[$segment])) {
                    $array[$segment] = [];
                }
                $array = &$array[$segment];
            }
        }
        
        $array = $value;
        
        return $array;
    }
    
    /**
     * Inserts a new key/value before the key in the array.
     *
     * @param $key The key to insert before.
     * @param $array An array to insert in to.
     * @param $new_key The key to insert.
     * @param $new_value An value to insert.
     */
    public static function arrayInsertBefore($key, array &$array, $new_key, $new_value) {
        if (array_key_exists($key, $array)) {
            $new = array();
            foreach ($array as $k => $value) {
                if ($k == $key) {
                    $new[$new_key] = $new_value;
                }
                $new[$k] = $value;
            }
            $array = $new;
        }
    }

    /**
     * Inserts a new key/value after the key in the array.
     *
     * @param $key The key to insert after.
     * @param $array An array to insert in to.
     * @param $new_key The key to insert.
     * @param $new_value An value to insert.
     */
    public static function arrayInsertAfter($key, array &$array, $new_key, $new_value) {
        if (array_key_exists($key, $array)) {
            $new = array();
            foreach ($array as $k => $value) {
                $new[$k] = $value;
                if ($k == $key) {
                    $new[$new_key] = $new_value;
                }
            }
            $array = $new;
        }
    }

    /**
     * get bot name
     *
     * @param $array An arry of matching bot.
     * @param $string user agent.
     * @return botname or false.
     */
    public static function getBotName($matchBots = [], $userAgent = null) {
        $crawlerDetect = new \Jaybizzle\CrawlerDetect\CrawlerDetect();
        if($crawlerDetect->isCrawler($userAgent)) {
            $botName = $crawlerDetect->getMatches();
            if ($matchBots) {
                if (!is_array($matchBots)) $matchBots = [$matchBots];
                return (in_array(strtolower($botName), array_map('strtolower', $matchBots))) ? $botName : false;
            }
            return $botName;
        }
        return false;
    }

    /**
     * Set Cookie
     *
     * @param $string cookie Name.
     * @param $mixed(string|array) cookie value, we can pass value as array or string.
     */
    public static function setCookie($name = NULL,$value = NULL, $time = NULL, $merge = true) {
        if($name && $value) {
            $cookie = \Phalcon\Di::getDefault()->get('cookies');
            // If value is in array and merge is true then we will check cookie is exist or not and if exist then we merge data in this cookie
            if(is_array($value) && $merge) {
                if($cookie->has($name)) {
                    $cookieValue = $cookie->get($name)->getValue();
                    $cookieArr = json_decode($cookieValue,true);
                    if(is_array($cookieArr) && count($cookieArr) > 0) {
                        $value = array_merge($cookieArr,$value);
                    }
                }
            }

            $value = is_array($value)?json_encode($value):$value;
            $cookie->set($name,$value,$time?:time() + 365 * 86400);
            $cookie->send();
        }

    }

    /**
     * get Cookie
     *
     * @param string cookie Name.
     * @param string If cookie value set as array and we want to value from that perticular key
     */
    public static function getCookie($name = NULL, $keyName = NULL) {
        $cookieValue = NULL;
        if($name) {
            $cookie = \Phalcon\Di::getDefault()->get('cookies');
            if($cookie->has($name)) {
                $cookieValue = $cookie->get($name)->getValue();
                if($keyName) {
                    $cookieValue = ( ($decodeValue = json_decode($cookieValue,true)) && is_array($decodeValue) && isset($decodeValue[$keyName]) )? $decodeValue[$keyName] : NULL;
                }
            }
        }
        return $cookieValue;
    }

    /*
     * Function to rekey object with specified key_column and convert to array
     */
    public static function rekeyObject($keyColumn, $arrayObject) {
        if(!$arrayObject || !$keyColumn) return NULL;

        return array_column($arrayObject->toArray(), null, $keyColumn);
    }

    /**
     * get group for passed number
     *
     * @param int number.
     * @return string group for number
     */
    public static function getGroupForNum($cnt = 0) {
        $groupArr = [
            [
                "high" => 5,
                "txt" => "5 or Less"
            ],
            [
                "low" => 5,
                "high" => 10,
            ],
            [
                "low" => 10,
                "high" => 100,
            ],
            [
                "low" => 100,
                "high" => 1000,
            ],
            [
                "low" => 1000,
                "txt" => "1000+"
            ]
        ];
        $groupText = "";
        foreach ($groupArr as $val) {
            if(isset($val['high']) && isset($val['low'])) {
                if($val['low'] < $cnt && $val['high'] >= $cnt) {
                    $groupText = isset($val['text']) ? $val['text'] : "(".$val['low']."-".$val['high'].")";
                    break;
                }
            }
            else if(isset($val['high']) && $val['high'] >= $cnt) {
                $groupText = isset($val['text']) ? $val['text'] : $val['high']." Or Less";
                break;
            }
            else if(isset($val['low']) && $val['low'] < $cnt) {
                $groupText = isset($val['text']) ? $val['text'] : $val['low']."+";
                break;
            }
        }
        return $groupText;
    }

    /**
     * Check current device is mobile only not tablet/Desktop
     *
     * @return true or false.
     */
    public static function isMobile() {
        $mobileDetect = new \App\Library\MobileDetect();
        $isMobile = ($mobileDetect->isMobile() && !$mobileDetect->isTablet()) ? true : false;
        return $isMobile;
    }
}