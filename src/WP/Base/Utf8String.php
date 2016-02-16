<?php
namespace WP\Base;

/**
 * Class Utf8String
 * @package WP\Base
 * @property-read int $length
 */
class Utf8String implements \Iterator, \Countable, \ArrayAccess
{
    const ENC = 'UTF-8';

    const SEARCH_AUTO = 0;
    const SEARCH_REGULAR = 1;
    const SEARCH_SUBSTR = 2;

    private $s = '';
    private $currentIndex = 0;

    /////////////////////// Constructors

    public function __construct($string = '') {
        $this->s = strval($string);
    }

    /**
     * Constructor. Creates String object from default PHP string.
     *
     * @param string $string
     * @return Utf8String
     */
    public static function fromPhpString($string = '') {
        return new self($string);
    }

    /**
     * Constructor. Creates String from file content.
     *
     * @param string $filename
     * @return Utf8String
     * @throws \InvalidArgumentException
     */
    public static function fromFile($filename) {
        if (is_file($filename) && is_readable($filename)) {
            return new self(file_get_contents($filename));
        }
        throw new \InvalidArgumentException("File '{$filename}' is not readable or not exists", 779);
    }

    /**
     * Constructor. Creates String by imploding passed array.
     *
     * @param array $array
     * @param string $glue
     * @return Utf8String
     * @throws \InvalidArgumentException
     */
    public static function fromArray($array, $glue = '') {
        if (is_array($array)) {
            return new self(implode($glue, $array));
        }
        throw new \InvalidArgumentException("Argument is not array", 780);
    }

    ////////////// Static versions of str funcs

    /**
     * @param string $phpStr
     * @return int
     */
    public static function strlen($phpStr) {
        return mb_strlen($phpStr, self::ENC);
    }

    /**
     * @param string $phpStr
     * @param int $from
     * @param null|int $count
     * @return string
     */
    public static function strSubstr($phpStr, $from = 0, $count = null) {
        return mb_substr($phpStr, $from, $count, self::ENC);
    }

    /**
     * @param string $phpStr
     * @return string
     */
    public static function strToUpper($phpStr) {
        return mb_strtoupper($phpStr, self::ENC);
    }

    /**
     * @param string $phpStr
     * @return string
     */
    public static function strToUpperWords($phpStr) {
        return mb_convert_case($phpStr, MB_CASE_TITLE, self::ENC);
    }

    /**
     * @param string $phpStr
     * @return string
     */
    public static function strToLower($phpStr) {
        return mb_strtolower($phpStr, self::ENC);
    }


    ////////////// Magic methods, setters & getters

    public function __toString() {
        return $this->getPhpString();
    }

    /**
     * @return string
     */
    public function getPhpString() {
        return $this->s;
    }

    public function __get($name) {
        return ($name == 'length') ? $this->count() : null;
    }

    public function __set($name, $value) {
        if ($name == 'length') {
            $cut = intval($value);
            $this->s = mb_substr($this->s, 0, $cut, self::ENC);
        }
    }

    ////////////// String functions

    /**
     * @param string $str
     * @return self
     */
    public function append($str) {
        return new self($this->s . $str);
    }

    /**
     * @param string $str
     * @return $this
     */
    public function appendMe($str) {
        $this->s .= $str;
        return $this;
    }

    /**
     * Returns part of a string
     *
     * @param int $from
     * @param int $count
     * @return self
     */
    public function substring($from = 0, $count = null) {
        return new self(self::strSubstr($this->s, $from, $count));
    }

    /**
     * Self-modifying version of $this->substring()
     *
     * @param int $from
     * @param int $count
     * @return self
     */
    public function substringMe($from = 0, $count = null) {
        $this->s = self::strSubstr($this->s, $from, $count);
        return $this;
    }

    /**
     * Alias for $this->substring().
     *
     * @param int $from
     * @param int $count
     * @return self
     */
    public function substr($from = 0, $count = null) {
        return $this->substring($from, $count);
    }

    /**
     * Returns uppercased string.
     *
     * @return self
     */
    public function upperCase() {
        return new self(self::strToUpper($this->s));
    }

    /**
     * Self-modifying version of $this->upperCase().
     *
     * @return self
     */
    public function upperCaseMe() {
        $this->s = self::strToUpper($this->s);
        return $this;
    }

    /**
     * Alias for $this->upperCase().
     *
     * @return self
     */
    public function upper() {
        return $this->upperCase();
    }

    /**
     * Alias for $this->upperCaseMe().
     *
     * @return self
     */
    public function upperMe() {
        return $this->upperCaseMe();
    }

    /**
     * Make a string's first character uppercase
     *
     * @return self
     */
    public function upperFirst() {
        $ret = new self($this->s);
        $ret[0] = self::strToUpper($ret[0]);
        return $ret;
    }

    /**
     * Self-modifying version of $this->upperFirst().
     *
     * @return self
     */
    public function upperFirstMe() {
        $this[0] = self::strToUpper($this[0]);
        return $this;
    }

