<?php

function t(...$args): void
{
    $isCli = PHP_SAPI === 'cli';
    if (!$isCli && !array_filter(headers_list(), fn ($value): bool => strpos(strtolower($value), 'content-type') === 0)) {
        header('Content-Type:text/html;charset=utf-8');
    }

    $filter = function (&$value) use (&$filter): void {
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

    if ($isCli) {
        foreach ($args as $value) {
            $filter($value);
            print_r($value);
            echo PHP_EOL;
        }
    } else {
        foreach ($args as $value) {
            $filter($value);
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
