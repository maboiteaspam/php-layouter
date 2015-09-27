<?php
namespace C\View;

use Symfony\Component\Form\FormView;

/**
 * This is a virtual class (interface)
 * to let user add documentation hint about $this
 * when he is developing templates.
 *
 * That way he can get auto completion
 * and nice display.
 *
 * Interface ConcreteContext
 * @package C\View
 */

interface ConcreteContext {

    #region CommonViewHelper
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
    public function date($date, $format = null, $timezone = null);
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
    public function date_converter($date = null, $timezone = null);
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
    public function date_modify($date, $modifier);
    /**
     * Replace given args name
     * into string format
     *
     * Make format like
     * 'hello %name%'
     *
     * resolve it with an array like
     * ['name'=>'some']
     *
     * @param $format
     * @param array $args
     * @return mixed
     */
    public function format($format, $args = null, $_ = null);
    /**
     * Replaces strings within a string.
     *
     * @param string            $str  String to replace in
     * @param array|\Traversable $from Replace values
     * @param string|null       $to   Replace to, deprecated (@see http://php.net/manual/en/function.strtr.php)
     *
     * @return string
     */
    public function replace($str, $from, $to = null);
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
    public function number_format($number, $decimal = null, $decimalPoint = null, $thousandSep = null);
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
    public function abs($number);
    /**
     * Rounds a number.
     *
     * @param int|float $value     The value to round
     * @param int|float $precision The rounding precision
     * @param string    $method    The method to use for rounding
     *
     * @return int|float The rounded number
     */
    public function round($value, $precision = 0, $method = 'common');

    // encoding
    /**
     * URL encodes (RFC 3986) a string as a path segment or an array as a query string.
     *
     * @param string|array $url A URL or an array of query parameters
     *
     * @return string The URL encoded value
     */
    public function url_encode ($url);
    /**
     * JSON encodes a variable.
     *
     * @param mixed $value   The value to encode.
     * @param int   $options Bit-mask consisting of JSON_HEX_QUOT, JSON_HEX_TAG, JSON_HEX_AMP, JSON_HEX_APOS, JSON_NUMERIC_CHECK, JSON_PRETTY_PRINT, JSON_UNESCAPED_SLASHES, JSON_FORCE_OBJECT
     *
     * @return mixed The JSON encoded value
     */
    public function json_encode($value, $options = 0);
    /**
     * Converts encoding of a string. Internal.
     *
     * @param $str
     * @param $to
     * @param $from
     * @return string|void
     */
    public function convert_encoding($str, $to, $from);


    // string filters
    /**
     * Returns a titlecased string.
     *
     * @param string           $string A string
     *
     * @return string The titlecased string
     */
    public function title($string);
    /**
     * Returns a capitalized string.
     *
     * @param string           $string A string
     *
     * @return string The capitalized string
     */
    public function capitalize($string);
    /**
     * Converts a string to uppercase.
     *
     * @param string           $string A string
     *
     * @return string The uppercased string
     */
    public function upper($string);
    /**
     * Converts a string to lowercase.
     *
     * @param string           $string A string
     *
     * @return string The lowercased string
     */
    public function lower($string);
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
    public function striptags($str, $allowable_tags='');
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
    public function trim($charlist);
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
    public function nl2br($string, $is_xhtml=false);


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
    public function join($value, $glue = '');
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
    public function split($value, $delimiter, $limit = null);
    /**
     * Sorts an array.
     *
     * @param array|\Traversable $array
     *
     * @return array
     */
    public function sort($array);
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
    public function merge($arr1, $arr2);
    /**
     * Batches item.
     *
     * @param array $items An array of items
     * @param int   $size  The size of the batch
     * @param mixed $fill  A value used to fill missing items
     *
     * @return array
     */
    function batch($items, $size, $fill = null);


    // string/array filters
    /**
     * Reverses a variable.
     *
     * @param array|\Traversable|string $item         An array, a Traversable instance, or a string
     * @param bool                     $preserveKeys Whether to preserve key or not
     *
     * @return mixed The reversed input
     */
    public function reverse($item, $preserveKeys = false);
    /**
     * Returns the length of a variable.
     *
     * @param mixed            $thing A variable
     *
     * @return int The length of the value
     */
    public function length($thing);
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
    public function slice($item, $start, $length = null, $preserveKeys = false);
    /**
     * Returns the first element of the item.
     *
     * @param mixed            $item A variable
     *
     * @return mixed The first element of the item
     */
    public function first($item);
    /**
     * Returns the last element of the item.
     *
     * @param mixed            $item A variable
     *
     * @return mixed The last element of the item
     */
    public function last($item);


    // iteration and runtime

