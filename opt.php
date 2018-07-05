<?php

$source = file_get_contents('blake256.php');

$methods = array();
preg_match_all('/\/\*\s*inplace\s*\*\/'
        . '\s*'
        . 'function\s+(?<name>\w+)\s*\((?<args>[^\)]+)\)\s*\{(?<body>.*?)\}'
        . '\s*'
        . '\/\*\s*inplace\s*\*\/'
        . '/iesu'
        , $source, $methods, PREG_SET_ORDER);

$source = preg_replace('/\/\*\s*inplace\s*\*\/'
        . '.*?'
        . '\/\*\s*inplace\s*\*\/'
        . '/isu', '', $source);

function unroll(array $for) {

    $output = '';
    $m = intval($for['limit']);
    $var = $for['varname'];
    $body = $for['body'];

    for ($i = 0; $i < $m; ++$i) {
        $output .= sprintf('$%s=%d;%s', $var, $i, PHP_EOL);
        $output .= $body;
        $output .= PHP_EOL;
        $output .= PHP_EOL;
    }

    return $output;
}

function inplace(array $data) {
    global $methods;

    $func = NULL;
    foreach ($methods as $m)
        ($m['name'] === $data['funcname']) && ($func = $m);

    if (!$func)
        return $data[0];

    $calltimeargs = array_map('trim', explode(',', $data['args']));
    $macroargs = array_map('trim', explode(',', str_replace('&', '', $func['args'])));

    $body = $func['body'];

    foreach ($macroargs as $idx => $arg_var) {

        $body = str_replace($arg_var, $calltimeargs[$idx], $body);
    }

    $code = str_replace('return', '', $body);
    $code = runinplace($code);
    return $code;
}

function runinplace($source) {

    return $source = preg_replace_callback('/(?<funcname>\w+)\((?<args>[^\)]*)\)\;?'
            . '/isu', 'inplace', $source/*, -1, $count*/);
}

$source = runinplace($source);

$source = preg_replace_callback('/\/\*\s*START unroll\s*\*\/'
        . '\s*'
        . 'for\s*\(\$(?<varname>\w+)\s*=\s*0\;\s*\$\\1\s*\<\s*(?<limit>\d+)\s*\;\s*\+\+\$\\1\s*\)\s*\{'
        . '\s*'
        . '(?<body>.*?)'
        . '\s*'
        . '\}'
        . '\s*'
        . '\/\*\s*END unroll\s*\*\/'
        . '/isu', 'unroll', $source);


file_put_contents('b256.opt.php', $source);
