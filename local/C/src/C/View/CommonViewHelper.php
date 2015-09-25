<?php
namespace C\View;

use Silex\Translator;
use Symfony\Component\Config\Definition\Exception\Exception;

class CommonViewHelper extends AbstractViewHelper {

    // totally inspired by twig
    // vendor/twig/twig/lib/Twig/Extension/Core.php


    public $escapers = [];
    public function getEscapers () {
        return $this->escapers;
    }
    public function addEscaper ( \Closure $fn ) {
        $this->escapers[] = $fn;
    }

    /**
     * @var Translator
     */
    public $translator;
    public function setTranslator (Translator $helper) {
        $this->translator = $helper;
    }

    /**
     * @param $id
     * @param array $parameters
     * @param null $domain
     * @param null $locale
     * @return array
     */
    public function trans () {
        return call_user_func_array([$this->translator, 'trans'], func_get_args());
    }

    /**
     * @param $id
     * @param $number
     * @param array $parameters
     * @param null $domain
     * @param null $locale
     * @return array
     */
    public function transChoice () {
        return call_user_func_array([$this->translator, 'transChoice'], func_get_args());
    }

    // formatting filters

    /**
     * Converts a date to the given format.
     *
     * <pre>
     *   {{ post.published_at|date("m/d/Y") }}
     * </pre>
     *
     * @param \DateTime|\DateTimeInterface|\DateInterval|string $date     A date
     * @param string|null                                    $format   The target format, null to use the default
     * @param \DateTimeZone|string|null|false                 $timezone The target timezone, null to use the default, false to leave unchanged
     *
     * @return string The formatted date
     */
    public function date($date, $format = null, $timezone = null)
    {
        if (null === $format) {
            $formats = $this->getDateFormat();
            $format = $date instanceof \DateInterval ? $formats[1] : $formats[0];
        }

        if ($date instanceof \DateInterval) {
            return $date->format($format);
        }

        return $this->date_converter($date, $timezone)->format($format);
    }
    /**
     * Converts an input to a DateTime instance.
     *
     * <pre>
     *    {% if date(user.created_at) < date('+2days') %}
     *      {# do something #}
     *    {% endif %}
     * </pre>
     *
     * @param \DateTime|\DateTimeInterface|string|null $date     A date
     * @param \DateTimeZone|string|null|false         $timezone The target timezone, null to use the default, false to leave unchanged
     *
     * @return \DateTime A DateTime instance
     */
    public function date_converter($date = null, $timezone = null)
    {
        // determine the timezone
        if (false !== $timezone) {
            if (null === $timezone) {
                $timezone = $this->getTimezone();
            } elseif (!$timezone instanceof \DateTimeZone) {
                $timezone = new \DateTimeZone($timezone);
            }
        }

        // immutable dates
        if ($date instanceof \DateTimeImmutable) {
            return false !== $timezone ? $date->setTimezone($timezone) : $date;
        }

        if ($date instanceof \DateTime || $date instanceof \DateTimeInterface) {
            $date = clone $date;
            if (false !== $timezone) {
                $date->setTimezone($timezone);
            }

            return $date;
        }

        if (null === $date || 'now' === $date) {
            return new \DateTime($date, false !== $timezone ? $timezone : $this->getTimezone());
        }

        $asString = (string) $date;
        if (ctype_digit($asString) || (!empty($asString) && '-' === $asString[0] && ctype_digit(substr($asString, 1)))) {
            $date = new \DateTime('@'.$date);
        } else {
            $date = new \DateTime($date, $this->getTimezone());
        }

        if (false !== $timezone) {
            $date->setTimezone($timezone);
        }

        return $date;
    }
    /**
     * Returns a new date object modified.
     *
     * <pre>
     *   {{ post.published_at|date_modify("-1day")|date("m/d/Y") }}
     * </pre>
     *
     * @param \DateTime|string  $date     A date
     * @param string           $modifier A modifier string
     *
     * @return \DateTime A new date object
     */
    public function date_modify($date, $modifier)
    {
        $date = $this->date_converter($date, false);
        $resultDate = $date->modify($modifier);

        // This is a hack to ensure PHP 5.2 support and support for DateTimeImmutable
        // DateTime::modify does not return the modified DateTime object < 5.3.0
        // and DateTimeImmutable does not modify $date.
        return null === $resultDate ? $date : $resultDate;
    }
    /**
     * @param $format
     * @param array $args
     * @return mixed
     */
    public function format($format, $args = []) {
        return str_replace(array_keys($args), array_values($args), $format);
    }
    /**
     * Replaces strings within a string.
     *
     * @param string            $str  String to replace in
     * @param array|\Traversable $from Replace values
     * @param string|null       $to   Replace to, deprecated (@see http://php.net/manual/en/function.strtr.php)
     *
     * @return string
     */
    public function replace($str, $from, $to = null)
    {
        if ($from instanceof \Traversable) {
            $from = iterator_to_array($from);
        } elseif (is_string($from) && is_string($to)) {
            @trigger_error(
                'Using "replace" with character by character replacement is deprecated and will be removed in Twig 2.0',
                E_USER_DEPRECATED);

            return strtr($str, $from, $to);
        } elseif (!is_array($from)) {
            throw new Exception(sprintf(
                'The "replace" filter expects an array or "Traversable" as replace values, got "%s".',
                is_object($from) ? get_class($from) : gettype($from)));
        }

        return strtr($str, $from);
    }
    /**
     * Number format filter.
     *
     * All of the formatting options can be left null, in that case the defaults will
     * be used.  Supplying any of the parameters will override the defaults set in the
     * environment object.
     *
     * @param mixed            $number       A float/int/string of the number to format
     * @param int              $decimal      The number of decimal points to display.
     * @param string           $decimalPoint The character(s) to use for the decimal point.
     * @param string           $thousandSep  The character(s) to use for the thousands separator.
     *
     * @return string The formatted number
     */
    public function number_format($number, $decimal = null, $decimalPoint = null, $thousandSep = null)
    {
        $defaults = $this->env->getNumberFormat();
        if (null === $decimal) {
            $decimal = $defaults[0];
        }

        if (null === $decimalPoint) {
            $decimalPoint = $defaults[1];
        }

        if (null === $thousandSep) {
            $thousandSep = $defaults[2];
        }

        return number_format((float) $number, $decimal, $decimalPoint, $thousandSep);
    }
    /**
     * (PHP 4, PHP 5)<br/>
     * Absolute value
     * @link http://php.net/manual/en/function.abs.php
     * @param mixed $number <p>
     * The numeric value to process
     * </p>
     * @return number The absolute value of number. If the
     * argument number is
     * of type float, the return type is also float,
     * otherwise it is integer (as float usually has a
     * bigger value range than integer).
     */
    public function abs($number) {
        return abs($number);
    }
    /**
     * Rounds a number.
     *
     * @param int|float $value     The value to round
     * @param int|float $precision The rounding precision
     * @param string    $method    The method to use for rounding
     *
     * @return int|float The rounded number
     */
    public function round($value, $precision = 0, $method = 'common')
    {
        if ('common' == $method) {
            return round($value, $precision);
        }

        if ('ceil' != $method && 'floor' != $method) {
            throw new Exception('The round filter only supports the "common", "ceil", and "floor" methods.');
        }

        return $method($value * pow(10, $precision)) / pow(10, $precision);
    }


