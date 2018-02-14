<?php

    require_once __DIR__.'/../../vendor/autoload.php';

    class BenchGeneral {
        static $_values = null;
        /**
         * @var string[]
         */
        static $_values_reverse = null;

        public function provideStrings() {
            $very_big_text_800kb = '';
            for ($i = 0; $i < 200000; $i++) {
                $n = mt_rand(1, 7);
                $word = '';
                for ($j = 0; $j < $n; $j++) {
                    $word .= mt_rand(ord('a'), ord('z'));
                }
                $very_big_text_800kb .= $word.', ';
            }

            $very_big_text_2mb = '';
            for ($i = 0; $i < 500000; $i++) {
                $n = mt_rand(1, 7);
                $word = '';
                for ($j = 0; $j < $n; $j++) {
                    $word .= mt_rand(ord('a'), ord('z'));
                }
                $very_big_text_2mb .= $word.', ';
            }

            $big_binary = '';
            for ($i = 0; $i < 1024000; $i++) {
                $big_binary .= chr(mt_rand(0, 255));
            }

            self::$_values = [
                ['Hello World!'],
                [''],
                [[]],
                [123],
                [[123, 0.45679]],
                [$very_big_text_800kb],
                [$very_big_text_2mb],
                [$big_binary],
            ];

            $output = [];
            for ($i = 0; $i < count(self::$_values); $i++) {
                $output[] = ['value' => $i];
            }

            return $output;
        }

        public function provideStringsUnserialize() {
            $output = $this->provideStrings();
            self::$_values_reverse = [];
            foreach (self::$_values as $value) {
                self::$_values_reverse[] = NokitaKaze\Serializer\Serializer::unserialize($value, $is_valid);
                if (!$is_valid) {
                    throw new \Exception('Can not unserialize value');
                }
            }

            return $output;
        }

        /**
         * @param array $params
         * @Revs(20)
         * @Iterations(10)
         * @ParamProviders({"provideStrings"})
         */
        function benchSerialize($params) {
            NokitaKaze\Serializer\Serializer::serialize(self::$_values[$params['value']]);
        }

        /**
         * @param array $params
         * @Revs(20)
         * @Iterations(10)
         * @ParamProviders({"provideStringsUnserialize"})
         */
        function benchUnserialize($params) {
            NokitaKaze\Serializer\Serializer::unserialize(self::$_values_reverse[$params['value']], $is_valid);
        }

        /**
         * @param array $params
         * @Revs(20)
         * @Iterations(10)
         * @ParamProviders({"provideStrings"})
         */
        function benchBoxSerialize($params) {
            \serialize(self::$_values[$params['value']]);
        }

        /**
         * @param array $params
         * @Revs(20)
         * @Iterations(10)
         * @ParamProviders({"provideStringsUnserialize"})
         */
        function benchBoxUnserialize($params) {
            @\unserialize(self::$_values_reverse[$params['value']]);
        }
    }

?>