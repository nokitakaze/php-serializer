<?php

    namespace NokitaKaze\Serializer;

    class Serializer {
        const TYPE_INT = 0;
        const TYPE_DOUBLE = 1;
        const TYPE_STRING = 2;
        const TYPE_ARRAY_INDEX = 3;
        const TYPE_ARRAY_KEY_VALUE = 4;
        const TYPE_OBJECT_KEY_VALUE = 5;
        const TYPE_SERIALIZABLE = ISerializable::TYPE;
        const TYPE_RESOURCE = 7;
        const TYPE_BOOLEAN = 7;
        const TYPE_NULL = 8;

        const DEFAULT_DEPTH = 255;

        /**
         * @param mixed   $data
         * @param integer $depth
         *
         * @return string
         */
        public static function serialize($data, $depth = self::DEFAULT_DEPTH) {
            return json_encode(static::serialize_to_variant($data, $depth));
        }

        /**
         * @param mixed   $text
         * @param boolean $is_valid
         * @param boolean $safe
         *
         * @return mixed
         */
        public static function unserialize($text, &$is_valid, $safe = true) {
            $is_valid = true;
            if ($text === 'null') {
                return null;
            }
            $data = json_decode($text);
            if (is_null($data)) {
                $is_valid = false;

                return null;
            }

            try {
                $object = static::unserialize_variant_to_value($data, $safe);
            } catch (UnserializeException $e) {
                $is_valid = false;

                return null;
            }

            return $object;
        }

        /**
         * @param mixed   $data
         * @param integer $depth
         *
         * @return Variant|object|boolean|null
         */
        public static function serialize_to_variant($data, $depth = self::DEFAULT_DEPTH) {
            if ($depth < 0) {
                return null;
            }
            if (is_double($data)) {
                return static::serialize_double($data);
            } elseif (is_int($data)) {
                return static::serialize_int($data);
            } elseif (is_string($data)) {
                return static::serialize_string($data);
            } elseif (is_array($data) and static::is_indexed_array($data)) {
                return static::serialize_indexed_array($data, $depth - 1);
            } elseif (is_array($data)) {
                return static::serialize_array_key_value($data, $depth - 1);
            } elseif (is_object($data) and ($data instanceof ISerializable)) {
                /**
                 * @var ISerializable $data
                 */
                return $data->ns_serialize();
            } elseif (is_object($data) and is_callable($data)) {
                return null;
            } elseif (is_object($data)) {
                return static::serialize_object_key_value($data);
            } elseif (is_resource($data)) {
                return static::serialize_int(intval($data));
            } elseif (is_bool($data)) {
                return $data;
            } elseif (is_null($data)) {
                return null;
            } else {
                // @codeCoverageIgnoreStart
                return null;
                // @codeCoverageIgnoreEnd
            }
        }

        protected static $_binary_codes = [
            0, 1, 2, 3, 4, 5, 6, 7, 11, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 128, 129, 130,
            131, 132, 133, 134, 135, 136, 137, 138, 139, 140, 141, 142, 143, 144, 145, 146, 147, 148, 149, 150, 151, 152, 153,
            154, 155, 156, 157, 158, 159, 160, 161, 162, 163, 164, 165, 166, 167, 168, 169, 170, 171, 172, 173, 174, 175, 176,
            177, 178, 179, 180, 181, 182, 183, 184, 185, 186, 187, 188, 189, 190, 191, 192, 193, 194, 195, 196, 197, 198, 199,
            200, 201, 202, 203, 204, 205, 206, 207, 208, 209, 210, 211, 212, 213, 214, 215, 216, 217, 218, 219, 220, 221, 222,
            223, 224, 225, 226, 227, 228, 229, 230, 231, 232, 233, 234, 235, 236, 237, 238, 239, 240, 241, 242, 243, 244, 245,
            246, 247, 248, 249, 250, 251, 252, 253, 254,
        ];

        protected static $_binary_codes_flip = null;

        /**
         * @param string $value
         *
         * @return boolean
         */
        public static function is_binary_string($value) {
            if (is_null(self::$_binary_codes_flip)) {
                self::$_binary_codes_flip = array_flip(self::$_binary_codes);
            }

            // @todo Оптимизировать через > & <
            for ($i = 0; $i < strlen($value); $i++) {
                $ord = ord(substr($value, $i, 1));
                if (isset(self::$_binary_codes_flip[$ord])) {
                    return true;
                }
            }

            return false;
        }

        /**
         * @param double $value
         *
         * @return Variant|object
         */
        public static function serialize_double($value) {
            return (object) [
                'type' => self::TYPE_DOUBLE,
                'value' => serialize($value),
            ];
        }

        /**
         * @param integer $value
         *
         * @return Variant|object
         */
        public static function serialize_int($value) {
            return (object) [
                'type' => self::TYPE_INT,
                'value' => $value,
            ];
        }

        /**
         * @param array   $value
         * @param integer $depth
         *
         * @return Variant|object
         */
        public static function serialize_indexed_array(array $value, $depth) {
            $output = [];
            foreach ($value as $sub_value) {
                $output[] = self::serialize_to_variant($sub_value, $depth - 1);
            }

            return (object) [
                'type' => self::TYPE_ARRAY_INDEX,
                'value' => $output,
            ];
        }

        /**
         * @param array   $value
         * @param integer $depth
         *
         * @return Variant|object
         */
        public static function serialize_array_key_value(array $value, $depth) {
            $output = [];
            $keys = [];
            foreach ($value as $key => $sub_value) {
                $keys[] = self::serialize_to_variant($key, $depth - 1);
                $output[] = self::serialize_to_variant($sub_value, $depth - 1);
            }

            return (object) [
                'type' => self::TYPE_ARRAY_KEY_VALUE,
                'value' => $output,
                'keys' => $keys,
            ];
        }

        /**
         * @param \stdClass|object $value
         *
         * @return Variant|object
         */
        public static function serialize_object_key_value($value) {
            $output = [];
            $keys = [];

            foreach (get_object_vars($value) as $key => $value) {
                $keys[] = self::serialize_to_variant($key);
                $output[] = self::serialize_to_variant($value);
            }

            return (object) [
                'type' => self::TYPE_OBJECT_KEY_VALUE,
                'value' => $output,
                'keys' => $keys,
            ];
        }

        /**
         * @param string $value
         *
         * @return Variant|object
         */
        public static function serialize_string($value) {
            return (object) [
                'type' => self::TYPE_STRING,
                'value' => base64_encode(gzcompress($value)),
            ];
        }

        /**
         * @param array $input
         *
         * @return boolean
         */
        public static function is_indexed_array(array $input) {
            if (empty($input)) {
                return true;
            }

            $keys = array_keys($input);
            for ($i = 0; $i < count($keys); $i++) {
                if ($keys[$i] !== $i) {
                    return false;
                }
            }

            return true;
        }

        /**
         * @param \stdClass|Variant $variant
         *
         * @return string
         */
        public static function unserialize_string($variant) {
            return gzuncompress(base64_decode($variant->value));
        }

        /**
         * @param \stdClass|Variant $variant
         * @param boolean           $safe
         *
         * @return array
         * @throws UnserializeException
         */
        public static function unserialize_array_index($variant, $safe) {
            $output = [];
            foreach ($variant->value as $sub_value) {
                $output[] = self::unserialize_variant_to_value($sub_value, $safe);
            }

            return $output;
        }

        /**
         * @param \stdClass|KeyValueVariant $variant
         * @param boolean                   $safe
         *
         * @return array
         * @throws UnserializeException
         */
        public static function unserialize_array_key_value($variant, $safe) {
            $count = count($variant->keys);
            if ($count != count($variant->value)) {
                throw new UnserializeException(sprintf('Array keys count (%d) does not equal value count (%d)',
                    $count,
                    count($variant->value)
                ), 2);
            }

            $output = [];
            for ($i = 0; $i < count($variant->keys); $i++) {
                $output[self::unserialize_variant_to_value($variant->keys[$i], $safe)]
                    = self::unserialize_variant_to_value($variant->value[$i], $safe);
            }

            return $output;
        }

        /**
         * @param SerializableVariant|boolean|null|object $variant
         * @param boolean                                 $safe
         *
         * @return mixed
         * @throws UnserializeException
         */
        public static function unserialize_object($variant, $safe) {
            if (!isset($variant->class_FQDN)) {
                throw new UnserializeException('Variant does not contain field with FQDN', 3);
            }
            if (!class_exists($variant->class_FQDN)) {
                throw new UnserializeException(sprintf('Class "%s" does not exist', $variant->class_FQDN), 4);
            }
            if (!is_subclass_of($variant->class_FQDN, 'NokitaKaze\\Serializer\\ISerializable')) {
                throw new UnserializeException(sprintf('Class "%s" does not implement ISerializable', $variant->class_FQDN), 5);
            }
            if ($safe and !is_subclass_of($variant->class_FQDN, 'NokitaKaze\\Serializer\\ISafeSerializable')) {
                // Не поддерживает safe-десериализацию
                return null;
            }

            /**
             * @var ISerializable $class
             */
            $class = $variant->class_FQDN;

            return $class::ns_unserialize($variant);
        }

        /**
         * @param Variant|boolean|null|object $variant
         * @param boolean                     $safe
         *
         * @return mixed
         * @throws UnserializeException
         */
        public static function unserialize_variant_to_value($variant, $safe) {
            if (is_null($variant)) {
                return null;
            } elseif (is_bool($variant)) {
                return $variant;
            } else {
                switch ($variant->type) {
                    case self::TYPE_INT:
                        return $variant->value;
                    case self::TYPE_DOUBLE:
                        return \unserialize($variant->value);
                    case self::TYPE_STRING:
                        return self::unserialize_string($variant);
                    case self::TYPE_ARRAY_INDEX:
                        return self::unserialize_array_index($variant, $safe);
                    case self::TYPE_ARRAY_KEY_VALUE:
                        return self::unserialize_array_key_value($variant, $safe);
                    case self::TYPE_OBJECT_KEY_VALUE:
                        return (object) self::unserialize_array_key_value($variant, $safe);
                    case self::TYPE_SERIALIZABLE:
                        return self::unserialize_object($variant, $safe);
                    default:
                        throw new UnserializeException('Malformed type '.$variant->type, 1);
                }
            }
        }

    }

    ?>