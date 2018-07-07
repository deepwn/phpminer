<?php

namespace Shift196\AKashLib;

interface IHashFunction
{

    /**
     * 
     * @param array $inputData int[]
     * @param array $opts
     * @return array int[]
     */
    public
        function doHash(array $inputData, array $opts = []);
}
