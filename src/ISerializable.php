<?php

    namespace NokitaKaze\Serializer;

    interface ISerializable {
        const TYPE = 6;

        /**
         * @return Variant
         */
        public function ns_serialize();

        /**
         * @param Variant $variant
         *
         * @return object
         * @throws UnserializeException
         */
        public static function ns_unserialize($variant);
    }

?>