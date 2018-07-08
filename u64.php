<?php

function SHR($x, $c) {
    $nmaxBits = PHP_INT_SIZE * 8;
    $c %= $nmaxBits;

        return (int)$x >> $c & ~ (-1 << $nmaxBits - $c);

}

final class o_u64 {


    function __construct($h, $l) {
        $this->hi = $h; // >>> 0;
        $this->lo = $l; // >>> 0;

    }

    function set(o_u64 $oWord) {
        throw new Exception('No plz');
        /* $this->lo = $oWord->lo;
          $this->hi = $oWord->hi; */
    }

    function __add(o_u64 $oWord) {

        $this_h = $this->hi & 0xffffffff;
        $this_l = $this->lo & 0xffffffff;
        $o_h = $oWord->hi & 0xffffffff;
        $o_l = $oWord->lo & 0xffffffff;

        //var lowest, lowMid, highMid, highest; //four parts of the whole 64 bit number..
        //need to add the respective parts from each number and the carry if on is present..

	 $lowest = (($this_l & 0XFFFF) + ($o_l & 0XFFFF))& 0xffffffff;

		
        $lowMid = (SHR($this_l, 16) + SHR($o_l, 16) + SHR($lowest, 16)) & 0xffffffff;
        $highMid = ($this_h & 0XFFFF) + ($o_h & 0XFFFF) + SHR($lowMid, 16) & 0xffffffff;
        $highest = SHR($this_h, 16) + SHR($o_h, 16) + SHR($highMid, 16) ;

        //now set the hgih and the low accordingly..
        $this->lo = (($lowMid << 16) | ($lowest & 0XFFFF));
        $this->hi = (($highest << 16) | ($highMid & 0XFFFF));

        return $this; //for chaining..
    }

    function addOne() {
        if ($this->lo === -1 || $this->lo === 0xFFFFFFFF) {
            $this->lo = 0;
            $this->hi++;
        } else {
            $this->lo++;
        }
    }

    function plus(o_u64 $oWord) {
		
        $c = new o_u64(0, 0);
//  var lowest, lowMid, highMid, highest; //four parts of the whole 64 bit number..
        //need to add the respective parts from each number and the carry if on is present..

     
	 $this_h = $this->hi & 0xffffffff;
        $this_l = $this->lo & 0xffffffff;
        $o_h = $oWord->hi & 0xffffffff;
        $o_l = $oWord->lo & 0xffffffff;

        //var lowest, lowMid, highMid, highest; //four parts of the whole 64 bit number..
        //need to add the respective parts from each number and the carry if on is present..
		
	    
        $lowest = (($this_l & 0XFFFF) + ($o_l & 0XFFFF))& 0xffffffff;
		
        $lowMid = (SHR($this_l, 16) + SHR($o_l, 16) + SHR($lowest, 16)) & 0xffffffff;
       

		$highMid = (($this_h & 0XFFFF) + ($o_h & 0XFFFF) + SHR($lowMid, 16)) & 0xffffffff;
        $highest = (SHR($this_h, 16) + SHR($o_h, 16) + SHR($highMid, 16)) & 0xffffffff;
 
        //now set the hgih and the low accordingly..
        $c->lo = (($lowMid << 16) | ($lowest & 0XFFFF))& 0xffffffff;
        $c->hi = ((($highest << 16) | ($highMid & 0XFFFF))) & 0xffffffff;

        return $c; //for chaining..
    }

    function not() {
        return new o_u64(~$this->hi, ~$this->lo);
    }

    function one() {
       // throw new Exception('No plz');
        return new o_u64(0x0, 0x1);
    }

    function zero() {
        //throw new Exception('No plz');
        return new o_u64(0x0, 0x0);
    }

    function neg() {
       // throw new Exception('No plz');
        return $this->not()->plus($this->one());
    }

    function minus(o_u64 $oWord) {
        //throw new Exception('No plz');
        return $this->plus($oWord->neg());
    }

    function isZero() {
        //throw new Exception('No plz');
        return ($this->lo === 0) && ($this->hi === 0);
    }

#function isLong($obj) {
#  return ($obj && $obj["__isLong__"]) === true;
#}
#function fromNumber(value) {
#  if (isNaN(value) || !isFinite(value))
#    return this.zero();
#  var pow32 = (1 << 32);
#  return new u64((value % pow32) | 0, (value / pow32) | 0);
#}