    /**
     * @param $value
     * @param string $default
     * @return string
     */
// The '_default' filter is used internally to avoid using the ternary operator
// which costs a lot for big contexts (before PHP 5.4). So, on average,
// a function call is cheaper.
    public function _default($value, $default = '');
    // stands for default

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
    public function keys ($array);


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
    public function escape($string, $strategy = 'html', $charset = null, $autoescape = false);

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
    public function max($value1, $value2=null);
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
    public function min($value1, $value2=null);
    public function range();
    /**
     * Provides the ability to get constants from instances as well as class/global constants.
     *
     * @param string      $constant The name of the constant
     * @param null|object $object   The object to get the constant from
     *
     * @return string
     */
    public function constant($constant, $object = null);
    /**
     * Cycles over a value.
     *
     * @param \ArrayAccess|array $values   An array or an ArrayAccess instance
     * @param int               $position The cycle position
     *
     * @return string The next value in the cycle
     */
    public function cycle ($values, $position);
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
    public function random($values = null);
    /**
     * Returns a file content without rendering it.
     *
     * @param string $file          The file path
     * @param bool   $ignoreMissing Whether to ignore missing templates or not
     *
     * @return string The template source
     * @throws \Exception
     */
    public function source($file, $ignoreMissing = false);

    // tests
    public function isEven();
    public function isOdd();
//    public abstract function isDefined(); // What for ?
    public function isSameAs();
//    public abstract function isNone(); // What for ?

    /**
     * Checks if null or empty value.
     *
     * @param $name
     * @return bool
     */
    public function isNull($name);
    public function isDivisibleBy();
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
    public function isConstant($name);
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
    public function isEmpty($value);
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
    public function isIterable($value);
    #endregion

    #region RoutingViewHelper
    /**
     * Forge url for a given route name and its parameters
     *
     * @param $name
     * @param array|object $options
     * @param array $only
     * @return mixed
     */
    public function urlFor($name, $options=[], $only=[]);

    /**
     * Forge URL GET parameters given an array/object of data.
     * It can exclude some property via $only second arguments
     *
     * @param array $data
     * @param array $only
     * @return mixed
     */
    public function urlArgs($data=[], $only=[]);
    #endregion

    #region AssetsViewHelper
    /**
     * Forge url to an assets given the asset type id and it s parameters
     *
     * @param $name
     * @param array|object $options
     * @param array $only
     * @return mixed
     */
    public function urlAsset($name, $options=[], $only=[]);
    #endregion

    #region LayoutViewHelper
    /**
     * Display a block given it s ID and its configuration.
     *
     * @param $blockId
     * @return mixed
     */
    public function display ($blockId);
    #endregion

    #region FormViewHelper
    // vendor/symfony/twig-bridge/Extension/FormExtension.php
    // vendor/symfony/twig-bridge/Resources/views/Form/form_div_layout.html.twig
    public function form_widget (FormView $form, $variables=[]);
    public function form_errors(FormView $form, $variables=[]);
    public function form_label (FormView $form, $variables=[]);
    public function form_row (FormView $form, $variables=[]);
    public function form_rows (FormView $form, $variables=[]);
    public function form_rest (FormView $form, $variables=[]);
    public function form (FormView $form, $variables=[]);
    public function form_start (FormView $form, $variables=[]);
    public function form_end (FormView $form, $variables=[]);
    public function csrf_token ();
    #endregion

    #region Form widgets
    public function textarea_widget (FormView $form, $variables=[]);
    public function choice_widget (FormView $form, $variables=[]);
    public function choice_widget_expanded (FormView $form, $variables=[]);
    public function choice_widget_collapsed (FormView $form, $variables=[]);
    public function choice_widget_options (FormView $form, $variables=[]);
    public function checkbox_widget (FormView $form, $variables=[]);
    public function radio_widget (FormView $form, $variables=[]);
    public function datetime_widget (FormView $form, $variables=[]);
    public function date_widget (FormView $form, $variables=[]);
    public function time_widget (FormView $form, $variables=[]);
    public function number_widget (FormView $form, $variables=[]);
    public function integer_widget (FormView $form, $variables=[]);
    public function money_widget (FormView $form, $variables=[]);
    public function url_widget (FormView $form, $variables=[]);
    public function search_widget (FormView $form, $variables=[]);
    public function percent_widget (FormView $form, $variables=[]);
    public function password_widget (FormView $form, $variables=[]);
    public function hidden_widget (FormView $form, $variables=[]);
    public function email_widget (FormView $form, $variables=[]);
    public function text_widget (FormView $form, $variables=[]);
    public function button_widget (FormView $form, $variables=[]);
    public function submit_widget (FormView $form, $variables=[]);
    public function reset_widget (FormView $form, $variables=[]);
    #endregion

}
