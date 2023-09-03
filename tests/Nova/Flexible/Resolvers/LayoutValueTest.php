<?php

namespace NovaThinKit\Tests\Nova\Flexible\Resolvers;

use NovaThinKit\Nova\Flexible\Resolvers\LayoutValue;
use NovaThinKit\Tests\TestCase;

class LayoutValueTest extends TestCase
{
    /** @test */
    public function magic_get()
    {
        $layoutValue = new LayoutValue([
            'foo' => 'bar',
            'baz' => [
                'qux' => 'quex',
            ],
        ]);

        $this->assertEquals('bar', $layoutValue->foo);
        $this->assertEquals('quex', $layoutValue->baz['qux']);
    }

    /** @test */
    public function get_attribute()
    {
        $layoutValue = new LayoutValue([
            'attributes' => [
                'foo' => 'bar',
                'baz' => [
                    'qux' => 'quex',
                ],
            ],
        ]);

        $this->assertEquals('bar', $layoutValue->attribute('foo'));
        $this->assertEquals('quex', $layoutValue->attribute('baz.qux'));
    }

    /** @test */
    public function get_layouts_attribute()
    {
        $layoutValue = new LayoutValue([
            'attributes' => [
                'foo' => 'bar',
                'baz' => [
                    'qux' => [
                        [
                            'fake' => 'structure',
                        ],
                        [
                            'layout'     => 'foo_home',
                            'key'        => '12qwas12',
                            'attributes' => [
                                'foo' => 'baz',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $otherLayouts = $layoutValue->layoutsAttribute('baz.qux');
        $otherLayout  = $otherLayouts[0];

        $this->assertInstanceOf(LayoutValue::class, $otherLayout);
        $this->assertEquals('foo_home', $otherLayout->layout);
        $this->assertEquals('12qwas12', $otherLayout->key);
        $this->assertEquals('baz', $otherLayout->attribute('foo'));


        $this->assertIsArray($layoutValue->layoutsAttribute('baz.not_exists'));
        $this->assertEmpty($layoutValue->layoutsAttribute('baz.not_exists'));
    }

    /** @test */
    public function to_string()
    {
        $data = [
            'attributes' => [
                'foo' => 'bar',
                'baz' => [
                    'qux' => 'quex',
                ],
            ],
        ];
        $layoutValue = new LayoutValue($data);

        $serialized = (string)$layoutValue;

        $this->assertEquals(json_encode($data), $serialized);
    }
}
