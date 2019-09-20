<?php


namespace App\Tests;


use PHPUnit\Framework\TestCase;

class SimpleTest extends TestCase
{

    public function test()
    {
        $this->assertEquals(10, 7+3);

        $value = true;
        $this->assertTrue($value);

        $array = ['key' => 'value'];
        $this->assertArrayHasKey('key', $array);
        $this->assertEquals('value', $array['key']);
        $this->assertCount(1, $array);

    }

}