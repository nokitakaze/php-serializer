<?php


    namespace NokitaKaze\Serializer\Test;

    use NokitaKaze\Serializer\ISerializable;

    class SerializableUnsafeObject implements ISerializable {
        protected $_foo = 'bar';
        public $key = 'value';
        public $wind = 'change';

        function ns_serialize() {
            return (object) [
                'type' => ISerializable::TYPE,
                'class_FQDN' => get_class($this),
                'value' => json_encode(['key' => $this->key, 'wind' => 'd\'etat']),
            ];
        }

        /**
         * @param \NokitaKaze\Serializer\Variant $variant
         *
         * @return object|void
         */
        static function ns_unserialize($variant) {
            $object = new SerializableObject();
            $object->key = json_decode($variant->value)->key;

            /** @noinspection PhpInconsistentReturnPointsInspection */
            return $object;
        }

        function isFooBar() {
            return ($this->_foo == 'bar');
        }

    }

?>