    // encoding
    /**
     * URL encodes (RFC 3986) a string as a path segment or an array as a query string.
     *
     * @param string|array $url A URL or an array of query parameters
     *
     * @return string The URL encoded value
     */
    public function url_encode ($url)
    {
        if (is_array($url)) {
            if (defined('PHP_QUERY_RFC3986')) {
                return http_build_query($url, '', '&', PHP_QUERY_RFC3986);
            }

            return http_build_query($url, '', '&');
        }

        return rawurlencode($url);
    }
    /**
     * JSON encodes a variable.
     *
     * @param mixed $value   The value to encode.
     * @param int   $options Bit-mask consisting of JSON_HEX_QUOT, JSON_HEX_TAG, JSON_HEX_AMP, JSON_HEX_APOS, JSON_NUMERIC_CHECK, JSON_PRETTY_PRINT, JSON_UNESCAPED_SLASHES, JSON_FORCE_OBJECT
     *
     * @return mixed The JSON encoded value
     */
    public function json_encode($value, $options = 0)
    {
        return json_encode($value, $options);
    }
    /**
     * Converts encoding of a string. Internal.
     *
     * @param $str
     * @param $to
     * @param $from
     * @return string|void
     */
    public function convert_encoding($str, $to, $from) {
        return patched_convert_encoding($str, $to, $from);
    }


