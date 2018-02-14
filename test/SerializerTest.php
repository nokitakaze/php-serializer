<?php

    namespace NokitaKaze\Serializer\Test;

    use NokitaKaze\Serializer;
    use NokitaKaze\Serializer\ISerializable;
    use PHPUnit\Framework\TestCase;

    class SerializerTest extends TestCase {
        function dataSerialize() {
            return [
                [1],
                [1.0],
                [1.0000000001],
                [-1.1345],
                [0.987654987],
                [INF],
                [NAN],
                [""],
                ["Lorem ipsum"],
                [null],
                [[]],
                [[0 => 1, 1 => null, 2 => true]],
                [[1.123, "", false]],
                [['a' => 1.123, "", false, -INF, "0e12346789"]],
                [[2 => 0, 10 => 1, 5 => 4]],
                [(object) ["key" => 'value', 'normies' => 'nyanpasu']],
                [(object) []],
                [new SerializableObject()],
            ];
        }

        /**
         * @param mixed $value
         *
         * @dataProvider dataSerialize
         */
        function testSerialize($value) {
            $serialized = Serializer\Serializer::serialize($value);
            $this->assertInternalType('string', gettype($serialized));
            $this->assertFalse(Serializer\Serializer::is_binary_string($serialized),
                'Serialized string contain binary characters');
            $this->assertJson($serialized);

            $data = Serializer\Serializer::unserialize($serialized, $is_value);
            $this->assertTrue($is_value);
            $this->assertEqualsDepth($value, $data);
        }

        function assertEqualsDepth($expected, $actual) {
            if (is_null($expected)) {
                $this->assertNull($actual);

                return;
            }
            $this->assertInternalType(gettype($expected), $actual);
            if (is_double($expected)) {
                if (is_nan($expected)) {
                    $this->assertNan($actual);

                    return;
                }

                $this->assertEquals($expected, $actual);
            } elseif (is_int($expected) or is_string($expected)) {
                $this->assertEquals($expected, $actual);
            } elseif (is_array($expected)) {
                $k1 = array_keys($expected);
                $k2 = array_keys($actual);
                $this->assertEquals($k1, $k2);
                foreach ($k1 as $key) {
                    $this->assertEqualsDepth($expected[$key], $actual[$key]);
                }
            } elseif (is_object($expected) and ($expected instanceof ISerializable)) {
                // @hint Два объекта сравнить нельзя
                $this->assertNotEquals(spl_object_hash($expected), spl_object_hash($actual));
                if (!($expected instanceof SerializableObject)) {
                    return;
                }
                $this->assertTrue($expected->isFooBar());
                $this->assertEquals('change', $expected->wind);
            } elseif (is_object($expected)) {
                $this->assertNotEquals(spl_object_hash($expected), spl_object_hash($actual));
                $k1 = array_keys(get_object_vars($expected));
                $k2 = array_keys(get_object_vars($actual));
                $this->assertEquals($k1, $k2);
                foreach ($k1 as $key) {
                    $this->assertEqualsDepth($expected->{$key}, $actual->{$key});
                }
            } elseif (is_resource($expected)) {
                // @todo придумать
            } elseif (is_bool($expected)) {
                if ($expected) {
                    $this->assertTrue($actual);
                } else {
                    $this->assertFalse($actual);
                }
            }
        }

        function dataDiffSerializeForUnsafeUnserializableClass() {
            return [
                [true],
                [false],
            ];
        }

        /**
         * @param boolean $safe
         *
         * @dataProvider dataDiffSerializeForUnsafeUnserializableClass
         */
        function testDiffSerializeForUnsafeUnserializableClass($safe) {
            $object = clone $this;
            $object_new = Serializer\Serializer::unserialize(Serializer\Serializer::serialize($object), $is_valid, $safe);
            $this->assertTrue($is_valid);
            // Будет скастован в key-value class
            $this->assertNotNull($object_new);
            $this->assertTrue($object_new instanceof \stdClass);
        }

        function testSafeSerializeForUnsafeSerializableClass() {
            $object = new SerializableUnsafeObject();
            $object_new = Serializer\Serializer::unserialize(Serializer\Serializer::serialize($object), $is_valid, true);
            $this->assertTrue($is_valid);
            $this->assertNull($object_new);
        }

        function testUnsafeSerializeForUnsafeSerializableClass() {
            $object = new SerializableUnsafeObject();
            $object_new = Serializer\Serializer::unserialize(Serializer\Serializer::serialize($object), $is_valid, false);
            $this->assertTrue($is_valid);
            $this->assertNotNull($object_new);
            $this->assertTrue($object_new instanceof SerializableUnsafeObject);
        }

        function testIs_binary_string() {
            $this->assertTrue(Serializer\Serializer::is_binary_string(chr(0)));
            $this->assertTrue(Serializer\Serializer::is_binary_string(chr(254)));
            $this->assertFalse(Serializer\Serializer::is_binary_string(chr(255)));
        }

        /**
         * @doc https://bugs.php.net/bug.php?id=53727
         */
        function testBugCheck53727() {
            require_once __DIR__.'/ClassesFor53727.php';
            $this->assertTrue(is_subclass_of('ChildClass', 'MyInterface'));
            $this->assertTrue(defined('ChildClass::TEST_CONSTANT'));
            $this->assertTrue(is_subclass_of('ParentClass', 'MyInterface'));
            $this->assertTrue(defined('ParentClass::TEST_CONSTANT'));
        }

        function testDepth() {
            $input = [];
            for ($i = 0; $i < 5; $i++) {
                $input = [$input];
            }
            $output = Serializer\Serializer::unserialize(Serializer\Serializer::serialize($input, 4), $is_valid);
            $this->assertInternalType('array', $output);
            $this->assertInternalType('array', $output[0]);
            $this->assertInternalType('array', $output[0][0]);
            $this->assertNull($output[0][0][0]);
        }

        function testMalformedInput() {
            $output = Serializer\Serializer::unserialize('fe94nsr89', $is_valid);
            $this->assertFalse($is_valid);
            $this->assertNull($output);
        }

        function testWithoutFQDN() {
            $output = Serializer\Serializer::unserialize(\json_encode([
                'type' => ISerializable::TYPE,
                'class' => get_class(new SerializableObject()),
            ]), $is_valid);
            $this->assertFalse($is_valid);
            $this->assertNull($output);
        }

        function testMalformedClassName() {
            $output = Serializer\Serializer::unserialize(\json_encode([
                'type' => ISerializable::TYPE,
                'class_FQDN' => 'NokitaKaze\Serializer\Test'.mt_rand(1000000, 9999999),
            ]), $is_valid);
            $this->assertFalse($is_valid);
            $this->assertNull($output);
        }

        function testMalformedClassSerialization() {
            $output = Serializer\Serializer::unserialize(\json_encode([
                'type' => ISerializable::TYPE,
                'class_FQDN' => get_class($this),
            ]), $is_valid);
            $this->assertFalse($is_valid);
            $this->assertNull($output);
        }

        function testMalformedType() {
            $output = Serializer\Serializer::unserialize(\json_encode([
                'type' => 100500,
            ]), $is_valid);
            $this->assertFalse($is_valid);
            $this->assertNull($output);
        }

        function testSerializeResource() {
            $resource = tmpfile();
            $json = Serializer\Serializer::serialize($resource);
            $this->assertInternalType('string', $json);
            Serializer\Serializer::unserialize($json, $is_value);
        }

        function testSerializeClosure() {
            $closure = function () { };
            $json = Serializer\Serializer::serialize($closure);
            $this->assertInternalType('string', $json);
            Serializer\Serializer::unserialize($json, $is_value);
        }
    }

?>