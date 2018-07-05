<?php

function _memcpy(array &$arr1, $off1, $arr2, $off2, $len) {
    for ($i = 0; $i < $len; ++$i)
        $arr1[$off1 + $i] = $arr2[$off2 + $i];
}

function _memcpy_spl(SplFixedArray $arr1, $off1, $arr2, $off2, $len) {
    for ($i = 0; $i < $len; ++$i)
        $arr1[$off1 + $i] = $arr2[$off2 + $i];
}

function ___encodeLELong(o_u64 $val,/* array*/ &$buf, $off) {
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

function ___decodeLELong(array $buf, $off = 0) {
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

final class ___view_uint32 implements ArrayAccess {

    private $buf;

    public function __construct(array &$buf) {
        $this->buf = &$buf;
    }

    public function offsetGet($offset) {

        return (($this->buf[$offset] & 0xFF) |
                (($this->buf[$offset + 1] & 0xFF) << 8) |
                (($this->buf[$offset + 2] & 0xFF) << 16) |
                (($this->buf[$offset + 3] & 0xFF) << 24)) & 0xffffffff;
    }

    public function offsetSet($off, $val) {
        $this->buf[$off + 0] = (($val >> 0) & 0xff);
        $this->buf[$off + 1] = (($val >> 8) & 0xff);
        $this->buf[$off + 2] = (($val >> 16) & 0xff);
        $this->buf[$off + 3] = (($val >> 24) & 0xff);
    }

    public function offsetExists($offset) {
        throw new Exception('Unsupported operation.');
    }

    public function offsetUnset($offset) {
        throw new Exception('Unsupported operation.');
    }

}

final class ___view_uint32_Spl implements ArrayAccess {

    private $buf;

    public function __construct(SplFixedArray $buf) {
        $this->buf = $buf;
    }

    public function offsetGet($offset) {

        return (($this->buf[$offset] & 0xFF) |
                (($this->buf[$offset + 1] & 0xFF) << 8) |
                (($this->buf[$offset + 2] & 0xFF) << 16) |
                (($this->buf[$offset + 3] & 0xFF) << 24)) & 0xffffffff;
    }

    public function offsetSet($off, $val) {
        $this->buf[$off + 0] = (($val >> 0) & 0xff);
        $this->buf[$off + 1] = (($val >> 8) & 0xff);
        $this->buf[$off + 2] = (($val >> 16) & 0xff);
        $this->buf[$off + 3] = (($val >> 24) & 0xff);
    }

    public function offsetExists($offset) {
        throw new Exception('Unsupported operation.');
    }

    public function offsetUnset($offset) {
        throw new Exception('Unsupported operation.');
    }

}

final class ___view_uint64 implements ArrayAccess {

    private $buf;

    public function __construct(array &$buf) {
        $this->buf = &$buf;
    }

    public function offsetGet($offset) {
        return ___decodeLELong($this->buf, 8 * $offset);
    }

    public function offsetSet($offset, $value) {
        ___encodeLELong($value, $this->buf, 8 * $offset);
    }

    public function offsetExists($offset) {
        throw new Exception('Unsupported operation.');
    }

    public function offsetUnset($offset) {
        throw new Exception('Unsupported operation.');
    }

}

final class ___view_uint64_Spl implements ArrayAccess {

    private $buf;

    public function __construct(SplFixedArray $buf) {
        $this->buf = &$buf;
    }

    public function offsetGet($off) {

        $l = (($this->buf[$off] & 0xFF) |
                (($this->buf[$off + 1] & 0xFF) << 8) |
                (($this->buf[$off + 2] & 0xFF) << 16) |
                (($this->buf[$off + 3] & 0xFF) << 24));

        $h = ((($this->buf[$off + 4] & 0xFF) << 0) |
                (($this->buf[$off + 5] & 0xFF) << 8) |
                (($this->buf[$off + 6] & 0xFF) << 16) |
                (($this->buf[$off + 7] & 0xFF) << 24));

        return new o_u64($h, $l);
    }

    public function offsetSet($off, $val) {
        $off *= 8;
        $this->buf[$off + 0] = (($val->lo >> 0) & 0xff);
        $this->buf[$off + 1] = (($val->lo >> 8) & 0xff);
        $this->buf[$off + 2] = (($val->lo >> 16) & 0xff);
        $this->buf[$off + 3] = (($val->lo >> 24) & 0xff);
        ##
        $this->buf[$off + 4] = (($val->hi >> 0) & 0xff);
        $this->buf[$off + 5] = (($val->hi >> 8) & 0xff);
        $this->buf[$off + 6] = (($val->hi >> 16) & 0xff);
        $this->buf[$off + 7] = (($val->hi >> 24) & 0xff);
    }

    public function offsetExists($offset) {
        throw new Exception('Unsupported operation.');
    }

    public function offsetUnset($offset) {
        throw new Exception('Unsupported operation.');
    }

}