    // string filters
    /**
     * Returns a titlecased string.
     *
     * @param string           $string A string
     *
     * @return string The titlecased string
     */
    public function title($string)
    {
        return patched_titlecased($string, $this->getCharset());
    }
    /**
     * Returns a capitalized string.
     *
     * @param string           $string A string
     *
     * @return string The capitalized string
     */
    public function capitalize($string) {
        return patched_capitalize($string, $this->getCharset());
    }
    /**
     * Converts a string to uppercase.
     *
     * @param string           $string A string
     *
     * @return string The uppercased string
     */
    public function upper($string)
    {
        return patched_upper($string, $this->getCharset());
    }
    /**
     * Converts a string to lowercase.
     *
     * @param string           $string A string
     *
     * @return string The lowercased string
     */
    public function lower($string)
    {
        return patched_lower($string, $this->getCharset());
    }
    /**
     * (PHP 4, PHP 5)<br/>
     * Strip HTML and PHP tags from a string
     * @link http://php.net/manual/en/function.strip-tags.php
     * @param string $str <p>
     * The input string.
     * </p>
     * @param string $allowable_tags [optional] <p>
     * You can use the optional second parameter to specify tags which should
     * not be stripped.
     * </p>
     * <p>
     * HTML comments and PHP tags are also stripped. This is hardcoded and
     * can not be changed with allowable_tags.
     * </p>
     * @return string the stripped string.
     */
    public function striptags($str, $allowable_tags='') {
        return strip_tags($str, $allowable_tags);
    }
    /**
     * (PHP 4, PHP 5)<br/>
     * Strip whitespace (or other characters) from the beginning and end of a string
     * @link http://php.net/manual/en/function.trim.php
     * @param string $str <p>
     * The string that will be trimmed.
     * </p>
     * @param string $charlist [optional] <p>
     * Optionally, the stripped characters can also be specified using
     * the charlist parameter.
     * Simply list all characters that you want to be stripped. With
     * .. you can specify a range of characters.
     * </p>
     * @return string The trimmed string.
     */
    public function trim($str, $charlist) {
        return trim($str, $charlist);
    }
    /**
     * (PHP 4, PHP 5)<br/>
     * Inserts HTML line breaks before all newlines in a string
     * @link http://php.net/manual/en/function.nl2br.php
     * @param string $string <p>
     * The input string.
     * </p>
     * @param bool $is_xhtml [optional] <p>
     * Whenever to use XHTML compatible line breaks or not.
     * </p>
     * @return string the altered string.
     */
    public function nl2br($string, $is_xhtml=false) {
        return nl2br($string, $is_xhtml);
    }

    /**
     * @param string $str
     * @return string
     */
    public function humanize ($str) {
        // @todo implement.
        return $str;
    }


