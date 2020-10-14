<?php
namespace Ds\Tests\Map;

use Ds\Tests\HashableObject;

trait put
{
    public function putDataProvider()
    {
        $o = new \stdClass();

        // put pairs, expected pairs
        return [
            [
                // Test using basic object as key.
                [[$o, 1]],
                [[$o, 1]]
            ],
            [
                // Test using string as key.
                [['a', 1]],
                [['a', 1]]
            ],
            [
                // Test that numeric strings are not treated as int.
                [[0, 0], ['0', 1]],
                [[0, 0], ['0', 1]]
            ],
            [
                // Test that a null key is valid
                [[null, null], [null, null]],
                [[null, null]]
            ],
            [
                // Test that -0.0 and 0.0 map to the same key
                [[-0.0, -0.0], [0.0, 0.0]],
                [[-0.0, 0.0]],
            ]
        ];
    }

    public function putHashableDataProvider()
    {
        // Two objects with the same hash code and equals.
        $h1 = new HashableObject(1);
        $h2 = new HashableObject(1);

        // put pairs, expected pairs
        return [
            // // Test that two equivalent hashable objects are the same.
            [
                [[$h1, 1], [$h2, 2]],
                [          [$h2, 2]]
            ],
        ];
    }

    /**
     * @dataProvider putDataProvider
     */
    public function testPut(array $pairs, array $expected)
    {
        $instance = $this->getInstance();

        foreach ($pairs as $pair) {
            $instance->put($pair[0], $pair[1]);
        }

        foreach ($expected as $pair) {
            $this->assertEquals($pair[1], $instance->get($pair[0]));
        }

        $this->assertCount(count($expected), $instance);
    }

    public function testPutMany()
    {
        $instance = $this->getInstance();

        for ($i = 0; $i < self::MANY; $i++) {
            $instance->put(rand(), rand());
        }

        $this->assertEquals(self::MANY, count($instance));
        $this->assertEquals(self::MANY, count($instance->toArray()));
    }

    /**
     * @dataProvider putHashableDataProvider
     */
    public function testPutHashable(array $pairs, array $expected)
    {
        $this->testPut($pairs, $expected);
    }

    public function testArrayAccessPut()
    {
        $instance = $this->getInstance(['a' => 1]);
        $instance['a'] = 2;
        $this->assertToArray(['a' => 2], $instance);
    }

    public function testArrayAccessPutByMethod()
    {
        $instance = $this->getInstance(['a' => 1]);
        $instance->offsetSet('a', 2);
        $this->assertToArray(['a' => 2], $instance);
    }

    public function testArrayAccessPutByReference()
    {
        $instance = $this->getInstance(['a' => [1]]);
        $instance['a'][0] = 2;

        $this->assertToArray(['a' => [2]], $instance);
    }

    public function testMapPutCircularReference()
    {
        $a = $this->getInstance();
        $b = $this->getInstance();

        $a->put("B", $b);
        $a->put("A", $a);
        $b->put("B", $b);
        $b->put("A", $a);

        $this->assertToArray(["B" => $b, "A" => $a], $a);
        $this->assertToArray(["B" => $b, "A" => $a], $b);
    }

    public function testPutKeyAsReference()
    {
        $map = $this->getInstance();

        $key = ['a'];
        $ref = &$key;

        $map->put($key, 1);
        $this->assertEquals(1, $map->get($key));
        $this->assertEquals(1, $map->get($ref));

        $this->assertTrue($map->hasKey($key));
        $this->assertTrue($map->hasKey($ref));
        $this->assertTrue($map->hasKey(['a']));

        $map->put($ref, 2);
        $this->assertEquals(2, $map->get($ref));
        $this->assertEquals(2, $map->get($key));

        $this->assertTrue($map->hasKey($key));
        $this->assertTrue($map->hasKey($ref));
        $this->assertTrue($map->hasKey(['a']));

        // Check that the variable that was a reference is still
        $ref['x'] = 10;
        $this->assertEquals(10, $key['x']);
    }

    public function testArrayAccessPutKeyAsReference()
    {
        $map = $this->getInstance();

        $key = ['a'];
        $ref = &$key;

        $map[$key] = 1;
        $this->assertEquals(1, $map->get($key));
        $this->assertEquals(1, $map->get($ref));

        $this->assertTrue($map->hasKey($key));
        $this->assertTrue($map->hasKey($ref));
        $this->assertTrue($map->hasKey(['a']));

        $map[$ref] = 2;
        $this->assertEquals(2, $map->get($ref));
        $this->assertEquals(2, $map->get($key));

        $this->assertTrue($map->hasKey($key));
        $this->assertTrue($map->hasKey($ref));
        $this->assertTrue($map->hasKey(['a']));

        // Check that the variable that was a reference is still
        $ref['x'] = 10;
        $this->assertEquals(10, $key['x']);
    }
}