    /**
     * Uppercase the first character of each word in a string.
     *
     * @return self
     */
    public function upperWords() {
        return new self(self::strToUpperWords($this->s));
    }

    /**
     * Self-modifying version of $this->upperWords().
     *
     * @return self
     */
    public function upperWordsMe() {
        $this->s = self::strToUpperWords($this->s);
        return $this;
    }

    /**
     * Make a string lowercase.
     *
     * @return self
     */
    public function lowerCase() {
        return new self(self::strToLower($this->s));
    }

    /**
     * Self-modifying version of $this->lowerCase().
     *
     * @return self
     */
    public function lowerCaseMe() {
        $this->s = self::strToLower($this->s);
        return $this;
    }

    /**
     * Alias for $this->lowerCase().
     *
     * @return self
     */
    public function lower() {
        return $this->lowerCase();
    }

    /**
     * Self-modifying version of $this->lower().
     *
     * @return self
     */
    public function lowerMe() {
        return $this->lowerCaseMe();
    }

    /**
     * Make a string's first character lowercase
     *
     * @return self
     */
    public function lowerFirst() {
        $ret = new self($this->s);
        $ret[0] = self::strToLower($ret[0]);
        return $ret;
    }

    /**
     * Self-modifying version of $this->lowerFirst().
     *
     * @return self
     */
    public function lowerFirstMe() {
        $this[0] = self::strToLower($this[0]);
        return $this;
    }

    /**
     * Convert special characters to HTML entities.
     *
     * @param int $quoteStyle ENT_COMPAT | ENT_IGNORE | ENT_NOQUOTES | ENT_QUOTES
     * @return self
     */
    public function html($quoteStyle = ENT_COMPAT) {
        return new self(htmlspecialchars($this->s, $quoteStyle));
    }

    /**
     * Self-modifying version of $this->html().
     *
     * @param int $quoteStyle
     * @return self
     */
    public function htmlMe($quoteStyle = ENT_COMPAT) {
        $this->s = htmlspecialchars($this->s, $quoteStyle);
        return $this;
    }

    /**
     * Strip whitespace (or other characters) from the beginning and end of a string.
     *
     * @param string $charlist Stripped characters
     * @return self
     */
    public function trim($charlist = null) {
        return new self(trim($this->s, $charlist));
    }

    /**
     * Self-modifying version of $this->trim().
     *
     * @param string $charlist Stripped characters
     * @return self
     */
    public function trimMe($charlist = null) {
        $this->s = trim($this->s, $charlist);
        return $this;
    }

    /**
     * Strip whitespace (or other characters) from the end of a string.
     *
     * @param string $charlist Stripped characters
     * @return self
     */
    public function trimRight($charlist = null) {
        return new self(rtrim($this->s, $charlist));
    }

    /**
     * Self-modifying version of $this->trimRight().
     *
     * @param string $charlist Stripped characters
     * @return self
     */
    public function trimRightMe($charlist = null) {
        $this->s = rtrim($this->s, $charlist);
        return $this;
    }

    /**
     * Strip whitespace (or other characters) from the beginning of a string.
     *
     * @param string $charlist Stripped characters
     * @return self
     */
    public function trimLeft($charlist = null) {
        return new self(ltrim($this->s, $charlist));
    }

    /**
     * Self-modifying version of $this->trimLeft().
     *
     * @param string $charlist Stripped characters
     * @return self
     */
    public function trimLeftMe($charlist = null) {
        $this->s = ltrim($this->s, $charlist);
        return $this;
    }

    /**
     * Split a string by delimeter
     *
     * @param string $delimeter
     * @return array
     */
    public function explode($delimeter = '') {
        return explode($delimeter, $this->s);
    }

    /**
     * One-way string hashing.
     *
     * @param string $salt
     * @return self
     */
    public function crypt($salt = null) {
        return new self(crypt($this->s, $salt));
    }

    /**
     * Self-modifying version of $this->crypt().
     *
     * @param string $salt
     * @return self
     */
    public function cryptMe($salt = null) {
        $this->s = crypt($this->s, $salt);
        return $this;
    }

    /**
     * Calculates the crc32 polynomial of a string.
     *
     * @return self
     */
    public function crc32() {
        return new self(crc32($this->s));
    }

    /**
     * Self-modifying version of $this->crc32().
     *
     * @return self
     */
    public function crc32Me() {
        $this->s = crc32($this->s);
        return $this;
    }

    /**
     * Calculate the md5 hash of a string.
     *
     * @return self
     */
    public function md5() {
        return new self(md5($this->s));
    }

    /**
     * Self-modifying version of $this->md5().
     *
     * @return self
     */
    public function md5Me() {
        $this->s = md5($this->s);
        return $this;
    }

    /**
     * Calculate the sha1 hash of a string.
     *
     * @return self
     */
    public function sha1() {
        return new self(sha1($this->s));
    }

