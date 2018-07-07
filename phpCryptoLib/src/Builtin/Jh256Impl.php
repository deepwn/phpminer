<?php

namespace Shift196\AKashLib\Builtin;

use Shift196\AKashLib\IHashFunction;

final
    class Jh256Impl
    implements IHashFunction
{

    private
        $databitlen; // max 0xffffffff

    private
        $datasize_in_buffer;

    private
        $H; //128

    private
        $A; //256

    private
        $rndConst; //64

    private
        $buffer; //64

    private
        $roundconstant_zero; //IMMUTABLE DATA

    private
        $S; //IMMUTABLE DATA

    public
        function __construct()
    {

        /* The constant for the Round 0 of E8 */
        $this->roundconstant_zero = [
            0x6, 0xa, 0x0, 0x9, 0xe, 0x6, 0x6, 0x7,
            0xf, 0x3, 0xb, 0xc, 0xc, 0x9, 0x0, 0x8,
            0xb, 0x2, 0xf, 0xb, 0x1, 0x3, 0x6, 0x6,
            0xe, 0xa, 0x9, 0x5, 0x7, 0xd, 0x3, 0xe,
            0x3, 0xa, 0xd, 0xe, 0xc, 0x1, 0x7, 0x5,
            0x1, 0x2, 0x7, 0x7, 0x5, 0x0, 0x9, 0x9,
            0xd, 0xa, 0x2, 0xf, 0x5, 0x9, 0x0, 0xb,
            0x0, 0x6, 0x6, 0x7, 0x3, 0x2, 0x2, 0xa
        ];

        /* The two Sboxes S0 and S1 */
        $this->S = [
            [9, 0, 4, 11, 13, 12, 3, 15, 1, 10, 2, 6, 7, 5, 8, 14],
            [3, 12, 6, 13, 5, 7, 1, 9, 15, 2, 0, 4, 11, 10, 14, 8]
        ];
    }

    public
        function doHash(array $inputData, array $opts = [])
    {

        //reset state
        #$this->hashbitlen         = 0; // max 0xffffffff
        $this->databitlen         = 0; // max 0xffffffff
        $this->datasize_in_buffer = 0;
        $this->H                  = array_fill(0, 128, 0);
        $this->A                  = array_fill(0, 256, 0);
        $this->rndConst           = array_fill(0, 64, 0);
        $this->buffer             = array_fill(0, 64, 0);

        $hashval = array_fill(0, 32, 0);

        $this->_init();
        $this->_update($inputData, count($inputData) * 8);
        $this->_final($hashval);

        return $hashval;
    }

    /* the round function of E8 */

    private
        function _r8()
    {
        $tem        = array_fill(0, 256, 0);
        $t          = 0;
        $rcExpanded = array_fill(0, 256, 0); #round constant

        /* expand the round constant into 256 one-bit element */
        for ($i = 0; $i < 256; ++$i)
            $rcExpanded[$i] = ($this->rndConst[$i >> 2] >> (3 - ($i & 3))) & 1;

        /* S box layer, each constant bit selects one Sbox from S0 and S1 */
        for ($i = 0; $i < 256; ++$i)
        /* constant bits are used to determine which Sbox to use */
            $tem[$i] = $this->S[$rcExpanded[$i]][$this->A[$i]];

        /* MDS Layer */
        for ($i = 0; $i < 256; $i += 2)
        {
            // L(tem[i], tem[i+1])

            $tem[$i + 1] ^= (((($tem[$i]) << 1) >> 0) ^
                (($tem[$i]) >> 3) ^
                ((($tem[$i]) >> 2) & 2)) &
                0xf;

            $tem[$i] ^= (((($tem[$i + 1]) << 1) >> 0) ^
                (($tem[$i + 1]) >> 3) ^
                ((($tem[$i + 1]) >> 2) & 2)) &
                0xf;
        }

        /* The following is the permuation layer P_8$

          /*initial swap Pi_8 */
        for ($i = 0; $i < 256; $i += 4)
        {
            $t           = $tem[$i + 2];
            $tem[$i + 2] = $tem[$i + 3];
            $tem[$i + 3] = $t;
        }

        /* permutation P'_8 */
        for ($i = 0; $i < 128; ++$i)
        {
            $this->A[$i]       = $tem[$i << 1];
            $this->A[$i + 128] = $tem[($i << 1) + 1];
        }

        /* final swap Phi_8 */
        for ($i = 128; $i < 256; $i += 2)
        {
            $t               = $this->A[$i];
            $this->A[$i]     = $this->A[$i + 1];
            $this->A[$i + 1] = $t;
        }
    }

    /* The following function generates the next round constant from the current
      round constant;  R6 is used for generating round constants for E8, with
      the round constants of R6 being set as 0;
     */

    private
        function _updateRndConst()
    {

        $i   = 0;
        $tem = array_fill(0, 64, 0);
        $t   = 0;

        /* Sbox layer */
        for ($i = 0; $i < 64; ++$i)
            $tem[$i] = $this->S[0][$this->rndConst[$i]];


        /* MDS layer */
        for ($i = 0; $i < 64; $i += 2)
        {
            //L(tem[i], tem[i+1])

            $tem[$i + 1] ^= (((($tem[$i]) << 1) >> 0) ^
                (($tem[$i]) >> 3) ^
                ((($tem[$i]) >> 2) & 2)) &
                0xf;

            $tem[$i] ^= (((($tem[$i + 1]) << 1) >> 0) ^
                (($tem[$i + 1]) >> 3) ^
                ((($tem[$i + 1]) >> 2) & 2)) &
                0xf;
        }

        /* The following is the permutation layer P_6 */

        /* initial swap Pi_6 */
        for ($i = 0; $i < 64; $i += 4)
        {
            $t           = $tem[$i + 2];
            $tem[$i + 2] = $tem[$i + 3];
            $tem[$i + 3] = $t;
        }

        /* permutation P'_6 */
        for ($i = 0; $i < 32; ++$i)
        {
            $this->rndConst[$i]      = $tem[$i << 1];
            $this->rndConst[$i + 32] = $tem[($i << 1) + 1];
        }

        /* final swap Phi_6 */
        for ($i = 32; $i < 64; $i += 2)
        {
            $t                      = $this->rndConst[$i];
            $this->rndConst[$i]     = $this->rndConst[$i + 1];
            $this->rndConst[$i + 1] = $t;
        }
    }

    /* initial group at the begining of E_8: group the
     *  bits of H into 4-bit elements of A.
      After the grouping, the i-th, (i+256)-th, (i+512)-th,
     *  (i+768)-th bits of state.H
      become the i-th 4-bit element of state.A
     */

    private
        function _e8Initialgroup()
    {
        $t0  = 0;
        $t1  = 0;
        $t2  = 0;
        $t3  = 0;
        $tem = array_fill(0, 256, 0);

        /* t0 is the i-th bit of H, i = 0, 1, 2, 3, ... , 127 */
        /* t1 is the (i+256)-th bit of H */
        /* t2 is the (i+512)-th bit of H */
        /* t3 is the (i+768)-th bit of H */
        for ($i = 0; $i < 256; ++$i)
        {
            $t0      = ($this->H[$i >> 3] >> (7 - ($i & 7))) & 1;
            $t1      = ($this->H[($i + 256) >> 3] >> (7 - ($i & 7))) & 1;
            $t2      = ($this->H[($i + 512) >> 3] >> (7 - ($i & 7))) & 1;
            $t3      = ($this->H[($i + 768) >> 3] >> (7 - ($i & 7))) & 1;
            $tem[$i] = (((($t0 << 3) >> 0) |
                (($t1 << 2) >> 0) |
                (($t2 << 1) >> 0) |
                ($t3 << 0)) & 0xFF) >> 0;
        }

        /* padding the odd-th elements and even-th elements separately */
        for ($i = 0; $i < 128; ++$i)
        {
            $this->A[$i << 1]       = $tem[$i];
            $this->A[($i << 1) + 1] = $tem[$i + 128];
        }
    }

    private
        function _e8Finaldegroup()
    {
        $t0  = 0;
        $t1  = 0;
        $t2  = 0;
        $t3  = 0;
        $tem = array_fill(0, 256, 0);

        for ($i = 0; $i < 128; ++$i)
        {
            $tem[$i]       = $this->A[$i << 1];
            $tem[$i + 128] = $this->A[($i << 1) + 1];
        }

        for ($i = 0; $i < 128; ++$i)
            $this->H[$i] = 0;


        for ($i = 0; $i < 256; ++$i)
        {
            $t0 = ($tem[$i] >> 3) & 1;
            $t1 = ($tem[$i] >> 2) & 1;
            $t2 = ($tem[$i] >> 1) & 1;
            $t3 = ($tem[$i] >> 0) & 1;

            $this->H[$i >> 3]         |= $t0 << (7 - ($i & 7));
            $this->H[($i + 256) >> 3] |= ($t1 << (7 - ($i & 7))) >> 0;
            $this->H[($i + 512) >> 3] |= ($t2 << (7 - ($i & 7))) >> 0;
            $this->H[($i + 768) >> 3] |= ($t3 << (7 - ($i & 7))) >> 0;
        }
    }

    /* bijective function E8 */

    private
        function _e8()
    {

        /* initialize the round constant */
        for ($i = 0; $i < 64; ++$i)
            $this->rndConst[$i] = $this->roundconstant_zero[$i];


        /* initial group at the begining of E_8: 
         * group the H value into 4-bit elements and store them in A */
        $this->_e8Initialgroup();

        /* 42 rounds */
        for ($i = 0; $i < 42; ++$i)
        {
            $this->_r8();
            $this->_updateRndConst();
        }

        /* de-group at the end of E_8:
         * decompose the 4-bit elements of A into the 1024-bit H */
        $this->_e8Finaldegroup();
    }

    private
        function _f8()
    {

        /* xor the message with the first half of H */
        for ($i = 0; $i < 64; $i++)
            $this->H[$i] ^= $this->buffer[$i];


        /* Bijective function E8 */
        $this->_e8();

        /* xor the message with the last half of H */
        for ($i = 0; $i < 64; $i++)
            $this->H[$i + 64] ^= $this->buffer[$i];
    }

    /* memcpy: copy 'len' elements 
      from array 'arr2' starting at index 'off2'
      to array 'arr1' starting at index 'off1'
     */

    private
        function _memcpy(array &$arr1, $off1, $arr2, $off2, $len)
    {

        for ($i = 0; $i < $len; ++$i)
            $arr1[$off1 + $i] = $arr2[$off2 + $i];
    }

    private
        function _init()
    {
        $this->databitlen         = 0;
        $this->datasize_in_buffer = 0;
        #$this->hashbitlen         = 256;

        for ($i = 0; $i < count($this->buffer); ++$i)
            $this->buffer[$i] = 0;

        for ($i = 0; $i < count($this->H); ++$i)
            $this->H[$i] = 0;

        $this->H[1] = 256 & 0xff;
        $this->H[0] = (256 >> 8) & 0xff;

        $this->_f8();
    }

    /* hash each 512-bit message block, except the last partial block */

    private
        function _update(array $data, $databitlen)
    {
        //$index = 0; /*the starting address of the data to be compressed*/

        $this->databitlen += $databitlen;
        $index            = 0;

        /* if there is remaining data in the buffer, 
         * fill it to a full message block first */
        /* we assume that the size of the data in the buffer is the 
         * multiple of 8 bits if it is not at the end of a message */

        /* There is data in the buffer, but the incoming data is 
         * insufficient for a full block */
        if (($this->datasize_in_buffer > 0) &&
            (($this->datasize_in_buffer + $databitlen) < 512))
        {

            $c = ($databitlen & 7) === 0 ? 0 : 1;

            $this->_memcpy($this->buffer, $this->datasize_in_buffer >> 3
                , $data, 0, 64 - ($this->datasize_in_buffer >> 3) + $c);

            $this->datasize_in_buffer += $databitlen;
            $databitlen               = 0;
        }

        /* There is data in the buffer, and the incoming data is
         * sufficient for a full block */
        if (($this->datasize_in_buffer > 0) &&
            (($this->datasize_in_buffer + $databitlen) >= 512))
        {

            $this->_memcpy($this->buffer, $this->datasize_in_buffer >> 3
                , $data, 0, 64 - ($this->datasize_in_buffer >> 3));

            $index      = 64 - ($this->datasize_in_buffer >> 3);
            $databitlen = $databitlen - (512 - $this->datasize_in_buffer);

            $this->_f8();

            $this->datasize_in_buffer = 0;
        }


        /* hash the remaining full message blocks */
        for (; $databitlen >= 512
        ; $index += 64, $databitlen -= 512)
        {
            $this->_memcpy($this->buffer, 0, $data, $index, 64);
            $this->_f8();
        }


        /* store the partial block into buffer, assume that -- if part of the 
         * last byte is not part of the message, then that 
         * part consists of 0 bits */
        if ($databitlen <= 0)
            return;

        $c = ($databitlen & 7) === 0 ? 0 : 1;

        $this->_memcpy($this->buffer, 0
            , $data, $index, (($databitlen & 0x1ff) >> 3) + $c);

        $this->datasize_in_buffer = $databitlen;
    }

    /* padding the message, truncate the hash value H 
     * and obtain the message digest */

    private
        function _final(array &$hashval)
    {

        if (($this->databitlen & 0x1ff) === 0)
        {
            /* pad the message when databitlen is multiple of 512 bits, 
             * then process the padded block */
            for ($i = 0; $i < 64; $i++)
                $this->buffer[$i] = 0x00;

            $this->buffer[0]  = 0x80;
            $this->buffer[63] = $this->databitlen & 0xff;
            $this->buffer[62] = ($this->databitlen >> 8) & 0xff;
            $this->buffer[61] = ($this->databitlen >> 16) & 0xff;
            $this->buffer[60] = ($this->databitlen >> 24) & 0xff;

            $this->_f8();
        }
        else
        {
            /* set the rest of the bytes in the buffer to 0 */
            $c = ($this->datasize_in_buffer & 7) === 0 ? 0 : 1;

            for ($i = (($this->databitlen & 0x1ff) >> 3) + $c; $i < 64; ++$i)
                $this->buffer[$i] = 0;

            /* pad and process the partial block when databitlen 
             * is not multiple of 512 bits, then hash the padded blocks */
            $this->buffer[(($this->databitlen & 0x1ff) >> 3)] |= 1 <<
                (7 - ($this->databitlen & 7));

            $this->_f8();

            for ($i = 0; $i < 64; $i++)
                $this->buffer[$i] = 0;

            $this->buffer[63] = $this->databitlen & 0xff;
            $this->buffer[62] = ($this->databitlen >> 8) & 0xff;
            $this->buffer[61] = ($this->databitlen >> 16) & 0xff;
            $this->buffer[60] = ($this->databitlen >> 24) & 0xff;

            $this->_f8();
        }

        /* trunacting the final hash value to generate the message digest */
        $this->_memcpy($hashval, 0, $this->H, 96, 32);
    }

}
