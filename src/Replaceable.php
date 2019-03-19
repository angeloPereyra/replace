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

    public function getTokens($boundary = true)
    {
        /**
         * FROM: $$++key++$$
         * TO: \B\$\$(\w+)\$\$\B
         */

        //$re = '/\B\$\$(\w+)\$\$\B/m';
        //$str = '$$wow$$ amazing $$wowamazing$$  ';
        //
        //preg_match_all($re, $str, $matches, PREG_SET_ORDER, 0);
        //
        //// Print the entire match result
        //var_dump($matches);

        $tokenFormat = $this->resolveTokenFormat(self::KEY_IDENTIFIER);

        $regex = '';

        $prefixStartIndex = 0;
        $prefixEndIndex = strpos($tokenFormat, self::KEY_IDENTIFIER);
        $prefix = substr($tokenFormat, $prefixStartIndex, $prefixEndIndex);
        $regex .= $this->escapeCharacters($prefix);
        $regex .= '(\w+)';

        $suffixStartIndex = $prefixEndIndex + (strlen(self::KEY_IDENTIFIER) - 1);
        $suffixEndIndex = strlen($tokenFormat) - 1;
        $suffix = substr($tokenFormat, $suffixStartIndex, $suffixEndIndex);
        $regex .= $this->escapeCharacters($suffix);

        return $regex;
    }

    private function escapeCharacters($substring)
    {
        $result = '';
        $length = strlen($substring);
        for ($i = 0; $i < $length; $i++){
            $result .= '[' . $substring[$i] . ']';
        }
        return $result;
    }
}