    // array helpers
    /**
     * Joins the values to a string.
     *
     * The separator between elements is an empty string per default, you can define it with the optional parameter.
     *
     * <pre>
     *  {{ [1, 2, 3]|join('|') }}
     *  {# returns 1|2|3 #}
     *
     *  {{ [1, 2, 3]|join }}
     *  {# returns 123 #}
     * </pre>
     *
     * @param array  $value An array
     * @param string $glue  The separator
     *
     * @return string The concatenated string
     */
    public function join($value, $glue = '')
    {
        if ($value instanceof \Traversable) {
            $value = iterator_to_array($value, false);
        }

        return implode($glue, (array) $value);
    }
    /**
     * Splits the string into an array.
     *
     * <pre>
     *  {{ "one,two,three"|split(',') }}
     *  {# returns [one, two, three] #}
     *
     *  {{ "one,two,three,four,five"|split(',', 3) }}
     *  {# returns [one, two, "three,four,five"] #}
     *
     *  {{ "123"|split('') }}
     *  {# returns [1, 2, 3] #}
     *
     *  {{ "aabbcc"|split('', 2) }}
     *  {# returns [aa, bb, cc] #}
     * </pre>
     *
     * @param string $value     A string
     * @param string $delimiter The delimiter
     * @param int    $limit     The limit
     *
     * @return array The split string as an array
     */
    public function split($value, $delimiter, $limit = null)
    {
        if (!empty($delimiter)) {
            return null === $limit ? explode($delimiter, $value) : explode($delimiter, $value, $limit);
        }

        if (!function_exists('mb_get_info') || null === $charset = $this->getCharset()) {
            return str_split($value, null === $limit ? 1 : $limit);
        }

        if ($limit <= 1) {
            return preg_split('/(?<!^)(?!$)/u', $value);
        }

        $length = mb_strlen($value, $charset);
        if ($length < $limit) {
            return array($value);
        }

        $r = array();
        for ($i = 0; $i < $length; $i += $limit) {
            $r[] = mb_substr($value, $i, $limit, $charset);
        }

        return $r;
    }
    /**
     * Sorts an array.
     *
     * @param array|\Traversable $array
     *
     * @return array
     */
    public function sort($array)
    {
        if ($array instanceof \Traversable) {
            $array = iterator_to_array($array);
        } elseif (!is_array($array)) {
            throw new Exception(sprintf(
                'The sort filter only works with arrays or "Traversable", got "%s".',
                gettype($array)));
        }

        asort($array);

        return $array;
    }
    /**
     * Merges an array with another one.
     *
     * <pre>
     *  {% set items = { 'apple': 'fruit', 'orange': 'fruit' } %}
     *
     *  {% set items = items|merge({ 'peugeot': 'car' }) %}
     *
     *  {# items now contains { 'apple': 'fruit', 'orange': 'fruit', 'peugeot': 'car' } #}
     * </pre>
     *
     * @param array|\Traversable $arr1 An array
     * @param array|\Traversable $arr2 An array
     *
     * @return array The merged array
     * @throws \Exception
     */
    public function merge($arr1, $arr2)
    {
        if ($arr1 instanceof \Traversable) {
            $arr1 = iterator_to_array($arr1);
        } elseif (!is_array($arr1)) {
            throw new \Exception(sprintf(
                'The merge filter only works with arrays or "Traversable", got "%s" as first argument.',
                gettype($arr1)));
        }

        if ($arr2 instanceof \Traversable) {
            $arr2 = iterator_to_array($arr2);
        } elseif (!is_array($arr2)) {
            throw new \Exception(sprintf(
                'The merge filter only works with arrays or "Traversable", got "%s" as second argument.',
                gettype($arr2)));
        }

        return array_merge($arr1, $arr2);
    }
    /**
     * Batches item.
     *
     * @param array $items An array of items
     * @param int   $size  The size of the batch
     * @param mixed $fill  A value used to fill missing items
     *
     * @return array
     */
    function batch($items, $size, $fill = null)
    {
        if ($items instanceof \Traversable) {
            $items = iterator_to_array($items, false);
        }

        $size = ceil($size);

        $result = array_chunk($items, $size, true);

        if (null !== $fill && !empty($result)) {
            $last = count($result) - 1;
            if ($fillCount = $size - count($result[$last])) {
                $result[$last] = array_merge(
                    $result[$last],
                    array_fill(0, $fillCount, $fill)
                );
            }
        }

        return $result;
    }


    // string/array filters
    /**
     * Reverses a variable.
     *
     * @param array|\Traversable|string $item         An array, a Traversable instance, or a string
     * @param bool                     $preserveKeys Whether to preserve key or not
     *
     * @return mixed The reversed input
     */
    public function reverse($item, $preserveKeys = false)
    {
        if ($item instanceof \Traversable) {
            return array_reverse(iterator_to_array($item), $preserveKeys);
        }

        if (is_array($item)) {
            return array_reverse($item, $preserveKeys);
        }

        if (null !== $charset = $this->getCharset()) {
            $string = (string) $item;

            if ('UTF-8' != $charset) {
                $item = patched_convert_encoding($string, 'UTF-8', $charset);
            }

            preg_match_all('/./us', $item, $matches);

            $string = implode('', array_reverse($matches[0]));

            if ('UTF-8' != $charset) {
                $string = patched_convert_encoding($string, $charset, 'UTF-8');
            }

            return $string;
        }

        return strrev((string) $item);
    }
    /**
     * Returns the length of a variable.
     *
     * @param mixed            $thing A variable
     *
     * @return int The length of the value
     */
    public function length($thing) {
        return patched_length($thing, $this->getCharset());
    }
    /**
     * Slices a variable.
     *
     * @param mixed            $item         A variable
     * @param int              $start        Start of the slice
     * @param int              $length       Size of the slice
     * @param bool             $preserveKeys Whether to preserve key or not (when the input is an array)
     *
     * @return mixed The sliced variable
     */
    public function slice($item, $start, $length = null, $preserveKeys = false)
    {
        if ($item instanceof \Traversable) {
            if ($item instanceof \IteratorAggregate) {
                $item = $item->getIterator();
            }

            if ($start >= 0 && $length >= 0 && $item instanceof \Iterator) {
                try {
                    return iterator_to_array(new \LimitIterator($item, $start, $length === null ? -1 : $length), $preserveKeys);
                } catch (\OutOfBoundsException $exception) {
                    return array();
                }
            }

            $item = iterator_to_array($item, $preserveKeys);
        }

        if (is_array($item)) {
            return array_slice($item, $start, $length, $preserveKeys);
        }

        $item = (string) $item;

        if (function_exists('mb_get_info') && null !== $charset = $this->getCharset()) {
            return (string) mb_substr($item, $start, null === $length ? mb_strlen($item, $charset) - $start : $length, $charset);
        }

        return (string) (null === $length ? substr($item, $start) : substr($item, $start, $length));
    }
    /**
     * Returns the first element of the item.
     *
     * @param mixed            $item A variable
     *
     * @return mixed The first element of the item
     */
    public function first($item)
    {
        $elements = $this->slice($item, 0, 1, false);

        return is_string($elements) ? $elements : current($elements);
    }
    /**
     * Returns the last element of the item.
     *
     * @param mixed            $item A variable
     *
     * @return mixed The last element of the item
     */
    public function last($item)
    {
        $elements = $this->slice($item, -1, 1, false);

        return is_string($elements) ? $elements : current($elements);
    }