    function multiply(o_u64 $multiplier) {
        #throw new Exception('No plz');
        //if ($this->isZero())
         //   return $this->zero();
        #if (!isLong(multiplier))
        #  multiplier = fromNumber(multiplier);
      //  if ($multiplier->isZero())
           // return $this->zero();

        // Divide each long into 4 chunks of 16 bits, and then add up 4x4 products.
        // We can skip products that would overflow.

        $a48 = $this->hi >> 16 & 0xFFFF;
        $a32 = $this->hi & 0xFFFF;
        $a16 = $this->lo >> 16 & 0xFFFF;
        $a00 = $this->lo & 0xFFFF;

        $b48 = $multiplier->hi >> 16 & 0xFFFF;
        $b32 = $multiplier->hi & 0xFFFF;
        $b16 = $multiplier->lo >> 16 & 0xFFFF;
        $b00 = $multiplier->lo & 0xFFFF;

        $c48 = 0;
        $c32 = 0;
        $c16 = 0;
        $c00 = 0;

        $c00 += $a00 * $b00;
        $c16 += $c00 >> 16;
        $c00 &= 0xFFFF;
		
        $c16 += $a16 * $b00;
        $c32 += $c16 >> 16;
        $c16 &= 0xFFFF;
		
        $c16 += $a00 * $b16;
        $c32 += $c16 >> 16;
        $c16 &= 0xFFFF;
		
        $c32 += $a32 * $b00;
        $c48 += $c32 >> 16;
        $c32 &= 0xFFFF;
		
        $c32 += $a16 * $b16;
        $c48 += $c32 >> 16;
        $c32 &= 0xFFFF;
		
        $c32 += $a00 * $b32;
        $c48 += $c32 >> 16;
        $c32 &= 0xFFFF;
		
        $c48 += $a48 * $b00 + $a32 * $b16 + $a16 * $b32 + $a00 * $b48;
        $c48 &= 0xFFFF;


        return new o_u64((($c48 << 16) | $c32)& 0xffffffff, (($c16 << 16) | $c00 )& 0xffffffff);
    }

    function shiftLeft($bits) {

        $this_h = $this->hi & 0xffffffff;
        $this_l = $this->lo & 0xffffffff;
        $bits %= 64;
        $c = new o_u64(0, 0);
        if ($bits === 0) {
            return clone $this;
        } else if($bits == 32){
			  $c->lo = 0;
            $c->hi = $this_l;
		} else if ($bits > 32) {
            $c->lo = 0;
            $c->hi = $this_l << ($bits - 32);
        } else {
            $toMoveUp = SHR($this_l, 32 - $bits);
            $c->lo = ($this_l << $bits)& 0xffffffff;
            $c->hi =(($this_h << $bits) | $toMoveUp)& 0xffffffff;
        }
        return $c; //for chaining..
    }

