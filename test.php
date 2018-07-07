<?php
require_once("u64.php");
require_once("keccak.php");

function slice(&$ar,$loc,$len){
	$new=array_fill(0,$len,0);
	for($i=0;$i<$len;$i++)
	$new[$i]=$ar[$loc+$i];
return $new;
}
function /* uint64_t */ mul128(/* uint64_t */ $multiplier, /* uint64_t */ $multiplicand, /* uint64_t * */ array &$product_hi) {//This mul for uint64_t copy from php-crypto

    $a = $multiplier->shiftRight(32);
    $b = $multiplier->__and(new o_u64(0xFFFFFFFF, 0xFFFFFFFF));
    $c = $multiplicand->shiftRight(32);
    $d = $multiplicand->__and(new o_u64(0xFFFFFFFF, 0xFFFFFFFF));
    $ad = $a->multiply($d);
    $bd = $b->multiply($d);
    $adbc = $ad->plus($b->multiply($c));
    $adbc_carry = ($adbc->hi < $ad->hi && $adbc->lo < $ad->lo) ?
            new o_u64(0, 1) : new o_u64(0, 0);
    $product_lo = $bd->plus($adbc->shiftLeft(32));
    $product_lo_carry = ($product_lo->hi < $bd->hi && $product_lo->lo < $bd->lo) ?
            new o_u64(0, 1) : new o_u64(0, 0);
    $product_hi = $a->multiply($c)
            ->plus($adbc->shiftRight(32))
            ->plus($adbc_carry->shiftLeft(32))
            ->plus($product_lo_carry);
    return $product_lo;
}

function mul_sum_xor_dst($a,&$c,&$dst,$offset=0){//char $a char $c

	$hi=___decodeLELong(slice($a,0,8),0);
	$lo=___decodeLELong(slice($dst,$offset+0,8),0);
		
			
	$u1=$hi->__and(o_u(0,0xffffffff));

	$v1=$lo->__and(o_u(0,0xffffffff));
	
	
	$t=$u1->multiply($v1);


	$w3=$t->__and(o_u(0,0xffffffff));


	$k=$t->shiftRightUnsigned(32);

	$hi=$hi->shiftRightUnsigned(32);

	$t=$hi->multiply($v1)->plus($k);
			

	$k=$t->__and(o_u(0,0xffffffff));

	$v1=$t->shiftRightUnsigned(32);
	
	$lo=$lo->shiftRightUnsigned(32);
		
	$t=$u1->multiply($lo)->plus($k);
	
	$k=$t->shiftRightUnsigned(32);

	$hi=$hi->multiply($lo)->plus($v1)->plus($k);
	

	$lo=$t->shiftLeft(32)->plus($w3);
	
	
	$lo=$lo->plus(___decodeLELong(slice($c,8,8),0));
	$hi=$hi->plus(___decodeLELong(slice($c,0,8),0));
			
		
/*		$tmp=array_fill(0,8,0);

	bufferEncode64($tmp,0,$lo);

		print_r($tmp);*/
	___encodeLELong(___decodeLELong(slice($dst,$offset+0,8),0)->__xor($hi),$c,0);
	___encodeLELong(___decodeLELong(slice($dst,$offset+8,8),0)->__xor($lo),$c,8);


/*if($hi->hi & 0xFF  >255){
		print_r($hi );
		exit();
		
}*/
	___encodeLELong($hi,$dst,$offset+0);
	___encodeLELong($lo,$dst,$offset+8);
	
	
	
}
$a=[150,63,229,152,61,168,138,202,25,130,255,44,72,173,253,111];
$c=[85,115,145,139,52,104,60,9,147,40,225,26,229,35,215,183];
$dst=[130,33,17,77,106,106,99,209,143,202,217,144,124,30,24,58];
mul_sum_xor_dst($a,$c,$dst);
print_r($c);

?>