    // iteration and runtime

    /**
     * @param $value
     * @param string $default
     * @return string
     */
// The '_default' filter is used internally to avoid using the ternary operator
// which costs a lot for big contexts (before PHP 5.4). So, on average,
// a function call is cheaper.
    public function _default($value, $default = '')
    {
        if ($this->isEmpty($value)) {
            return $default;
        }

        return $value;
    }
    // stands fro default

    /**
     * Returns the keys for the given array.
     *
     * It is useful when you want to iterate over the keys of an array:
     *
     * <pre>
     *  {% for key in array|keys %}
     *      {# ... #}
     *  {% endfor %}
     * </pre>
     *
     * @param array $array An array
     *
     * @return array The keys
     */
    public function keys ($array)
    {
        if ($array instanceof \Traversable) {
            return array_keys(iterator_to_array($array));
        }

        if (!is_array($array)) {
            return array();
        }

        return array_keys($array);
    }


    // escaping

    /**
     * Escapes a string.
     *
     * @param string           $string     The value to be escaped
     * @param string           $strategy   The escaping strategy
     * @param string           $charset    The charset
     * @param bool             $autoescape Whether the function is called by the auto-escaping feature (true) or by the developer (false)
     *
     * @return string
     * @throws \Exception
     */
    public function escape($string, $strategy = 'html', $charset = null, $autoescape = false)
    {
        if (!is_string($string)) {
            if (is_object($string) && method_exists($string, '__toString')) {
                $string = (string) $string;
            } else {
                return $string;
            }
        }

        if (null === $charset) {
            $charset = $this->getCharset();
        }

        switch ($strategy) {
            case 'html':
                // see http://php.net/htmlspecialchars

                // Using a static variable to avoid initializing the array
                // each time the function is called. Moving the declaration on the
                // top of the function slow downs other escaping strategies.
                static $htmlspecialcharsCharsets;

                if (null === $htmlspecialcharsCharsets) {
                    if (defined('HHVM_VERSION')) {
                        $htmlspecialcharsCharsets = array('utf-8' => true, 'UTF-8' => true);
                    } else {
                        $htmlspecialcharsCharsets = array(
                            'ISO-8859-1' => true, 'ISO8859-1' => true,
                            'ISO-8859-15' => true, 'ISO8859-15' => true,
                            'utf-8' => true, 'UTF-8' => true,
                            'CP866' => true, 'IBM866' => true, '866' => true,
                            'CP1251' => true, 'WINDOWS-1251' => true, 'WIN-1251' => true,
                            '1251' => true,
                            'CP1252' => true, 'WINDOWS-1252' => true, '1252' => true,
                            'KOI8-R' => true, 'KOI8-RU' => true, 'KOI8R' => true,
                            'BIG5' => true, '950' => true,
                            'GB2312' => true, '936' => true,
                            'BIG5-HKSCS' => true,
                            'SHIFT_JIS' => true, 'SJIS' => true, '932' => true,
                            'EUC-JP' => true, 'EUCJP' => true,
                            'ISO8859-5' => true, 'ISO-8859-5' => true, 'MACROMAN' => true,
                        );
                    }
                }

                if (isset($htmlspecialcharsCharsets[$charset])) {
                    return htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE, $charset);
                }

                if (isset($htmlspecialcharsCharsets[strtoupper($charset)])) {
                    // cache the lowercase variant for future iterations
                    $htmlspecialcharsCharsets[$charset] = true;

                    return htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE, $charset);
                }

