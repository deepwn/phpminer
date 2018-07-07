<?php

namespace Shift196\AKashLib;

use InvalidArgumentException;
use Shift196\AKashLib\Builtin\Blake256Impl;
use Shift196\AKashLib\Builtin\Groestl256;
use Shift196\AKashLib\Builtin\Jh256Impl;
use Shift196\AKashLib\Builtin\Skein256Impl;

final
    class Hasher
{

    /**
     *
     * @var IHashFunction[]
     */
    private static
        $_algoMap = [];

    private
        function __construct()
    {
        
    }

    /**
     * 
     * @param string $algoName
     * @param IHashFunction $fn
     */
    public static
        function registerAlgo($algoName, IHashFunction $fn)
    {
        static::$_algoMap[$algoName] = $fn;
    }

    public static
        function regBuiltinAlgos()
    {

        Hasher::registerAlgo('JH256', new Jh256Impl());
        Hasher::registerAlgo('BLAKE256', new Blake256Impl());
        Hasher::registerAlgo('GROESTL256', new Groestl256());
        Hasher::registerAlgo('SKEIN256', new Skein256Impl());
    }

    /**
     * 
     * @param string $algoName
     * @param InputDataSupplier $input
     * @param array $opts Extra options to IHashFunction
     * @throws InvalidArgumentException
     * @return IHash
     */
   static public
        function doHash($algoName, InputDataSupplier $input, array $opts = [])
    {

        if (!isset(static::$_algoMap[$algoName]))
            throw new InvalidArgumentException(
            sprintf('Unknown algo: %s.', $algoName));

        $hashArray = static::$_algoMap[$algoName]
            ->doHash($input->getInputData(), $opts);

        return new HashImpl($hashArray);
    }

}