    function setShiftLeft($bits) {
        throw new Exception('No plz');
        if ($bits === 0) {
            return $this;
        }
        if ($bits > 63) {
            $bits = $bits % 64;
        }

        if ($bits > 31) {
            $this->hi = $this->lo << ($bits - 32);
            $this->lo = 0;
        } else {
            $toMoveUp = $this->lo >> 32 - $bits;
            $this->lo <<= $bits;
            $this->hi = ($this->hi << $bits) | $toMoveUp;
        }
        return $this; //for chaining..
    }

//Shifts this word by the given number of bits to the right (max 32)..
    function shiftRight($bits) {


        $bits %= 64;

        if ($bits === 0)
            return clone $this;

        $this_h = $this->hi & 0xffffffff;
        $this_l = $this->lo & 0xffffffff;
        $nmaxBits = PHP_INT_SIZE * 8;

        $c = new o_u64(0, 0);

        if ($bits >= 32) {//больше 32
            $c->hi = -1;
            $c->lo = ($this_h >> ($bits - 32)) | (-1 << (64 - $bits));
        } else {//до 32
            $c->hi = ($this_h >> $bits) | (-1 << (32 - $bits));
            $c->lo = (SHR($this_l, $bits) | ($this_h << (32 - $bits))) &
                    ~(-1 << ($nmaxBits - $bits));
        }
        return $c; //for chaining..
    }
	
 
    function shiftRightUnsigned($bits) {
	
        $bits %= 64;
		

        if ($bits === 0)
            return clone $this;

        $this_h = $this->hi & 0xffffffff;
        $this_l = $this->lo & 0xffffffff;

        $c = new o_u64(0,0);

 if ($bits == 32) {
	
	 $c->lo = $this_h ;
	 	 

 }
	else
        if ($bits > 32) {
       	
	   $c->hi = 0;
		
            $c->lo = ($this_h >> ($bits - 32));
		
        } else {
            $bitsOff32 = 32 - $bits;
            $toMoveDown = $this_h << $bitsOff32 >> $bitsOff32;

            $c->hi = $this_h >> $bits & 0xffffffff;
            $c->lo = ($this_l >> $bits | ($toMoveDown << $bitsOff32) ) & 0xffffffff;
        }
		
		
        return $c; //for chaining..
    }

//Rotates the bits of this word round to the left (max 32)..
    function rotateLeft($bits) {
        #throw new Exception('No plz');
        if ($bits > 32) {
            return $this->rotateRight(64 - $bits);
        }
        $c = new o_u64(0, 0);
        if ($bits === 0) {
            $c->lo = $this->lo >> 0;
            $c->hi = $this->hi >> 0;
        } else if ($bits === 32) { //just switch high and low over in this case..
            $c->lo = $this->hi;
            $c->hi = $this->lo;
        } else {
            $c->lo = (($this->lo << $bits) | ($this->hi >> (32 - $bits))) & 0xffffffff;
            $c->hi = (($this->hi << $bits) | ($this->lo >> (32 - $bits))) & 0xffffffff;
        }
	
        return $c; //for chaining..
    }

    function setRotateLeft($bits) {
        throw new Exception('No plz');
        if ($bits > 32) {
            return $this->setRotateRight(64 - $bits);
        }
        $newHigh = 0;
        if ($bits === 0) {
            return $this;
        } else if ($bits === 32) { //just switch high and low over in this case..
            $newHigh = $this->lo;
            $this->lo = $this->hi;
            $this->hi = $newHigh;
        } else {
            $newHigh = ($this->hi << $bits) | ($this->lo >> (32 - $bits));
            $this->lo = ($this->lo << $bits) | ($this->hi >> (32 - $bits));
            $this->hi = $newHigh;
        }
        return $this; //for chaining..
    }

//Rotates the bits of this word round to the right (max 32)..
    function rotateRight($bits) {
        #throw new Exception('No plz');
        if ($bits > 32) {
            return $this->rotateLeft(64 - $bits);
        }
        $c = new o_u64(0, 0);
        if ($bits === 0) {
            $c->lo = $this->lo >> 0;
            $c->hi = $this->hi >> 0;
        } else if ($bits === 32) { //just switch high and low over in this case..
            $c->lo = $this->hi;
            $c->hi = $this->lo;
        } else {
            $c->lo = (($this->hi << (32 - $bits)) | ($this->lo >> $bits)) & 0xffffffff;
            $c->hi = (($this->lo << (32 - $bits)) | ($this->hi >> $bits)) & 0xffffffff;
        }

		
        return $c; //for chaining..
    }

