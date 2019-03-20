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
    public $base = [
        'gitk -p {file_path}',
        '@@php $name = session(\'name\') @@endphp',
        'Hi. I think you are _{adjective}_',
        '$url = \'$$select_url\';'
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

    /**
     * @dataProvider provideCanIdentifyTokensCorrectly
     */
    public function testCanIdentifyTokensCorrectly($dataIndex, $tokenFormat, $wordBoundary, $expected)
    {
        $instance = new Replaceable($this->base[$dataIndex], $tokenFormat);
        $actual = $instance->identifyTokens($wordBoundary);
        $this->assertEquals($expected, $actual);
    }

    public function provideCanIdentifyTokensCorrectly()
    {
        return [
            [
                0,
                null,
                true,
                ['file_path']
            ],
            [
                2,
                null,
                false,
                ['adjective']
            ],
            [
                1,
                '@@' . Replaceable::KEY_IDENTIFIER,
                true,
                ['php', 'endphp']
            ],
            [
                3,
                '$$' . Replaceable::KEY_IDENTIFIER,
                false,
                ['select_url']
            ]
        ];
    }

    /**
     * @dataProvider provideCanIdentifyTokensCorrectlyOnMultiline
     */
    public function testCanIdentifyTokensCorrectlyOnMultiline($base, $tokenFormat, $wordBoundary, $expected)
    {
        $instance = new Replaceable($base, $tokenFormat);
        $actual = $instance->identifyTokens($wordBoundary);
        $this->assertEquals($expected, $actual);
    }

    public function provideCanIdentifyTokensCorrectlyOnMultiline()
    {
        $data1 = file_get_contents(__DIR__ . '/files/1.txt');
        $data2 = file_get_contents(__DIR__ . '/files/2.txt');

        return [
            [
                $data1,
                null,
                true,
                ['London', 'is', 'down', 'fair', 'lady']
            ],
            [
                $data1,
                '@' . Replaceable::KEY_IDENTIFIER,
                false,
                ['bridge', 'down', 'falling']
            ],
            [
                $data2,
                '$$' . Replaceable::KEY_IDENTIFIER,
                true,
                ['comment']
            ],
            [
                $data2,
                '$$' . Replaceable::KEY_IDENTIFIER,
                false,
                ['comment', 'select_url', 'other_scripts']
            ]
        ];
    }
}