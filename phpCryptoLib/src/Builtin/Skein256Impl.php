<?php

namespace Shift196\AKashLib\Builtin;

use Shift196\AKashLib\IHashFunction;
use Shift196\AKashLib\Util\UnsignedInt64 as o_u64;

final
    class Skein256Impl
    implements IHashFunction
{

    /**
     * int
     */
    const
        BLOCK_LEN = 64;

    /**
     *
     * @var byte[] 
     */
    private
        $buf;

    /**
     *
     * @var byte[] 
     */
    private
        $tmpOut;

    /**
     *
     * @var int 
     */
    private
        $ptr;

    /**
     *
     * @var long[]
     */
    private
        $h;

    /**
     *
     * @var o_u64
     */
    private
        $bcount;

    /**
     * Create the object.
     */
    function __construct()
    {
        $this->buf    = array_fill(0, static::BLOCK_LEN, 0);
        $this->tmpOut = array_fill(0, static::BLOCK_LEN, 0);
        #$this->h = array_fill(0, 27, 0);
        $this->h      = array_fill(0, 27, NULL);
        for ($i = 0; $i < 27; ++$i)
            $this->h[$i]  = new o_u64(0x0, 0x0);


        $this->bcount = new o_u64(0x0, 0x0);
        $this->reset();
    }

    private
        function arraycopy(array $src, $src_position
    , array &$dst, $dst_position
    , $length)
    {
        for (; $length > 0; --$length)
            $dst[$dst_position++] = $src[$src_position++];
    }

    /** @see Digest */
    private
        function update2(array $inbuf, $off, $len)
    {
        if ($len <= 0)
            return;
        $clen = static::BLOCK_LEN - $this->ptr;
        if ($len <= $clen)
        {
            $this->arraycopy($inbuf, $off, $this->buf, $this->ptr, $len);
            $this->ptr += $len;
            return;
        }
        if ($clen != 0)
        {
            $this->arraycopy($inbuf, $off, $this->buf, $this->ptr, $clen);
            $off += $clen;
            $len -= $clen;
        }

        for (;;)
        {
            $etype = ($this->bcount->lo == 0) ? 224 : 96;
            #++$this->bcount;
            $this->bcount->addOne();
            $this->ubi($etype, 0);
            if ($len <= static::BLOCK_LEN)
                break;
            $this->arraycopy($inbuf, $off, $this->buf, 0, static::BLOCK_LEN);
            $off   += static::BLOCK_LEN;
            $len   -= static::BLOCK_LEN;
        }
        $this->arraycopy($inbuf, $off, $this->buf, 0, $len);
        $this->ptr = $len;
    }

    /** @see Digest */
    private
        function digest0()
    {
        $len = $this->getDigestLength();
        $out = array_fill(0, $len, 0);
        $this->digest2($out, 0, $len);
        return $out;
    }

    /** @see Digest */
    private
        function digest1($inbuf)
    {
        $this->update2($inbuf, 0, count($inbuf));
        return $this->digest0();
    }

    /** @see Digest */
    private
        function digest2(array &$outbuf, $off, $len)
    {
        for ($i = $this->ptr; $i < static::BLOCK_LEN; ++$i)
            $this->buf[$i] = 0x00;
        $this->ubi(($this->bcount->lo == 0) ? 480 : 352, $this->ptr);
        for ($i = 0; $i < static::BLOCK_LEN; $i++)
            $this->buf[$i] = 0x00;
        $this->bcount  = new o_u64(0x0, 0x0);
        $this->ubi(510, 8);

        for ($i = 0; $i < 8; ++$i)
            $this->encodeLELong($this->h[$i], $this->tmpOut, $i << 3);
        $dlen = $this->getDigestLength();
        if ($len > $dlen)
            $len  = $dlen;
        $this->arraycopy($this->tmpOut, 0, $outbuf, $off, $len);
        $this->reset();
        return $len;
    }

    /** @see Digest */
    private
        function reset()
    {
        $this->ptr    = 0;
        $iv           = array(
            new o_u64(0xCCD044A1, 0x2FDB3E13), new o_u64(0xE8359030, 0x1A79A9EB),
            new o_u64(0x55AEA061, 0x4F816E6F), new o_u64(0x2A2767A4, 0xAE9B94DB),
            new o_u64(0xEC06025E, 0x74DD7683), new o_u64(0xE7A436CD, 0xC4746251),
            new o_u64(0xC36FBAF9, 0x393AD185), new o_u64(0x3EEDBA18, 0x33EDFC13)
        );
        $this->arraycopy($iv, 0, $this->h, 0, 8);
        $this->bcount = new o_u64(0x0, 0x0);
    }

    private
        function encodeLELong(o_u64 $val, array &$buf, $off)
    {
        /* $buf[$off + 0] = BYTE(SHR($val, 0));
          $buf[$off + 1] = BYTE(SHR($val, 8));
          $buf[$off + 2] = BYTE(SHR($val, 16));
          $buf[$off + 3] = BYTE(SHR($val, 24));
          $buf[$off + 4] = BYTE(SHR($val, 32));
          $buf[$off + 5] = BYTE(SHR($val, 40));
          $buf[$off + 6] = BYTE(SHR($val, 48));
          $buf[$off + 7] = BYTE(SHR($val, 56)); */
        $buf[$off + 0] = (($val->lo >> 0) & 0xff);
        $buf[$off + 1] = (($val->lo >> 8) & 0xff);
        $buf[$off + 2] = (($val->lo >> 16) & 0xff);
        $buf[$off + 3] = (($val->lo >> 24) & 0xff);
        ##
        $buf[$off + 4] = (($val->hi >> 0) & 0xff);
        $buf[$off + 5] = (($val->hi >> 8) & 0xff);
        $buf[$off + 6] = (($val->hi >> 16) & 0xff);
        $buf[$off + 7] = (($val->hi >> 24) & 0xff);
    }

    private
        function decodeLELong(array $buf, $off)
    {
        /* return intval(($buf[$off] & 0xFF) |
          (($buf[$off + 1] & 0xFF) << 8) |
          (($buf[$off + 2] & 0xFF) << 16) |
          (($buf[$off + 3] & 0xFF) << 24) |
          (($buf[$off + 4] & 0xFF) << 32) |
          (($buf[$off + 5] & 0xFF) << 40) |
          (($buf[$off + 6] & 0xFF) << 48) |
          (($buf[$off + 7] & 0xFF) << 56)); */

        $l = (($buf[$off] & 0xFF) |
            (($buf[$off + 1] & 0xFF) << 8) |
            (($buf[$off + 2] & 0xFF) << 16) |
            (($buf[$off + 3] & 0xFF) << 24));

        $h = ((($buf[$off + 4] & 0xFF) << 0) |
            (($buf[$off + 5] & 0xFF) << 8) |
            (($buf[$off + 6] & 0xFF) << 16) |
            (($buf[$off + 7] & 0xFF) << 24));

        return new o_u64($h, $l);
    }

    private
        function ubi(/* int */$etype, /* int */ $extra)
    {

        $m0 = $this->decodeLELong($this->buf, 0);
        $m1 = $this->decodeLELong($this->buf, 8);
        $m2 = $this->decodeLELong($this->buf, 16);
        $m3 = $this->decodeLELong($this->buf, 24);
        $m4 = $this->decodeLELong($this->buf, 32);
        $m5 = $this->decodeLELong($this->buf, 40);
        $m6 = $this->decodeLELong($this->buf, 48);
        $m7 = $this->decodeLELong($this->buf, 56);

        $p0 = clone $m0;
        $p1 = clone $m1;
        $p2 = clone $m2;
        $p3 = clone $m3;
        $p4 = clone $m4;
        $p5 = clone $m5;
        $p6 = clone $m6;
        $p7 = clone $m7;

        /* @var $p0 o_u64  */
        /* @var $p1 o_u64  */
        /* @var $p2 o_u64  */
        /* @var $p3 o_u64  */
        /* @var $p4 o_u64  */
        /* @var $p5 o_u64  */
        /* @var $p6 o_u64  */
        /* @var $p7 o_u64  */

        $dd = new o_u64(0x1BD11BDA, 0xA9FC1A22);

        for ($f = 0; $f < 8; ++$f)
            $dd         = $dd->__xor($this->h[$f]);
        $this->h[8] = $dd;

        $etype_u64 = new o_u64(0, $etype);
        $extra_u64 = new o_u64(0, $extra);

        /* @var $t0 o_u64  */
        /* @var $t1 o_u64  */
        /* @var $t2 o_u64  */
        $t0 = $this->bcount->shiftLeft(6)->plus($extra_u64);
        $t1 = $this->bcount->shiftRightUnsigned(58)
            ->plus($etype_u64->shiftLeft(55));
        $t2 = $t0->__xor($t1);

        for ($u = 0; $u <= 15; $u += 3)
        {
            $this->h[$u + 9]  = clone $this->h[$u + 0];
            $this->h[$u + 10] = clone $this->h[$u + 1];
            $this->h[$u + 11] = clone $this->h[$u + 2];
        }

        for ($u = 0; $u < 9; $u++)
        {
            $s = $u << 1;

            $p0->__add($this->h[$s + 0]);
            $p1->__add($this->h[$s + 1]);
            $p2->__add($this->h[$s + 2]);
            $p3->__add($this->h[$s + 3]);
            $p4->__add($this->h[$s + 4]);
            $p5->__add($this->h[$s + 5]->plus($t0));
            $p6->__add($this->h[$s + 6]->plus($t1));
            $p7->__add($this->h[$s + 7]->plus(new o_u64(0x0, $s)));









            $p0->__add($p1);
            $p1 = $p1->shiftLeft(46)->__xor($p1->shiftRightUnsigned(64 - 46))->__xor($p0);
            $p2->__add($p3);
            //$p3 = ($p3 << 36) ^ SHR($p3, (64 - 36)) ^ $p2;
            $p3 = $p3->shiftLeft(36)->__xor($p3->shiftRightUnsigned(64 - 36))->__xor($p2);
            $p4->__add($p5); //$p4 += $p5;
            //$p5 = ($p5 << 19) ^ SHR($p5, (64 - 19)) ^ $p4;
            $p5 = $p5->shiftLeft(19)->__xor($p5->shiftRightUnsigned(64 - 19))->__xor($p4);
            $p6->__add($p7); //$p6 += $p7;
            //$p7 = ($p7 << 37) ^ SHR($p7, (64 - 37)) ^ $p6;
            $p7 = $p7->shiftLeft(37)->__xor($p7->shiftRightUnsigned(64 - 37))->__xor($p6);



            $p2->__add($p1); //$p2 += $p1;

            $p1 = $p1->shiftLeft(33)->__xor($p1->shiftRightUnsigned(64 - 33))->__xor($p2);
            $p4->__add($p7); //$p4 += $p7;
            //$p7 = ($p7 << 27) ^ SHR($p7, (64 - 27)) ^ $p4;
            $p7 = $p7->shiftLeft(27)->__xor($p7->shiftRightUnsigned(64 - 27))->__xor($p4);
            $p6->__add($p5); //$p6 += $p5;
            //$p5 = ($p5 << 14) ^ SHR($p5, (64 - 14)) ^ $p6;
            $p5 = $p5->shiftLeft(14)->__xor($p5->shiftRightUnsigned(64 - 14))->__xor($p6);








            $p0->__add($p3); //$p0 += $p3;
            //$p3 = ($p3 << 42) ^ SHR($p3, (64 - 42)) ^ $p0;
            $p3 = $p3->shiftLeft(42)->__xor($p3->shiftRightUnsigned(64 - 42))->__xor($p0);
            $p4->__add($p1); //$p4 += $p1;
            //$p1 = ($p1 << 17) ^ SHR($p1, (64 - 17)) ^ $p4;
            $p1 = $p1->shiftLeft(17)->__xor($p1->shiftRightUnsigned(64 - 17))->__xor($p4);
            $p6->__add($p3); //$p6 += $p3;
            //$p3 = ($p3 << 49) ^ SHR($p3, (64 - 49)) ^ $p6;
            $p3 = $p3->shiftLeft(49)->__xor($p3->shiftRightUnsigned(64 - 49))->__xor($p6);
            $p0->__add($p5); //$p0 += $p5;
            //$p5 = ($p5 << 36) ^ SHR($p5, (64 - 36)) ^ $p0;
            $p5 = $p5->shiftLeft(36)->__xor($p5->shiftRightUnsigned(64 - 36))->__xor($p0);
            $p2->__add($p7); //$p2 += $p7;
            //$p7 = ($p7 << 39) ^ SHR($p7, (64 - 39)) ^ $p2;
            $p7 = $p7->shiftLeft(39)->__xor($p7->shiftRightUnsigned(64 - 39))->__xor($p2);
            $p6->__add($p1); //$p6 += $p1;
            //$p1 = ($p1 << 44) ^ SHR($p1, (64 - 44)) ^ $p6;
            $p1 = $p1->shiftLeft(44)->__xor($p1->shiftRightUnsigned(64 - 44))->__xor($p6);
            $p0->__add($p7); //$p0 += $p7;
            //$p7 = ($p7 << 9) ^ SHR($p7, (64 - 9)) ^ $p0;
            $p7 = $p7->shiftLeft(9)->__xor($p7->shiftRightUnsigned(64 - 9))->__xor($p0);
            $p2->__add($p5); //$p2 += $p5;
            //$p5 = ($p5 << 54) ^ SHR($p5, (64 - 54)) ^ $p2;
            $p5 = $p5->shiftLeft(54)->__xor($p5->shiftRightUnsigned(64 - 54))->__xor($p2);
            $p4->__add($p3); //$p4 += $p3;
            //$p3 = ($p3 << 56) ^ SHR($p3, (64 - 56)) ^ $p4;
            $p3 = $p3->shiftLeft(56)->__xor($p3->shiftRightUnsigned(64 - 56))->__xor($p4);



            $p0->__add($this->h[$s + 1 + 0]);
            $p1->__add($this->h[$s + 1 + 1]);
            $p2->__add($this->h[$s + 1 + 2]);
            $p3->__add($this->h[$s + 1 + 3]);
            $p4->__add($this->h[$s + 1 + 4]);
            $p5->__add($this->h[$s + 1 + 5]->plus($t1));
            $p6->__add($this->h[$s + 1 + 6]->plus($t2));
            $p7->__add($this->h[$s + 1 + 7]->plus(new o_u64(0x0, $s))->plus(new o_u64(0x0, 0x1)));

            $p0->__add($p1); //$p0 += $p1;
            //$p1 = ($p1 << 39) ^ SHR($p1, (64 - 39)) ^ $p0;
            $p1  = $p1->shiftLeft(39)->__xor($p1->shiftRightUnsigned(64 - 39))->__xor($p0);
            $p2->__add($p3); //$p2 += $p3;
            //$p3 = ($p3 << 30) ^ SHR($p3, (64 - 30)) ^ $p2;
            $p3  = $p3->shiftLeft(30)->__xor($p3->shiftRightUnsigned(64 - 30))->__xor($p2);
            $p4->__add($p5); //$p4 += $p5;
            //$p5 = ($p5 << 34) ^ SHR($p5, (64 - 34)) ^ $p4;
            $p5  = $p5->shiftLeft(34)->__xor($p5->shiftRightUnsigned(64 - 34))->__xor($p4);
            $p6->__add($p7); //$p6 += $p7;
            //$p7 = ($p7 << 24) ^ SHR($p7, (64 - 24)) ^ $p6;
            $p7  = $p7->shiftLeft(24)->__xor($p7->shiftRightUnsigned(64 - 24))->__xor($p6);
            $p2->__add($p1); //$p2 += $p1;
            //$p1 = ($p1 << 13) ^ SHR($p1, (64 - 13)) ^ $p2;
            $p1  = $p1->shiftLeft(13)->__xor($p1->shiftRightUnsigned(64 - 13))->__xor($p2);
            $p4->__add($p7); //$p4 += $p7;
            //$p7 = ($p7 << 50) ^ SHR($p7, (64 - 50)) ^ $p4;
            $p7  = $p7->shiftLeft(50)->__xor($p7->shiftRightUnsigned(64 - 50))->__xor($p4);
            $p6->__add($p5); //$p6 += $p5;
            //$p5 = ($p5 << 10) ^ SHR($p5, (64 - 10)) ^ $p6;
            $p5  = $p5->shiftLeft(10)->__xor($p5->shiftRightUnsigned(64 - 10))->__xor($p6);
            $p0->__add($p3); //$p0 += $p3;
            //$p3 = ($p3 << 17) ^ SHR($p3, (64 - 17)) ^ $p0;
            $p3  = $p3->shiftLeft(17)->__xor($p3->shiftRightUnsigned(64 - 17))->__xor($p0);
            $p4->__add($p1); //$p4 += $p1;
            //$p1 = ($p1 << 25) ^ SHR($p1, (64 - 25)) ^ $p4;
            $p1  = $p1->shiftLeft(25)->__xor($p1->shiftRightUnsigned(64 - 25))->__xor($p4);
            $p6->__add($p3); //$p6 += $p3;
            //$p3 = ($p3 << 29) ^ SHR($p3, (64 - 29)) ^ $p6;
            $p3  = $p3->shiftLeft(29)->__xor($p3->shiftRightUnsigned(64 - 29))->__xor($p6);
            $p0->__add($p5); //$p0 += $p5;
            //$p5 = ($p5 << 39) ^ SHR($p5, (64 - 39)) ^ $p0;
            $p5  = $p5->shiftLeft(39)->__xor($p5->shiftRightUnsigned(64 - 39))->__xor($p0);
            $p2->__add($p7); //$p2 += $p7;
            //$p7 = ($p7 << 43) ^ SHR($p7, (64 - 43)) ^ $p2;
            $p7  = $p7->shiftLeft(43)->__xor($p7->shiftRightUnsigned(64 - 43))->__xor($p2);
            $p6->__add($p1); //$p6 += $p1;
            //$p1 = ($p1 << 8) ^ SHR($p1, (64 - 8)) ^ $p6;
            $p1  = $p1->shiftLeft(8)->__xor($p1->shiftRightUnsigned(64 - 8))->__xor($p6);
            $p0->__add($p7); //$p0 += $p7;
            //$p7 = ($p7 << 35) ^ SHR($p7, (64 - 35)) ^ $p0;
            $p7  = $p7->shiftLeft(35)->__xor($p7->shiftRightUnsigned(64 - 35))->__xor($p0);
            $p2->__add($p5); //$p2 += $p5;
            //$p5 = ($p5 << 56) ^ SHR($p5, (64 - 56)) ^ $p2;
            $p5  = $p5->shiftLeft(56)->__xor($p5->shiftRightUnsigned(64 - 56))->__xor($p2);
            $p4->__add($p3); //$p4 += $p3;
            //$p3 = ($p3 << 22) ^ SHR($p3, (64 - 22)) ^ $p4;
            $p3  = $p3->shiftLeft(22)->__xor($p3->shiftRightUnsigned(64 - 22))->__xor($p4);
            $tmp = clone $t2;
            $t2  = clone $t1;
            $t1  = clone $t0;
            $t0  = $tmp;
        }



        $p0->__add($this->h[18 + 0]);
        $p1->__add($this->h[18 + 1]);
        $p2->__add($this->h[18 + 2]);
        $p3->__add($this->h[18 + 3]);
        $p4->__add($this->h[18 + 4]);
        $p5->__add($this->h[18 + 5]->plus($t0));
        $p6->__add($this->h[18 + 6]->plus($t1));
        $p7->__add($this->h[18 + 7]->plus(new o_u64(0x0, 18)));




        /*
          var_dump((string) $p0);
          var_dump((string) $p1);
          var_dump((string) $p2);
          var_dump((string) $p3);
          var_dump((string) $p4);
          var_dump((string) $p5);
          var_dump((string) $p6);
          var_dump((string) $p7);
          die;

         */

        /*
          var_dump((string) $m0);
          var_dump((string) $m1);
          var_dump((string) $m2);
          var_dump((string) $m3);
          var_dump((string) $m4);
          var_dump((string) $m5);
          var_dump((string) $m6);
          var_dump((string) $m7);
          die; */

        $this->h[0] = ($m0->__xor($p0));
        $this->h[1] = ($m1->__xor($p1));
        $this->h[2] = ($m2->__xor($p2));
        $this->h[3] = ($m3->__xor($p3));
        $this->h[4] = ($m4->__xor($p4));
        $this->h[5] = ($m5->__xor($p5));
        $this->h[6] = ($m6->__xor($p6));
        $this->h[7] = ($m7->__xor($p7));



        /* var_dump((string) $this->h[0]);
          var_dump((string) $this->h[1]);
          var_dump((string) $this->h[2]);
          var_dump((string) $this->h[3]);
          var_dump((string) $this->h[4]);
          var_dump((string) $this->h[5]);
          var_dump((string) $this->h[6]);
          var_dump((string) $this->h[7]);
          die; */
    }

    /** @see Digest */
    private
        function getDigestLength()
    {
        return 32;
    }

    public
        function doHash(array $inputData, array $opts = array())
    {
        return $this->digest1($inputData);
    }

}
