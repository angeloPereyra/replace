<?php

/**
 * Replaces readable defined tokens in a string through native str_replace
 * 
 * @author Neil Angelo Pereyra <neilpereyra@outlook.ph>
 * @version 0.1.0
 */
class Replaceable
{
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
     * Undocumented function
     *
     * @param [type] $format
     * @param [type] $keyLookup
     * @param [type] $tokenFormat
     * @return void
     */
    public static function parse($format, $keyLookup, $tokenFormat = null)
    {
        $instance = new self($format, $tokenFormat);
        $instance->setLookup($keyLookup);
        return (string) $instance;
    }

    /**
     * Creates a new StringFormat object
     *
     * @param string $format Base unformatted string containing the tokens
     * @param null|closure|string $tokenFormat Accepts a string or closure. For string token format, the substring ++key++ will be the identifier. On the other hand, the closure will be passed with the $key parameter. The token format has a default definition, if passed a null or not specified, similar to passing this string: "{++key++}"
     */
    public function __construct(string $format, $tokenFormat = null)
    {
        $this->base = $format;
        $this->tokenFormat = $tokenFormat;
    }

    /**
     * Magic method override
     *
     * @param string $key
     * @param string $value
     */
    public function __set($key, $value)
    {
        $this->addLookup($key, $value);
    }
    
    /**
     * Returns the token in the string
     *
     * @param [type] $key
     * @return void
     */
    private function getToken($key)
    {
        if ($this->tokenFormat === null) {
            return '{' . $key . '}';
        }

        if (is_callable($this->tokenFormat) === true) {
            return call_user_func($this->tokenFormat);
        }

        return str_replace('++key++', $key, $this->tokenFormat);
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
}