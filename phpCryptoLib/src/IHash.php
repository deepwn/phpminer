<?php

namespace Shift196\AKashLib;

interface IHash
{

    /**
     * @return string
     */
    public
        function binaryString();

    /**
     * @return int[]
     */
    public
        function byteArray();

    /**
     * @param bool $upperCase
     * @return string
     */
    public
        function hex($upperCase = TRUE);
}