    function setFlip() {
  
        #$newHigh;
        $newHigh = $this->lo;
        $this->lo = $this->hi;
        $this->hi = $newHigh;
        return $this;
    }
  function Flip() {
  
        #$newHigh;
		$new=clone $this;
		
 
        $new->lo = $this->hi;
        $new->hi = $this->lo;
        return $new;
    }

//Rotates the bits of this word round to the right (max 32)..
    function setRotateRight($bits) {
        throw new Exception('No plz');
        if ($bits > 32) {
            return $this->setRotateLeft(64 - $bits);
        }

        if ($bits === 0) {
            return $this;
        } else if ($bits === 32) { //just switch high and low over in this case..
            #$newHigh;
            $newHigh = $this->lo;
            $this->lo = $this->hi;
            $this->hi = $newHigh;
        } else {
            $newHigh = ($this->lo << (32 - $bits)) | ($this->hi >> $bits);
            $this->lo = ($this->hi << (32 - $bits)) | ($this->lo >> $bits);
            $this->hi = $newHigh;
        }
	
		
        return $this; //for chaining..
    }

//Xors this word with the given other..
    function __xor(o_u64 $oWord) {
        $this_h = $this->hi & 0xffffffff;
        $this_l = $this->lo & 0xffffffff;
        $o_h = $oWord->hi & 0xffffffff;
        $o_l = $oWord->lo & 0xffffffff;
        $c = new o_u64(0, 0);
        $c->hi = $this_h ^ $o_h;
        $c->lo = $this_l ^ $o_l;
        return $c; //for chaining..
    }

//Xors this word with the given other..
    function setxorOne(o_u64 $oWord) {
        $o_h = $oWord->hi & 0xffffffff;
        $o_l = $oWord->lo & 0xffffffff;
        $this_h = $this->hi & 0xffffffff;
        $this_l = $this->lo & 0xffffffff;
        $this->hi = $this_h ^ $o_h;
        $this->lo = $this_l ^ $o_l;
        return $this; //for chaining..
    }

//Ands this word with the given other..
    function __and(o_u64 $oWord) {

        $c = new o_u64(0, 0);
        $c->hi = $this->hi & $oWord->hi;
        $c->lo = $this->lo & $oWord->lo;
        return $c; //for chaining..
    }
   function __or(o_u64 $oWord) {

        $c = new o_u64(0, 0);
        $c->hi = $this->hi | $oWord->hi;
        $c->lo = $this->lo | $oWord->lo;
        return $c; //for chaining..
    }
//Creates a deep copy of this Word..
    function __clone() {
        return new o_u64($this->hi, $this->lo);
    }

    function setxor64() {
        $a = func_get_args();
        $i = func_num_args();
        while ($i--) {
            $this_h = $this->hi & 0xffffffff;
            $this_l = $this->lo & 0xffffffff;
            $el_h = $a[$i]->hi & 0xffffffff;
            $el_l = $a[$i]->lo & 0xffffffff;
            $this->hi = $this_h ^ $el_h;
            $this->lo = $this_l ^ $el_l;
        }
        return $this;
    }

    function __toString() {
        #return sprintf('u64 (hi:%x lo:%x)'
        return sprintf("%08x %08x\n###\n%032b %032b"
                , $this->hi & 0xffffffff, $this->lo & 0xffffffff
                , $this->hi & 0xffffffff, $this->lo & 0xffffffff);
    }

}

function o_u($h, $l) {
    return new o_u64($h, $l);
}

function xor64() {
    $a = func_get_args();
    $h = $a[0]->hi;
    $l = $a[0]->lo;
    $i = count($a) - 1;

    do {
        $h ^= $a[$i]->hi;
        $l ^= $a[$i]->lo;
        $i--;
    } while ($i > 0);
    return new o_u64($h, $l);
}

function clone64Array(array $arr) {
    $i = 0;
    $len = count($arr);
    $a = Array();
    while ($i < $len) {
        $a[$i] = $arr[$i];
        $i++;
    }
    return $a;
}

//this shouldn't be a problem, but who knows in the future javascript might support 64bits
function t32($x) {
    return ($x & 0xFFFFFFFF);
}

function rotl32($x, $c) {
    return ((($x) << ($c)) | (($x) >> (32 - ($c)))) & (0xFFFFFFFF);
}

function rotr32($x, $c) {
    return rotl32($x, (32 - ($c)));
}

function swap32($val) {
    return (($val & 0xFF) << 24) |
            (($val & 0xFF00) << 8) |
            (($val >> 8) & 0xFF00) |
            (($val >> 24) & 0xFF);
}

function swap32Array(array $a) {
    //can't do this with map because of support for IE8 (Don't hate me plz).
    $i = 0;
    $len = count($a);
    $r = Array();
    while ($i < $len) {
        $r[$i] = (swap32($a[$i]));
        $i++;
    }
    return $r;
}

function xnd64($x, $y, $z) {
    return new o_u64($x->hi ^ ((~$y->hi) & $z->hi), $x->lo ^ ((~$y->lo) & $z->lo));
}

