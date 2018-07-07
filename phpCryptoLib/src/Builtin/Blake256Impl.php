<?php

namespace Shift196\AKashLib\Builtin;

use Shift196\AKashLib\IHashFunction;

final
    class Blake256Impl
    implements IHashFunction
{

    /**
     *
     * @var uint32_t 
     */
    private
        $h = array();

    /**
     *
     * @var uint32_t 
     */
    private
        $s = array();

    /**
     *
     * @var uint32_t 
     */
    private
        $t = array();

    /**
     *
     * @var int
     */
    private
        $buflen;

    /**
     *
     * @var int
     */
    private
        $nullt;

    /**
     *
     * @var uint8_t
     */
    private
        $buf;

    private
        $sigma, $cst, $padding;

    public
        function __construct()
    {

        /* inplace */
        $this->sigma   = array(
            array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15),
            array(14, 10, 4, 8, 9, 15, 13, 6, 1, 12, 0, 2, 11, 7, 5, 3),
            array(11, 8, 12, 0, 5, 2, 15, 13, 10, 14, 3, 6, 7, 1, 9, 4),
            array(7, 9, 3, 1, 13, 12, 11, 14, 2, 6, 5, 10, 4, 0, 15, 8),
            array(9, 0, 5, 7, 2, 4, 10, 15, 14, 1, 11, 12, 6, 8, 3, 13),
            array(2, 12, 6, 10, 0, 11, 8, 3, 4, 13, 7, 5, 15, 14, 1, 9),
            array(12, 5, 1, 15, 14, 13, 4, 10, 0, 7, 6, 3, 9, 2, 8, 11),
            array(13, 11, 7, 14, 12, 1, 3, 9, 5, 0, 15, 4, 8, 6, 2, 10),
            array(6, 15, 14, 9, 11, 3, 0, 8, 12, 2, 13, 7, 1, 4, 10, 5),
            array(10, 2, 8, 4, 7, 6, 1, 5, 15, 11, 9, 14, 3, 12, 13, 0),
            array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15),
            array(14, 10, 4, 8, 9, 15, 13, 6, 1, 12, 0, 2, 11, 7, 5, 3),
            array(11, 8, 12, 0, 5, 2, 15, 13, 10, 14, 3, 6, 7, 1, 9, 4),
            array(7, 9, 3, 1, 13, 12, 11, 14, 2, 6, 5, 10, 4, 0, 15, 8)
        );
        $this->cst     = array(
            0x243F6A88, 0x85A308D3, 0x13198A2E, 0x03707344,
            0xA4093822, 0x299F31D0, 0x082EFA98, 0xEC4E6C89,
            0x452821E6, 0x38D01377, 0xBE5466CF, 0x34E90C6C,
            0xC0AC29B7, 0xC97C50DD, 0x3F84D5B5, 0xB5470917
        );
        $this->padding = array(
            0x80, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
            0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
            0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
            0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0
        );
    }

    /**
     * 
     * @param type $p0 uint8_t
     * @param type $p1 uint8_t
     * @param type $p2 uint8_t
     * @param type $p3 uint8_t
     * @return type uint32_t
     */
    /* inplace */
    private
        function __U8TO32($p0, $p1, $p2, $p3)
    {
        return (((($p0 & 0xff) << 24) | (($p1 & 0xff) << 16)) & 0xffffffff |
            (($p2 & 0xff) << 8) | ($p3 & 0xff)) & 0xffffffff;
    }

    /* inplace */

    /**
     * 
     * @param type $p0 uint8_t
     * @param type $p1 uint8_t
     * @param type $p2 uint8_t
     * @param type $p3 uint8_t
     * @param real $v uint32_t
     */
    /* inplace */
    private
        function __U32TO8(&$p0, &$p1, &$p2, &$p3, $v)
    {
        $v  &= 0xffffffff;
        $p0 = ($v >> 24) & 0xff;
        $p1 = ($v >> 16) & 0xff;
        $p2 = ($v >> 8) & 0xff;
        $p3 = $v & 0xff;
    }

    /* inplace */

    private
        function _64to32($x)
    {
        //强制去除后四字节
        $a = (($x >> 0) & 0xff) << 0;
        $b = (($x >> 8) & 0xff) << 8;
        $c = (($x >> 16) & 0xff) << 16;
        $d = (($x >> 24) & 0xff) << 24;
        return $a | $b | $c | $d;
    }

    private
        function ROT($x, $n)
    {
        $x = $this->_64to32($x);
        return ($this->trans2(($x) << (32 - $n)) |
            $this->trans2(($x >> $n) & 0xffffffff));
    }

    /* inplace */
    /* inplace */

    private
        function trans($nb)
    {
        $n = $nb & 0xffffffff;
        if ($n > 0x7fffffff)
        {
            $n--;
            $n = ~$n;
            $n &= 0x7fffffff;
            $n = -$n;
        }
        return $n;
    }

    private
        function trans2($num)
    {
        $num = unpack('l', pack('l', $num));
        return $num[1];
    }

    private
        function G($a, $b, $_c, $d, $e, array &$v, array $m, $i)
    {

        $v[$a] = $this->trans($v[$a] +
            $this->trans(($m[$this->sigma[$i][$e]] ^
                $this->cst[$this->sigma[$i][$e + 1]])) + ($v[$b]));

        $v[$d] = $this->trans($this->ROT(($v[$d]) ^ ($v[$a]), 16));

        $v[$_c] = $this->trans($v[$_c] + $v[$d]);

//print_r(trans2(($v[$b]) ^ ($v[$_c])));

        $v[$b] = $this->trans($this->ROT(($v[$b]) ^ ($v[$_c]), 12));

        $v[$a] = $this->trans($v[$a] +
            $this->trans((($m[$this->sigma[$i][$e + 1]] ^
                $this->cst[$this->sigma[$i][$e]]) ) + ($v[$b])));
        $v[$d] = $this->trans($this->ROT($v[$d] ^ $v[$a], 8));

        $v[$_c] = $this->trans($v[$_c] + ($v[$d] ));
        $v[$b]  = $this->trans($this->ROT($v[$b] ^ $v[$_c], 7));
    }

    /* inplace */

    /**
     * 
     * @param state_ $S state_ *S
     * @param array $block const uint8_t *block
     */
    /* inplace */private
        function blake256_compress($block)
    {
        #global $sigma, $cst;
        //uint32_t v[16], m[16], i;
        $v       = array_fill(0, 16, 0);
        $m       = array_fill(0, 16, 0);
        #$i = 0;
        for ($i = 0, $p = 0; $i < 16 && $p < 64;)
            $m[$i++] = $this->__U8TO32($block[$p++], $block[$p++]
                , $block[$p++], $block[$p++]);
        for ($i = 0; $i < 8; ++$i)
        {
            $v[$i] = $this->h[$i];
        }/* END unroll */
        $v[8]  = ($this->s[0] ^ 0x243F6A88);
        $v[9]  = $this->s[1] ^ 0x85A308D3;
        $v[10] = $this->s[2] ^ 0x13198A2E;
        $v[11] = $this->s[3] ^ 0x03707344;
        $v[12] = 0xA4093822;
        $v[13] = 0x299F31D0;
        $v[14] = 0x082EFA98;
        $v[15] = 0xEC4E6C89;
        if ($this->nullt == 0)
        {
            $v[12] ^= $this->t[0];
            $v[13] ^= $this->t[0];
            $v[14] ^= $this->t[1];
            $v[15] ^= $this->t[1];
        }

        for ($i = 0; $i < 16; ++$i)
            $v[$i] = $this->trans($v[$i]);



        /* START unroll */for ($i = 0; $i < 14; ++$i)
        {

            $this->G(0, 4, 8, 12, 0, $v, $m, $i);

            $this->G(1, 5, 9, 13, 2, $v, $m, $i);

            $this->G(2, 6, 10, 14, 4, $v, $m, $i);

            $this->G(3, 7, 11, 15, 6, $v, $m, $i);
            $this->G(3, 4, 9, 14, 14, $v, $m, $i);
            $this->G(2, 7, 8, 13, 12, $v, $m, $i);
            $this->G(0, 5, 10, 15, 8, $v, $m, $i);
            $this->G(1, 6, 11, 12, 10, $v, $m, $i);
        }/* END unroll */



//print_r( $v);
        /* START unroll */ for ($i = 0; $i < 16; ++$i)
        {
            $this->h[$i % 8] ^= $v[$i] & 0xffffffff;
        }/* END unroll */
        /* START unroll */ for ($i = 0; $i < 8; ++$i)
        {
            $this->h[$i] ^= $this->s[$i % 4];
        }/* END unroll */
    }

    /* inplace */

    /**
     * 
     * @param state_ $S state_ *S
     */
    private
        function blake256_init()
    {
        $this->h[0]   = 0x6A09E667;
        $this->h[1]   = 0xBB67AE85;
        $this->h[2]   = 0x3C6EF372;
        $this->h[3]   = 0xA54FF53A;
        $this->h[4]   = 0x510E527F;
        $this->h[5]   = 0x9B05688C;
        $this->h[6]   = 0x1F83D9AB;
        $this->h[7]   = 0x5BE0CD19;
        $this->t[0]   = $this->t[1]   = $this->buflen = $this->nullt  = 0;
        $this->s[0]   = $this->s[1]   = $this->s[2]   = $this->s[3]   = 0;
    }

    /**
     * 
     * @param state_ $S state_ *S
     * @param array $data const uint8_t *data
     * @param uint64 $datalen uint64_t datalen = number of bits
     */
    private
        function blake256_update(array $data, $datalen)
    {
        /* int */$left = $this->buflen >> 3;
        /* int */ $fill = 64 - $left;
        if ($left && ((($datalen >> 3) & 0x3F) >= /* (unsigned) */$fill))
        {

            #memcpy((void *) (S->buf + left), (void *) data, fill);
            for ($x = 0; $x < $fill; ++$x)
                $this->buf[$x + $left] = $data[$x];
            $this->t[0]            += 512;

            if ($this->t[0] == 0)
                ++$this->t[1];

            $this->blake256_compress($this->buf);

            #$data += $fill;
            $data = array_slice($data, $fill);

            $datalen -= ($fill << 3);

            $left = 0;
        }
        while ($datalen >= 512)
        {
            $this->t[0] += 512;
            if ($this->t[0] === 0)
                ++$this->t[1];
            $this->blake256_compress($data);
            #$data += 64;
            $data       = array_slice($data, 64);
            $datalen    -= 512;
        }
        if ($datalen > 0)
        {
            #memcpy((void *) (S->buf + left), (void *) data, datalen >> 3);
            for ($x = 0; $x < ($datalen >> 3); ++$x)
                $this->buf[$x + $left] = $data[$x];
            $this->buflen          = ($left << 3) + ((int) $datalen);
        }
        else
            $this->buflen = 0;
    }

    /**
     * 
     * @global array $padding
     * @param state_ $S state_ *S
     * @param array $digest uint8_t *digest
     * @param type $pa uint8_t
     * @param type $pb uint8_t
     */
    function blake256_final_h(array &$digest, $pa, $pb)
    {
        #uint8_t msglen[8];
        $msglen = array_fill(0, 8, 0);
        #uint32_t lo = S->t[0] + S->buflen, hi = S->t[1];
        $lo     = ($this->t[0] + $this->buflen) & 0xffffffff;
        $hi     = $this->t[1] & 0xffffffff;
        if ($lo < /* (unsigned) */$this->buflen)
            ++$hi;


        $this->__U32TO8($msglen[0], $msglen[1], $msglen[2], $msglen[3], $hi);
        $this->__U32TO8($msglen[4], $msglen[5], $msglen[6], $msglen[7], $lo);

        if ($this->buflen == 440)
        { /* one padding byte */
            $this->t[0] -= 8;
            $this->blake256_update(  array($pa), 8);
        }
        else
        {
            if ($this->buflen < 440)
            { /* enough space to fill the block  */
                $this->nullt = $this->buflen === 0 ? 1 : $this->nullt;
                $this->t[0]  -= 440 - $this->buflen;

                $this->blake256_update( $this->padding, 440 - $this->buflen);
                //print_r($S->nullt);
            }
            else
            { /* need 2 compressions */
                $this->t[0]  -= 512 - $this->buflen;
                $this->blake256_update(  $this->padding, 512 - $this->buflen);
                $this->t[0]  -= 440;
                #blake256_update($S, $padding + 1, 440);
                $this->blake256_update(  array_slice($this->padding, 1), 440);
                $this->nullt = 1;
            }
            $this->blake256_update(  array($pb), 8);
            $this->t[0] -= 8;
        }
        $this->t[0] -= 64;
        $this->blake256_update($msglen, 64);
        #$digest = array_fill(0, 32, 0);
        $this->__U32TO8($digest[0], $digest[1], $digest[2], $digest[3], $this->h[0]);
        $this->__U32TO8($digest[4], $digest[5], $digest[6], $digest[7], $this->h[1]);
        $this->__U32TO8($digest[8], $digest[9], $digest[10], $digest[11], $this->h[2]);
        $this->__U32TO8($digest[12], $digest[13], $digest[14], $digest[15], $this->h[3]);
        $this->__U32TO8($digest[16], $digest[17], $digest[18], $digest[19], $this->h[4]);
        $this->__U32TO8($digest[20], $digest[21], $digest[22], $digest[23], $this->h[5]);
        $this->__U32TO8($digest[24], $digest[25], $digest[26], $digest[27], $this->h[6]);
        $this->__U32TO8($digest[28], $digest[29], $digest[30], $digest[31], $this->h[7]);
    }

    /**
     * 
     * @param state_ $S state_ *S
     * @param array $digest uint8_t *digest
     */
    function blake256_final(/* uint8_t * */ array &$digest)
    {
        $this->blake256_final_h($digest, 0x81, 0x01);
    }

    public
        function doHash(array $inputData, array $opts = array())
    {
        $this->h   = array_fill(0, 8, 0);
        $this->s   = array_fill(0, 4, 0);
        $this->t   = array_fill(0, 2, 0);
        $this->buf = array_fill(0, 64, 0);
        $out       = [];

        $this->blake256_init();
        $this->blake256_update($inputData, count($inputData) * 8);
        $this->blake256_final($out);

        return $out;
    }

}
