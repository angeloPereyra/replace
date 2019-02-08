<?php

use PHPUnit\Framework\TestCase;
use Replace\Replaceable;

/**
 * Test cases for Replaceable class
 * 
 * @author Neil Angelo Pereyra <neilpereyra@outlook.ph>
 * @version 0.1.0
 */
class ReplaceableTest extends TestCase
{
    public $testString = 'gitk -p {file_path}';

    public $base = [
        'gitk -p {file_path}',
        '@@php $name = session(\'name\') @@endphp'
    ];

    public function testConstructorWorking()
    {
        $instance = new Replaceable($this->base[0]);
        $this->assertEquals($this->base[0], $instance->base);
    }

    /**
     * @dataProvider provideCanReplaceIdentifier
     */
    public function testCanReplaceIdentifier($value, $expected)
    {
        $instance = new Replaceable($this->base[0]);
        $instance->addLookup('file_path', $value);
        $this->assertEquals($expected, (string)$instance);
    }

    public function provideCanReplaceIdentifier()
    {
        return [
            [
                'src/Replaceable.php',
                'gitk -p src/Replaceable.php'
            ],
            [
                'tests/ReplaceableTest.php',
                'gitk -p tests/ReplaceableTest.php'
            ],
            [
                '@file_name',
                'gitk -p @file_name'
            ]
        ];
    }

    /**
     * @dataProvider provideCanReplaceCustomToken
     */
    public function testCanReplaceCustomToken($key, $value, $tokenFormat, $expected)
    {
        $instance = new Replaceable($this->base[1], $tokenFormat);
        $instance->addLookup($key, $value);
        $this->assertEquals($expected, (string)$instance);
    }

    public function provideCanReplaceCustomToken()
    {
        return [
            [
                'php',
                '<?php',
                '@@++key++',
                '<?php $name = session(\'name\') @@endphp'
            ],
            [
                'endphp',
                '?>',
                function($key) {
                    return '@@' . $key;
                },
                '@@php $name = session(\'name\') ?>'
            ]
        ];
    }

    /**
     * @dataProvider provideCanReplaceUsingStaticHelper
     */
    public function testCanReplaceUsingStaticHelper($lookup, $tokenFormat, $expected)
    {
        $this->assertEquals($expected, Replaceable::parse($this->base[1], $lookup, $tokenFormat));
    }

    public function provideCanReplaceUsingStaticHelper()
    {
        return [
            [
                [
                    'php'       => '<?php',
                    'endphp'    => '?>'
                ],
                '@@++key++',
                '<?php $name = session(\'name\') ?>'
            ],
            [
                [
                    'php'       => '<?php',
                    'endphp'    => '?>',
                    'name'      => '\'employee_1\''
                ],
                function ($key) {
                    if ($key === 'name') {
                        return '\'name\'';
                    }

                    return '@@' . $key;
                },
                '<?php $name = session(\'employee_1\') ?>'
            ]
        ];
    }
}