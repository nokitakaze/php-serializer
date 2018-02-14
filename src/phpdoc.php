<?php

    namespace NokitaKaze\Serializer;

    /**
     * Interface Variant
     * @package NokitaKaze\Serializer
     *
     * @property integer $type
     * @property mixed   $value
     */
    interface Variant {
    }

    /**
     * Interface Variant
     * @package NokitaKaze\Serializer
     *
     * @property Variant[] $keys
     * @property Variant[] $value
     */
    interface KeyValueVariant extends Variant {
    }

    /**
     * Interface Variant
     * @package NokitaKaze\Serializer
     *
     * @property string $class_FQDN
     */
    interface SerializableVariant extends Variant {
    }

?>