/*
  module.exports.load64 = function(x, i) {
  var l = x[i] | (x[i + 1] << 8) | (x[i + 2] << 16) | (x[i + 3] << 24);
  var h = x[i + 4] | (x[i + 5] << 8) | (x[i + 6] << 16) | (x[i + 7] << 24);
  return new this.u64(h, l);
  }
 */

function bufferInsert(&$buffer, $bufferOffset, $data, $len, $dataOffset = 0) {

    $i = 0;
    while ($i < $len) {
        $buffer[$i + $bufferOffset] = $data[$i + $dataOffset];
        $i++;
    }
}

function bufferInsert64(&$buffer, $bufferOffset, $data, $len) {
    $i = 0;
    while ($i < $len) {
        $buffer[$i + $bufferOffset] = clone $data[$i];
        $i++;
    }
}

/*
  module.exports.buffer2Insert = function(buffer, bufferOffset, bufferOffset2, data, len, len2) {
  while (len--) {
  var j = len2;
  while (j--) {
  buffer[len + bufferOffset][j + bufferOffset2] = data[len][j];
  }
  }
  }
 */

function bufferInsertBackwards(&$buffer, $bufferOffset, $data, $len) {
    $i = 0;
    while ($i < $len) {
        $buffer[$i + $bufferOffset] = $data[$len - 1 - $i];
        $i++;
    }
}

function bufferSet(&$buffer, $bufferOffset, $value, $len) {
    $i = 0;
    while ($i < $len) {
        $buffer[$i + $bufferOffset] = $value;
        $i++;
    }
}

function bufferXORInsert(&$buffer, $bufferOffset, $data, $dataOffset, $len) {
    $i = 0;
    while ($i < $len) {
        $buffer[$i + $bufferOffset] ^= $data[$i + $dataOffset];
        $i++;
    }
}

function xORTable(&$d, $s1, $s2, $len) {
    $i = 0;
    while ($i < $len) {
        $d[$i] = $s1[$i] ^ $s2[$i];
        $i++;
    }
}
function strReplace(&$buffer,$rm,$offset,$len){
	$left=substr($buffer,0,$offset);
	$right=substr($buffer,$offset+$len,strlen($buffer));
	
}


function bufferEncode64_str(&$buffer, $offset, $uint64) {

	$ret="";
    $ret.= chr($uint64->hi >> 24 & 0xFF);
    $ret.= chr($uint64->hi >> 16 & 0xFF);
    $ret.= chr($uint64->hi >> 8 & 0xFF);
    $ret.= chr($uint64->hi & 0xFF);
    $ret.= chr($uint64->lo >> 24 & 0xFF);
    $ret.= chr($uint64->lo >> 16 & 0xFF);
    $ret.= chr($uint64->lo >> 8 & 0xFF);
    $ret.= chr($uint64->lo & 0xFF);
strReplace($buffer,$ret,$offset,8);


echo "$tm\n";		
	
	exit();
}
function bufferEncode64_str_(&$buffer, $offset, $uint64) {
	$ret="";
    $ret.= chr($uint64->hi >> 0 & 0xFF);
    $ret.= chr($uint64->hi >> 8 & 0xFF);
    $ret.= chr($uint64->hi >> 16 & 0xFF);
    $ret.= chr($uint64->hi >>24 & 0xFF);
    $ret.= chr($uint64->lo >> 0 & 0xFF);
    $ret.= chr($uint64->lo >> 8 & 0xFF);
    $ret.= chr($uint64->lo >> 16 & 0xFF);
    $ret.= chr($uint64->lo >> 24 & 0xFF);
	//$buffer=strReplace($buffer,$ret,$offset,8);
	
}
function bufferEncode64(&$buffer, $offset, $uint64) {
    $buffer[$offset] = $uint64->hi >> 24 & 0xFF;
    $buffer[$offset + 1] = $uint64->hi >> 16 & 0xFF;
    $buffer[$offset + 2] = $uint64->hi >> 8 & 0xFF;
    $buffer[$offset + 3] = $uint64->hi & 0xFF;
    $buffer[$offset + 4] = $uint64->lo >> 24 & 0xFF;
    $buffer[$offset + 5] = $uint64->lo >> 16 & 0xFF;
    $buffer[$offset + 6] = $uint64->lo >> 8 & 0xFF;
    $buffer[$offset + 7] = $uint64->lo & 0xFF;
}

