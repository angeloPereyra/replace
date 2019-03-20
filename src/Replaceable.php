<?php

namespace Replace;
/**
 * Replaces readable defined tokens in a string through native str_replace
 *
 * @author Neil Angelo Pereyra <neilpereyra@outlook.ph>
 * @version 1.0.1
 */
class Replaceable
{

    /**
     * @var string
     */
    const KEY_IDENTIFIER = '++key++';

    /**
     * The base unformatted string. This string should contain the tokens.
     *
     * @var string
     */
    public $base;

    /**
     * Contains the token format definition
     *
     * @var null|closure|string
     */
    protected $tokenFormat;

    /**
     * The resulting string after being parsed
     *
     * @var string
     */
    protected $result;

    /**
     * Dictionary for key value of tokens. The key contains the readable identifier to be replaced by the value
     *
     * @var array
     */
    protected $keyLookups = [];

    /**
     * Replaces the token in the string according to the keylookup given
     *
     * @param string $subject Base unformatted string containing the tokens
     * @param array $keyLookup
     * @param null|callable|string $tokenFormat Accepts a string or closure. For string token format, the substring ++key++ will be the identifier. On the other hand, the closure will be passed with the $key parameter. The token format has a default definition, if passed a null or not specified, similar to passing this string: "{++key++}"
     * @return string
     */
    public static function parse(string $subject, array $keyLookup, $tokenFormat = null)
    {
        $instance = new self($subject, $tokenFormat);
        $instance->setLookup($keyLookup);
        return (string) $instance;
    }

    /**
     * Creates a new StringFormat object
     *
     * @param string $subject Base unformatted string containing the tokens
     * @param null|callable|string $tokenFormat Accepts a string or closure. For string token format, the substring ++key++ will be the identifier. On the other hand, the closure will be passed with the $key parameter. The token format has a default definition, if passed a null or not specified, similar to passing this string: "{++key++}"
     * @return string
     */
    public function __construct(string $subject, $tokenFormat = null)
    {
        $this->base = $subject;
        $this->tokenFormat = $tokenFormat;
    }

    /**
     * @param null $defaultKey
     * @return callable|mixed|null|closure|string
     */
    private function resolveTokenFormat($defaultKey = null)
    {
        if ($this->tokenFormat === null) {
            return '{' . self::KEY_IDENTIFIER . '}';
        }

        if (is_string($this->tokenFormat) === true) {
            return $this->tokenFormat;
        }

        if ($defaultKey === null) {
            return 'callable';
        }

        return call_user_func($this->tokenFormat, $defaultKey);
    }

    /**
     * Returns the token in the string
     *
     * @param string $key
     * @return string
     */
    private function getToken($key)
    {
        return str_replace(self::KEY_IDENTIFIER, $key, $this->resolveTokenFormat($key));
    }

    /**
     * Adds a key to the lookup dictionary
     *
     * @param string $key
     * @param string $value
     * @return void
     */
    public function addLookup($key, $value)
    {
        $this->keyLookups[$key] = $value;
    }

    /**
     * Replaces the lookup values
     *
     * @param array $keyValue
     * @return void
     */
    public function setLookup($keyValue)
    {
        $this->keyLookups = $keyValue;
    }

    /**
     * Magic method override
     *
     * @return string
     */
    public function __toString()
    {
        return $this->parseBase();
    }

    /**
     * Replaces the tokens with their respective value defined in keyLookup property
     *
     * @return string
     */
    private function parseBase()
    {
        $result = $this->base;
        foreach ($this->keyLookups as $key => $value) {
            $result = str_replace($this->getToken($key), $value, $result);
        }

        return $result;
    }

    /**
     * Returns an array that contains the identified tokens on the base string
     *
     * @param bool $wordBoundary
     * @return array
     */
    public function identifyTokens($wordBoundary = true)
    {
        // get the resolved token format
        $tokenFormat = $this->resolveTokenFormat(self::KEY_IDENTIFIER);

        // get regex for identifying tokens
        $regex = $this->getTokenFormatRegex($tokenFormat, self::KEY_IDENTIFIER, $wordBoundary);

        // identify tokens using preg_match_all
        $matches = [];
        preg_match_all($regex, $this->base, $matches, PREG_SET_ORDER, 0);

        // get the token from the preg_match result and return result as a flat array
        $result = [];
        foreach ($matches as $match) {
            $result[] = $match[2];
        }
        return $result;
    }

    /**
     * Returns the regex for the tokenFormat set by user
     *
     * @param $tokenFormat
     * @param $keyIdentifier
     * @param $boundary
     * @return string
     */
    private function getTokenFormatRegex($tokenFormat, $keyIdentifier, $boundary)
    {
        // create empty result variable
        $regex = '';

        // identify the prefix and append the resulting group capture regex
        $prefixStartIndex = 0;
        $prefixEndIndex = strpos($tokenFormat, $keyIdentifier);
        $prefix = substr($tokenFormat, $prefixStartIndex, $prefixEndIndex);
        $regex .= $this->applyGroupCapture($prefix);

        // append group capture for the key identifier itself
        $regex .= '(\w+)';

        //identify the suffix and append the resulting group capture regex
        $identifierLength = strlen($keyIdentifier);
        $suffixStartIndex = $prefixEndIndex + $identifierLength;
        $suffixEndIndex = strlen($tokenFormat) - 1;
        $suffix = substr($tokenFormat, $suffixStartIndex, $suffixEndIndex);
        $regex .= $this->applyGroupCapture($suffix);

        // apply word boundary regex depending on the option set by user
        if ($boundary === true) {
            $regex = $this->applyBoundary($regex);
        }

        //finally, apply delimiter so that the regex can be used with preg_match_all function
        return $this->applyDelimiter($regex);
    }

    /**
     * Applies group capture regex code to a substring
     *
     * @param $substring
     * @return string
     */
    private function applyGroupCapture($substring)
    {
        $result = '';
        $length = strlen($substring);
        for ($i = 0; $i < $length; $i++) {
            $result .= '[' . $substring[$i] . ']';
        }
        return '(' . $result . ')';
    }

    /**
     * Applies word boundary to the regex
     *
     * @param $regex
     * @return string
     */
    private function applyBoundary($regex)
    {
        return '(?<=\s|^)' . $regex . '(?=\s|$)';
    }

    /**
     * Applies delimeter to a regex
     *
     * @param $regex
     * @return string
     */
    private function applyDelimiter($regex)
    {
        $delimeter = ';';
        return $delimeter . $regex . $delimeter;
    }
}