                $string = patched_convert_encoding($string, 'UTF-8', $charset);
                $string = htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

                return patched_convert_encoding($string, $charset, 'UTF-8');

            case 'js':
                // escape all non-alphanumeric characters
                // into their \xHH or \uHHHH representations
                if ('UTF-8' != $charset) {
                    $string = patched_convert_encoding($string, 'UTF-8', $charset);
                }

                if (0 == strlen($string) ? false : (1 == preg_match('/^./su', $string) ? false : true)) {
                    throw new \Exception('The string to escape is not a valid UTF-8 string.');
                }

                $string = preg_replace_callback('#[^a-zA-Z0-9,\._]#Su', 'escape_js_callback', $string);

                if ('UTF-8' != $charset) {
                    $string = patched_convert_encoding($string, $charset, 'UTF-8');
                }

                return $string;

            case 'css':
                if ('UTF-8' != $charset) {
                    $string = patched_convert_encoding($string, 'UTF-8', $charset);
                }

                if (0 == strlen($string) ? false : (1 == preg_match('/^./su', $string) ? false : true)) {
                    throw new \Exception('The string to escape is not a valid UTF-8 string.');
                }

                $string = preg_replace_callback('#[^a-zA-Z0-9]#Su', 'escape_css_callback', $string);

                if ('UTF-8' != $charset) {
                    $string = patched_convert_encoding($string, $charset, 'UTF-8');
                }

                return $string;

            case 'html_attr':
                if ('UTF-8' != $charset) {
                    $string = patched_convert_encoding($string, 'UTF-8', $charset);
                }

                if (0 == strlen($string) ? false : (1 == preg_match('/^./su', $string) ? false : true)) {
                    throw new \Exception('The string to escape is not a valid UTF-8 string.');
                }

                $string = preg_replace_callback('#[^a-zA-Z0-9,\.\-_]#Su', 'escape_html_attr_callback', $string);

                if ('UTF-8' != $charset) {
                    $string = patched_convert_encoding($string, $charset, 'UTF-8');
                }

                return $string;

            case 'url':
                if (PHP_VERSION_ID < 50300) {
                    return str_replace('%7E', '~', rawurlencode($string));
                }

                return rawurlencode($string);

            default:
                static $escapers;

                if (null === $escapers) {
                    $escapers = $this->getEscapers();
                }

                if (isset($escapers[$strategy])) {
                    return call_user_func($escapers[$strategy], $string, $charset);
                }

                $validStrategies = implode(', ', array_merge(array('html', 'js', 'url', 'css', 'html_attr'), array_keys($escapers)));

                throw new \Exception(sprintf('Invalid escaping strategy "%s" (valid ones: %s).', $strategy, $validStrategies));
        }
    }

    // @todo
//    public abstract function e();

    // global functions
    /**
     * (PHP 4, PHP 5)<br/>
     * Find highest value
     * @link http://php.net/manual/en/function.max.php
     * @param array|mixed $value1 Array to look through or first value to compare
     * @param mixed $value2 [optional] second value to compare
     * </p>
     * @return mixed max returns the numerically highest of the
     * parameter values, either within a arg array or two arguments.
     */
    public function max($value1, $value2=null) {
        return max($value1, $value2);
    }
    /**
     * (PHP 4, PHP 5)<br/>
     * Find lowest value
     * @link http://php.net/manual/en/function.min.php
     * @param array|mixed $value1 Array to look through or first value to compare
     * @param mixed $value2 [optional] second value to compare
     * </p>
     * @return mixed min returns the numerically lowest of the
     * parameter values.
     */
    public function min($value1, $value2=null) {
        return min($value1, $value2);
    }

    // @todo
