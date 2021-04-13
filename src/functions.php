<?php

function t(...$args): void
{
    $isCli = PHP_SAPI === 'cli';
    if (!$isCli && !in_array('Content-type:text/html;charset=utf-8', headers_list())) {
        header('Content-type:text/html;charset=utf-8');
    }
    $filter = function (&$value) use (&$filter) {
        switch (gettype($value)) {
            case 'NULL':
                $value = 'null';
                break;
            case 'boolean':
                if ($value === true) {
                    $value = 'true';
                } else {
                    $value = 'false';
                }
                break;
            case 'string':
                $value = "'{$value}'";
                break;
            case 'array':
                array_walk($value, $filter);
                break;
        }
    };
    foreach ($args as $value) {
        $filter($value);
        if ($isCli) {
            print_r($value);
            echo PHP_EOL;
        } else {
            echo '<pre>';
            print_r($value);
            echo '</pre>';
        }
    }
}

function tt(...$args): void
{
    call_user_func_array('t', $args);
    die();
}
