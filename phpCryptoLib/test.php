<?php
error_reporting(0);
use Shift196\AKashLib\Hasher;
use Shift196\AKashLib\InputDataSupplier;

require_once 'vendor/autoload.php';

Hasher::regBuiltinAlgos();

$entities = (array) json_decode(file_get_contents('fixtures.json'));

foreach ($entities as $i => $entity)
{

    list($algo, $validHashHex, $dataHex) = $entity;

    $hash = Hasher::doHash($algo, InputDataSupplier::forHex($dataHex));
    $fail = $hash->hex(TRUE) !== strtoupper($validHashHex);

    printf('test %s#%d is: %s%s'
        , $algo, $i, $fail ? 'FAILED' : 'OK', PHP_EOL);

    if ($fail)
        var_dump($hash->hex(), $validHashHex);
}