//    public abstract function range();
    /**
     * Provides the ability to get constants from instances as well as class/global constants.
     *
     * @param string      $constant The name of the constant
     * @param null|object $object   The object to get the constant from
     *
     * @return string
     */
    public function constant($constant, $object = null)
    {
        if (null !== $object) {
            $constant = get_class($object).'::'.$constant;
        }

        return constant($constant);
    }
    /**
     * Cycles over a value.
     *
     * @param \ArrayAccess|array $values   An array or an ArrayAccess instance
     * @param int               $position The cycle position
     *
     * @return string The next value in the cycle
     */
    public function cycle ($values, $position)
    {
        if (!is_array($values) && !$values instanceof \ArrayAccess) {
            return $values;
        }

        return $values[$position % count($values)];
    }
    /**
     * Returns a random value depending on the supplied parameter type:
     * - a random item from a Traversable or array
     * - a random character from a string
     * - a random integer between 0 and the integer parameter.
     *
     * @param \Traversable|array|int|string $values The values to pick a random item from
     *
     * @throws \Exception When $values is an empty array (does not apply to an empty string which is returned as is).
     *
     * @return mixed A random value from the given sequence
     */
    public function random($values = null)
    {
        if (null === $values) {
            return mt_rand();
        }

        if (is_int($values) || is_float($values)) {
            return $values < 0 ? mt_rand($values, 0) : mt_rand(0, $values);
        }

        if ($values instanceof \Traversable) {
            $values = iterator_to_array($values);
        } elseif (is_string($values)) {
            if ('' === $values) {
                return '';
            }
            if (null !== $charset = $this->getCharset()) {
                if ('UTF-8' != $charset) {
                    $values = patched_convert_encoding($values, 'UTF-8', $charset);
                }

                // unicode version of str_split()
                // split at all positions, but not after the start and not before the end
                $values = preg_split('/(?<!^)(?!$)/u', $values);

                if ('UTF-8' != $charset) {
                    foreach ($values as $i => $value) {
                        $values[$i] = patched_convert_encoding($value, $charset, 'UTF-8');
                    }
                }
            } else {
                return $values[mt_rand(0, strlen($values) - 1)];
            }
        }

        if (!is_array($values)) {
            return $values;
        }

        if (0 === count($values)) {
            throw new \Exception('The random function cannot pick from an empty array.');
        }

        return $values[array_rand($values, 1)];
    }
    /**
     * Returns a file content without rendering it.
     *
     * @param string $file          The file path
     * @param bool   $ignoreMissing Whether to ignore missing templates or not
     *
     * @return string The template source
     * @throws \Exception
     */
    public function source($file, $ignoreMissing = false)
    {
        try {
            return file_get_contents($file);
        } catch (\Exception $e) {
            if (!$ignoreMissing) {
                throw $e;
            }
        }
        return '';
    }

    // tests
    public function isEven($num) {
        $num = (string)$num;
        return in_array((int)substr($num,-1), [0,2,4,6,8]);
    }
    public function isOdd($num) {
        return !$this->isEven($num);
    }
//    public abstract function isDefined(); // tells if a variable is defined. it needs access to block data.
    public function isSameAs($a, $b) {
        return $a==$b; // may need improvements later
    }
//    public abstract function isNone(); // What for ?

    /**
     * Checks if null or empty value.
     *
     * @param $name
     * @return bool
     */
    public function isNull($name) {
        return $name===null || empty($name);
    }
    public function isDivisibleBy($a, $b) {
        return 0 == $a%$b;
    }
    /**
     * (PHP 4, PHP 5)<br/>
     * Checks whether a given named constant exists
     * @link http://php.net/manual/en/function.defined.php
     * @param string $name <p>
     * The constant name.
     * </p>
     * @return bool true if the named constant given by <i>name</i>
     * has been defined, false otherwise.
     */
    public function isConstant($name) {
        return defined($name);
    }
    /**
     * Checks if a variable is empty.
     *
     * <pre>
     * {# evaluates to true if the foo variable is null, false, or the empty string #}
     * {% if foo is empty %}
     *     {# ... #}
     * {% endif %}
     * </pre>
     *
     * @param mixed $value A variable
     *
     * @return bool true if the value is empty, false otherwise
     */
    public function isEmpty($value)
    {
        if ($value instanceof \Countable) {
            return 0 == count($value);
        }

        return '' === $value || false === $value || null === $value || array() === $value;
    }
    /**
     * Checks if a variable is traversable.
     *
     * <pre>
     * {# evaluates to true if the foo variable is an array or a traversable object #}
     * {% if foo is traversable %}
     *     {# ... #}
     * {% endif %}
     * </pre>
     *
     * @param mixed $value A variable
     *
     * @return bool true if the value is traversable
     */
    public function isIterable($value)
    {
        return $value instanceof \Traversable || is_array($value);
    }



}


