<?php

namespace Shift196\AKashLib;

final
    class HashImpl
    implements IHash
{

    /**
     *
     * @var int[]
     */
    private
        $_array;

    /**
     * 
     * @param int[] $hashArray
     */
    public
        function __construct($hashArray)
    {
        $this->_array = $hashArray;
    }

    public
        function binaryString()
    {
        return implode('', array_map('chr', $this->_array));
    }

    public
        function byteArray()
    {
        return array_slice($this->_array, 0);
    }

    public
        function hex($upperCase = TRUE)
    {
        $h = bin2hex($this->binaryString());
        return $upperCase ? strtoupper($h) : strtolower($h);
    }

}
