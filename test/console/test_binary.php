#!/usr/bin/env php
<?php
    $codes = [];
    for ($i = 123; $i < 255; $i++) {
        $c = json_encode(chr($i));
        $u = (substr($c, 0, 3) == '"\\u');
        $u1 = empty($c);
        echo $c."\t".(($u | $u1) ? '+' : '-')."\n";

        if ($u | $u1) {
            $codes[] = $i;
        }
    }

    echo "\n\n".implode(',', $codes)."\n";
?>