if (function_exists('mb_convert_encoding')) {
    function patched_convert_encoding($string, $to, $from)
    {
        return mb_convert_encoding($string, $to, $from);
    }
} elseif (function_exists('iconv')) {
    function patched_convert_encoding($string, $to, $from)
    {
        return iconv($from, $to, $string);
    }
} else {
    function patched_convert_encoding($string, $to, $from)
    {
        throw new \Exception(
            'No suitable convert encoding function
            (use UTF-8 as your encoding or install the iconv or mbstring extension).');
    }
}




/* used internally */

/* used internally */

function escape_js_callback($matches)
{
    $char = $matches[0];

    // \xHH
    if (!isset($char[1])) {
        return '\\x'.strtoupper(substr('00'.bin2hex($char), -2));
    }

    // \uHHHH
    $char = patched_convert_encoding($char, 'UTF-16BE', 'UTF-8');

    return '\\u'.strtoupper(substr('0000'.bin2hex($char), -4));
}

function escape_css_callback($matches)
{
    $char = $matches[0];

    // \xHH
    if (!isset($char[1])) {
        $hex = ltrim(strtoupper(bin2hex($char)), '0');
        if (0 === strlen($hex)) {
            $hex = '0';
        }

        return '\\'.$hex.' ';
    }

    // \uHHHH
    $char = patched_convert_encoding($char, 'UTF-16BE', 'UTF-8');

    return '\\'.ltrim(strtoupper(bin2hex($char)), '0').' ';
}

/**
 * This function is adapted from code coming from Zend Framework.
 *
 * @param $matches
 * @return string
 *
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
function escape_html_attr_callback($matches)
{
    /*
     * While HTML supports far more named entities, the lowest common denominator
     * has become HTML5's XML Serialisation which is restricted to the those named
     * entities that XML supports. Using HTML entities would result in this error:
     *     XML Parsing Error: undefined entity
     */
    static $entityMap = array(
        34 => 'quot', /* quotation mark */
        38 => 'amp',  /* ampersand */
        60 => 'lt',   /* less-than sign */
        62 => 'gt',   /* greater-than sign */
    );

    $chr = $matches[0];
    $ord = ord($chr);

    /*
     * The following replaces characters undefined in HTML with the
     * hex entity for the Unicode replacement character.
     */
    if (($ord <= 0x1f && $chr != "\t" && $chr != "\n" && $chr != "\r") || ($ord >= 0x7f && $ord <= 0x9f)) {
        return '&#xFFFD;';
    }

    /*
     * Check if the current character to escape has a name entity we should
     * replace it with while grabbing the hex value of the character.
     */
    if (strlen($chr) == 1) {
        $hex = strtoupper(substr('00'.bin2hex($chr), -2));
    } else {
        $chr = patched_convert_encoding($chr, 'UTF-16BE', 'UTF-8');
        $hex = strtoupper(substr('0000'.bin2hex($chr), -4));
    }

    $int = hexdec($hex);
    if (array_key_exists($int, $entityMap)) {
        return sprintf('&%s;', $entityMap[$int]);
    }

    /*
     * Per OWASP recommendations, we'll use hex entities for any other
     * characters where a named entity does not exist.
     */
    return sprintf('&#x%s;', $hex);
}


/* used internally */



// add multibyte extensions if possible
if (function_exists('mb_get_info')) {
    function patched_length($thing, $charset)
    {
        return is_scalar($thing) ? mb_strlen($thing, $charset) : count($thing);
    }
    function patched_upper($string, $charset)
    {
        if (null !== $charset) {
            return mb_strtoupper($string, $charset);
        }

        return strtoupper($string);
    }
    function patched_lower($string, $charset)
    {
        if (null !== $charset) {
            return mb_strtolower($string, $charset);
        }

        return strtolower($string);
    }
    function patched_titlecased($string, $charset)
    {
        if (null !== $charset) {
            return mb_convert_case($string, MB_CASE_TITLE, $charset);
        }

        return ucwords(strtolower($string));
    }
    function patched_capitalize($string, $charset)
    {
        if (null !== $charset) {
            return mb_strtoupper(mb_substr($string, 0, 1, $charset), $charset).
            mb_strtolower(mb_substr($string, 1, mb_strlen($string, $charset), $charset), $charset);
        }

        return ucfirst(strtolower($string));
    }
}
// and byte fallback
else {
    function patched_length($thing)
    {
        return is_scalar($thing) ? strlen($thing) : count($thing);
    }
    function patched_upper($string)
    {
        return strtoupper($string);
    }
    function patched_lower($string)
    {
        return strtolower($string);
    }
    function patched_titlecased($string)
    {
        return ucwords(strtolower($string));
    }
    function patched_capitalize($string)
    {
        return ucfirst(strtolower($string));
    }
}