    /**
     * Self-modifying version of $this->sha1().
     *
     * @return self
     */
    public function sha1Me() {
        $this->s = sha1($this->s);
        return $this;
    }

    /**
     * Generate a hash value (message digest)
     *
     * @param string $algorithm Name of selected hashing algorithm (i.e. "md5" (default), "sha256", "haval160,4", etc..)
     * @param bool $raw_output When set to TRUE, outputs raw binary data. FALSE outputs lowercase hexits.
     * @return self
     */
    public function hashify($algorithm = 'md5', $raw_output = false) {
        return new self(hash($algorithm, $this->s, $raw_output));
    }

    /**
     * Self-modifying version of $this->hashify().
     *
     * @param string $algorithm Name of selected hashing algorithm (i.e. "md5" (default), "sha256", "haval160,4", etc..)
     * @param bool $raw_output When set to TRUE, outputs raw binary data. FALSE outputs lowercase hexits.
     * @return self
     */
    public function hashifyMe($algorithm = 'md5', $raw_output = false) {
        $this->s = hash($algorithm, $this->s, $raw_output);
        return $this;
    }

    /**
     * Perform a regular expression match and return matches count.
     *
     * @param string $pattern PCRE pattern
     * @return int
     */
    public function match($pattern) {
        return preg_match($pattern, $this->s);
    }

    /**
     * Perform a regular expression match and return matches array.
     * Perform a global regular expression match.
     *
     * @param string $pattern PCRE pattern
     * @return array
     */
    public function getMatches($pattern) {
        if (preg_match($pattern, $this->s, $matches)) {
            return $matches;
        }
        return array();
    }

    /**
     * Perform a global regular expression match and return matches array.
     *
     * @param string $pattern PCRE pattern
     * @return array
     */
    public function getAllMatches($pattern) {
        if (preg_match_all($pattern, $this->s, $matches)) {
            return $matches;
        }
        return array();
    }

    /**
     * Replace all occurrences of the search string or PCRE with the replacement string,
     * or calling callback for each match (only if $pattern is PCRE).
     *
     * @param string $pattern PCRE string or simply substring to search
     * @param string|\Closure $replacer Replacement string or callback (only if $pattern is PCRE)
     * @param int $mode String::SEARCH_AUTO (default) | String::SEARCH_REGULAR | String::SEARCH_SUBSTR
     * @return self
     */
    public function replace($pattern, $replacer, $mode = self::SEARCH_AUTO) {
        if (empty($pattern)) {
            return $this;
        }
        if ($mode == self::SEARCH_AUTO) {
            $delimeter = ($pattern{0} == '@') ? '\@' : $pattern{0};
            $isRegPattern = '@^' . $delimeter . '.*' . $delimeter . '\w*$@';
            $mode = preg_match($isRegPattern, $pattern) ? self::SEARCH_REGULAR : self::SEARCH_SUBSTR;
        }
        if ($mode == self::SEARCH_REGULAR) {
            if ($replacer instanceof \Closure) {
                return new self(preg_replace_callback($pattern, $replacer, $this->s));
            }
            return new self(preg_replace($pattern, $replacer, $this->s));
        } else {
            return new self(str_replace($pattern, $replacer, $this->s));
        }
    }



    /////////////////// Countable iterface realization /////////////////////////
    /**
     * Returns chars count.
     *
     * @return int
     */
    public function count() {
        return self::strlen($this->s);
    }

    ////////////////////////////////////////////////////////////////////////////

    /**
     * Alias for $this->count().
     *
     * @return int
     */
    public function length() {
        return $this->count();
    }


    ////////// ArrayAccess iterface impl

    public function offsetExists($key) {
        return $key < $this->count();
    }

    public function offsetGet($key) {
        return mb_substr($this->s, $key, 1, self::ENC);
    }

    public function offsetSet($key, $val) {
        if ($this->offsetExists($key)) {
            $this->s = mb_substr($this->s, 0, $key, self::ENC) . $val . mb_substr($this->s, $key + 1, null, self::ENC);
        }
    }

    public function offsetUnset($key) {
        if ($this->offsetExists($key)) {
            $this->s = mb_substr($this->s, 0, $key, self::ENC) . mb_substr($this->s, $key + 1, null, self::ENC);
        }
    }


    ///////// Iterator iterface impl

    /**
     * Moves internal pointer on first char and returns it.
     */
    public function rewind() {
        $this->currentIndex = 0;
    }

    /**
     * Moves internal pointer on next char and returns it.
     */
    public function next() {
        $this->currentIndex++;
    }

    /**
     * Returns true, if current key is valid.
     *
     * @return bool
     */
    public function valid() {
        return isset($this[$this->currentIndex]);
    }

    /**
     * Returns current key
     *
     * @return int
     */
    public function key() {
        return $this->currentIndex;
    }

    /**
     * Returns current char
     *
     * @return self|null
     */
    public function current() {
        return $this->valid() ? $this[$this->currentIndex] : null;
    }

}
