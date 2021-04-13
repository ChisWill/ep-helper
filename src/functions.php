<?php

function t(...$args): void
{
    $isCli = PHP_SAPI === 'cli';
    if (!$isCli && !in_array('Content-type:text/html;charset=utf-8', headers_list())) {
        header('Content-type:text/html;charset=utf-8');
    }
    foreach ($args as $value) {
        switch ($value) {
            case $value === null:
                $value = '"null"';
                break;
            case $value === false:
                $value = '"false"';
                break;
            case $value === true:
                $value = '"true"';
                break;
        }
        if ($isCli) {
            print_r($value);
            echo PHP_EOL;
        } else {
            echo '<xmp>';
            print_r($value);
            echo '</xmp>';
        }
    }
}

function tt(...$args): void
{
    call_user_func_array('t', $args);
    die();
}