function getBuffer64_B( $offset, $uint64) {
	$buffer=array_fill(0,8,0);
    $buffer[$offset] = $uint64->lo >> 0 & 0xFF;
    $buffer[$offset + 1] = $uint64->lo >> 8 & 0xFF;
    $buffer[$offset + 2] = $uint64->lo >> 16 & 0xFF;
    $buffer[$offset + 3] = $uint64->lo>> 24 & 0xFF;
	
    $buffer[$offset + 4] = $uint64->hi >> 0 & 0xFF;
    $buffer[$offset + 5] = $uint64->hi >> 8 & 0xFF;
    $buffer[$offset + 6] = $uint64->hi >> 16 & 0xFF;
    $buffer[$offset + 7] = $uint64->hi >> 24 & 0xFF;
	return $buffer;
}

function bufferEncode64_(&$buffer, $offset, $uint64) {
    $buffer[$offset] = $uint64->hi >> 0 & 0xFF;
    $buffer[$offset + 1] = $uint64->hi >> 8 & 0xFF;
    $buffer[$offset + 2] = $uint64->hi >> 16 & 0xFF;
    $buffer[$offset + 3] = $uint64->hi >>24 & 0xFF;
    $buffer[$offset + 4] = $uint64->lo >> 0 & 0xFF;
    $buffer[$offset + 5] = $uint64->lo >> 8 & 0xFF;
    $buffer[$offset + 6] = $uint64->lo >> 16 & 0xFF;
    $buffer[$offset + 7] = $uint64->lo >> 24 & 0xFF;
}
function b2int64_offset(&$b,$of){
	return new o_u64(
                ($b[$of+0] << 24) | ($b[ $of+1] << 16) | ($b[ $of+ 2] << 8) | $b[ $of+3]
                , ($b[ $of+4] << 24) | ($b[ $of+5] << 16) | ($b[$of+6] << 8) | $b[$of+ 7]);
}
function b2int64($b){
	return new o_u64(
                ($b[0] << 24) | ($b[ 1] << 16) | ($b[  2] << 8) | $b[ 3]
                , ($b[ 4] << 24) | ($b[ 5] << 16) | ($b[6] << 8) | $b[ 7]);
}
function b2int64_B($b){
	return new o_u64(
           ($b[ 4] << 24) | ($b[ 5] << 16) | ($b[6] << 8) | ($b[ 7])
                ,     ($b[0] << 24) | ($b[ 1] << 16) | ($b[  2] << 8) | ($b[ 3]) );
}

function b2int64_($b){
	return new o_u64(
                ($b[0] << 0) | ($b[ 1] << 8) | ($b[  2] <<16) | $b[ 3]<<24
                , ($b[ 4] ) | ($b[ 5] << 8) | ($b[6] << 16) | $b[ 7]<<24);
}
function b32toint($x,$offset=0){
	 return ($x[$offset+0] |$x[$offset+1]<<8 |$x[$offset+2]<<16 |$x[$offset+3]<<24);
}


function bytes2Int64Buffer($b) {
    if (!$b)
        return [];
    $len = count($b) ? (((count($b) - 1) >> 3) + 1) : 0;
	
    $buffer = Array();
    $j = 0;
    while ($j < $len) {
        $buffer[$j] = new o_u64(
                ($b[$j * 8] << 24) | ($b[$j * 8 + 1] << 16) | ($b[$j * 8 + 2] << 8) | $b[$j * 8 + 3]
                , ($b[$j * 8 + 4] << 24) | ($b[$j * 8 + 5] << 16) | ($b[$j * 8 + 6] << 8) | $b[$j * 8 + 7]);
        $j++;
    }
    return $buffer;
}

function int32Buffer2Bytes($b) {
    $buffer = array_fill(0, count($b), 0);
    $len = count($b);
    $i = 0;
    while ($i < $len) {
        $buffer[$i * 4] = ($b[$i] & 0xFF000000) >> 24;
        $buffer[$i * 4 + 1] = ($b[$i] & 0x00FF0000) >> 16;
        $buffer[$i * 4 + 2] = ($b[$i] & 0x0000FF00) >> 8;
        $buffer[$i * 4 + 3] = ($b[$i] & 0x000000FF);
        $i++;
    }
    return $buffer;
}



