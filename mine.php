<?php
//namespace mine;

require_once("groestl256.php");
require_once("skein.php");
require_once("jh.php");
require_once("oaes.php");
require_once("keccak.php");
require_once("blake256.php");



function groestl256($in){
	$out = array_fill(0, 32, 0);
crypto_hash_gl($out,$in,count($in));
	return $out;
}

function skein($in){
	$s=new Skein256;
	return $s->digest1($in);
}
function jh($in){
	 return array_slice(jh_($in, 256, count($in) * 8),0,32);
}
function keccak_($in){
	$m=array();
    keccak1600($in,count($in),$m);
	return $m;
}
function blake256($in){
	$o=array();
	blake256_hash($o,$in,count($in));
	return $o;
}
$sub = array(
    // 		0,    1,    2,    3,    4,    5,    6,    7,    8,    9,    a,    b,    c,    d,    e,    f,
    /* 0 */ array(0x63, 0x7c, 0x77, 0x7b, 0xf2, 0x6b, 0x6f, 0xc5, 0x30, 0x01, 0x67, 0x2b, 0xfe, 0xd7, 0xab, 0x76),
    /* 1 */ array(0xca, 0x82, 0xc9, 0x7d, 0xfa, 0x59, 0x47, 0xf0, 0xad, 0xd4, 0xa2, 0xaf, 0x9c, 0xa4, 0x72, 0xc0),
    /* 2 */ array(0xb7, 0xfd, 0x93, 0x26, 0x36, 0x3f, 0xf7, 0xcc, 0x34, 0xa5, 0xe5, 0xf1, 0x71, 0xd8, 0x31, 0x15),
    /* 3 */ array(0x04, 0xc7, 0x23, 0xc3, 0x18, 0x96, 0x05, 0x9a, 0x07, 0x12, 0x80, 0xe2, 0xeb, 0x27, 0xb2, 0x75),
    /* 4 */ array(0x09, 0x83, 0x2c, 0x1a, 0x1b, 0x6e, 0x5a, 0xa0, 0x52, 0x3b, 0xd6, 0xb3, 0x29, 0xe3, 0x2f, 0x84),
    /* 5 */ array(0x53, 0xd1, 0x00, 0xed, 0x20, 0xfc, 0xb1, 0x5b, 0x6a, 0xcb, 0xbe, 0x39, 0x4a, 0x4c, 0x58, 0xcf),
    /* 6 */ array(0xd0, 0xef, 0xaa, 0xfb, 0x43, 0x4d, 0x33, 0x85, 0x45, 0xf9, 0x02, 0x7f, 0x50, 0x3c, 0x9f, 0xa8),
    /* 7 */ array(0x51, 0xa3, 0x40, 0x8f, 0x92, 0x9d, 0x38, 0xf5, 0xbc, 0xb6, 0xda, 0x21, 0x10, 0xff, 0xf3, 0xd2),
    /* 8 */ array(0xcd, 0x0c, 0x13, 0xec, 0x5f, 0x97, 0x44, 0x17, 0xc4, 0xa7, 0x7e, 0x3d, 0x64, 0x5d, 0x19, 0x73),
    /* 9 */ array(0x60, 0x81, 0x4f, 0xdc, 0x22, 0x2a, 0x90, 0x88, 0x46, 0xee, 0xb8, 0x14, 0xde, 0x5e, 0x0b, 0xdb),
    /* a */ array(0xe0, 0x32, 0x3a, 0x0a, 0x49, 0x06, 0x24, 0x5c, 0xc2, 0xd3, 0xac, 0x62, 0x91, 0x95, 0xe4, 0x79),
    /* b */ array(0xe7, 0xc8, 0x37, 0x6d, 0x8d, 0xd5, 0x4e, 0xa9, 0x6c, 0x56, 0xf4, 0xea, 0x65, 0x7a, 0xae, 0x08),
    /* c */ array(0xba, 0x78, 0x25, 0x2e, 0x1c, 0xa6, 0xb4, 0xc6, 0xe8, 0xdd, 0x74, 0x1f, 0x4b, 0xbd, 0x8b, 0x8a),
    /* d */ array(0x70, 0x3e, 0xb5, 0x66, 0x48, 0x03, 0xf6, 0x0e, 0x61, 0x35, 0x57, 0xb9, 0x86, 0xc1, 0x1d, 0x9e),
    /* e */ array(0xe1, 0xf8, 0x98, 0x11, 0x69, 0xd9, 0x8e, 0x94, 0x9b, 0x1e, 0x87, 0xe9, 0xce, 0x55, 0x28, 0xdf),
    /* f */ array(0x8c, 0xa1, 0x89, 0x0d, 0xbf, 0xe6, 0x42, 0x68, 0x41, 0x99, 0x2d, 0x0f, 0xb0, 0x54, 0xbb, 0x16),
);
function sub_bytes(&$state){
	global $sub;
	for($i=0;$i<4;$i++)
		for($j=0;$j<4;$j++)
		{
			$row=($state[4*$i+$j]&0xf0)>>4;
			$col=$state[4*$i+$j]&0x0f;
			$state[4*$i+$j]=$sub[$row][$col];
		}
}
function mix_columns(&$state) {
oaes_mix_cols($state,0);
oaes_mix_cols($state,4);
oaes_mix_cols($state,8);
oaes_mix_cols($state,12);

}
//Nb=4,Nr=10;
function intToBytes(int $value )   
{   
    $src[3] =  (($value>>24) );  
    $src[2] =   (($value>>16) );  
    $src[1] =  (($value>>8) );    
    $src[0] =   ($value);                  
    return $src;   
}  

function add_round_key($state, $p ) {
	
	$w=intToBytes($p);
	
	for ($c = 0; $c < 4; $c++) {
		$state[4*0+$c] = $state[4*0+$c]^$w[0];   //debug, so it works for Nb !=4 
		$state[4*1+$c] = $state[4*1+$c]^$w[1];
		$state[4*2+$c] = $state[4*2+$c]^$w[2];
		$state[4*3+$c] = $state[4*3+$c]^$w[3];	
	}
}
/*function aes_round(array &$block,$round_key){ //low effiency
	sub_bytes($block);
		oaes_shift_rows($block);
mix_columns($block);//too slow
add_round_key($block,$round_key);

}*/


function SubAndShiftAndMixAddRoundInPlace(array &$out,$round_key){ // int[] round_key
	global $table1,$table2,$table3,$table4;
	$save=array_fill(0,16,0);
	
    $nb = $table1[$out[0]] ^ $table2[$out[5]] ^ $table3[$out[10]] ^ $table4[$out[15]] ^ ($round_key[0]<<0 |$round_key[1]<<8 |$round_key[2]<<16 |$round_key[3]<<24);//4byte
	$save[0]=$nb & 0xFF;$save[1]=$nb>>8 & 0xFF;$save[2]=$nb>>16  & 0xFF;$save[3]=$nb>>24 ;

    $nb =  $table4[$out[3]] ^ $table1[$out[4]] ^ $table2[$out[9]] ^ $table3[$out[14]] ^ ($round_key[4]<<0 |$round_key[5]<<8 |$round_key[6]<<16 |$round_key[7]<<24);//4byte
	$save[4]=$nb & 0xFF;$save[5]=$nb>>8 & 0xFF;$save[6]=$nb>>16  & 0xFF;$save[7]=$nb>>24;

	 
    $nb = $table3[$out[2]] ^ $table4[$out[7]] ^ $table1[$out[8]] ^ $table2[$out[13]] ^ ($round_key[8]<<0 |$round_key[9]<<8 |$round_key[10]<<16 |$round_key[11]<<24);//4byte
	$save[8]=$nb & 0xFF;$save[9]=$nb>>8 & 0xFF;$save[10]=$nb>>16  & 0xFF;$save[11]=$nb>>24;

    $nb = $table2[$out[1]] ^ $table3[$out[6]] ^ $table4[$out[11]] ^ $table1[$out[12]] ^ ($round_key[12]<<0 |$round_key[13]<<8 |$round_key[14]<<16 |$round_key[15]<<24);//4byte
	$save[12]=$nb & 0xFF;$save[13]=$nb>>8 & 0xFF;$save[14]=$nb>>16  & 0xFF;$save[15]=$nb>>24;

$out=$save;


}
function aes_round_out(array $block,int $loc,$round_key){

	$$block=array_slice($block,$loc,16);
		sub_bytes($$block);
		oaes_shift_rows($$block);
mix_columns($$block);//too slow
add_round_key($$block,$round_key);
return $$block;
}
function shift_rows(&$state) {
/*
	for ($i = 1; $i < 4; $i++) {
		$s = 0;
		while ($s < $i) {
			$tmp = $state[4*$i+0];
			
			for ($k = 1; $k < 4; $k++) {
				$state[4*$i+$k-1] = $state[4*$i+$k];
			}

			$state[4*$i+4-1] = $tmp;
			$s++;
		}
	}*/
}

function xor_($x,$y)
{
	$res=array_fill(0,count($x),0);
	for($i=0;$i<count($x);++$i)
		$res[$i]=$x[$i]^$y[$i];
	
	return $res;
}
function e2i($x){
	return $a[0] & 0x1FFFF0;
}
function mul_sum_xor_dst(&$a,&$c,&$dst){//char $a char $c
	$hi=b2int64(array_slice($a,0,8));
	$lo=b2int64(array_slice($dst,0,8));
	$u1=$hi->__and(o_u(0xffffffff,0));
	$v1=$lo->__and(o_u(0xffffffff,0));
	$t=$hi->multiply($lo);

	$w3=$t->__and(o_u(0xffffffff,0));
	$k=$t->shiftLeft(32);

	$hi=$hi->shiftRightUnsigned(32);

		
	$t=$hi->multiply($v1)->plus($k);

		
	$k=$t->__and(o_u(0xffffffff,0));
	$v1=$t->shiftLeft(32);
		
	$lo=$lo->shiftLeft(32);
	$t=$u1->multiply($lo)->plus($k);
	$k=$t->shiftLeft(32);
		
	$hi=$hi->multiply($lo)->plus($v1)->plus($k);
	$lo=$t->shiftRightUnsigned(32)->plus($w3);
	

	$lo=$lo->plus(b2int64(array_slice($c,8,8)));
	$hi=$hi->plus(b2int64(array_slice($c,0,8)));
/*		$tmp=array_fill(0,8,0);

	bufferEncode64($tmp,0,$lo);

		print_r($tmp);*/
	bufferEncode64($c,0,b2int64(array_slice($dst,0,8))->__xor($hi));
	bufferEncode64($c,8,b2int64(array_slice($dst,8,8))->__xor($lo));
	
	bufferEncode64($dst,0,$hi);
	bufferEncode64($dst,8,$lo);
	
	
}
function cryptonight($in){
	$len=32;
		
	$data=keccak_($in);
	//$cache= array_fill(0, 2097152, 0);

	$cache=array();
	$first32=array_slice($data,0,32);
	$aes=new oaes_ctx();
	oaes_key_import_data($aes,$first32,$len);
	$blocks=[];
	$mem=[];//2MB
	
	$parts=[];
	

	for($i=0;$i<8;$i++)
		$blocks[$i]=array_slice($data,64+$i*16,16);
	
	// $$block=$first32;
	//$text=[];//8
$longstate=array_fill(0,2097152,0);

	for($i=0;$i<2097152/16;$i+=128){//2MB
	
	for($j=0;$j<10;$j++){
		$ptr=array_slice($aes->key->exp_data,$j<<4,16);//$j<<4=$j*16
	
		SubAndShiftAndMixAddRoundInPlace($blocks[0],$ptr);
		SubAndShiftAndMixAddRoundInPlace($blocks[1],$ptr);
		SubAndShiftAndMixAddRoundInPlace($blocks[2],$ptr);
		SubAndShiftAndMixAddRoundInPlace($blocks[3],$ptr);
		SubAndShiftAndMixAddRoundInPlace($blocks[4],$ptr);
		SubAndShiftAndMixAddRoundInPlace($blocks[5],$ptr);
		SubAndShiftAndMixAddRoundInPlace($blocks[6],$ptr);
		SubAndShiftAndMixAddRoundInPlace($blocks[7],$ptr);
		
	//$time=microtime(TRUE);
	$combine=array_merge($blocks[0],$blocks[1],$blocks[2],$blocks[3],$blocks[4],$blocks[5],$blocks[6],$blocks[7]);
	_memcpy($longstate,$i,$combine,0,128);


	//$tm=(microtime(TRUE)-$time);
	//	echo "$tm\n";	
	}

	
	//print_r (" $i \r\n");
	}

		/*
	
$ab=xor_(array_slice($data,0,32),array_slice($data,32,32));
$a=array_slice($ab,0,16);$b=array_slice($ab,16,16);

for($i=0;$i<524288/2;++$i){
	$j=e2i($a);
	$out=array();
	aes_round_out($longstate,$j,$a,$out);
	$ret=xor_($out,$b);
	_memcpy($longstate,$j,$ret,0,16);
	
	
	
}*/
	//$cache=array_merge($cache,$$block);
	
	
	
	//print_r (count($$block));
	
	//oaes_key_import_data($aes,$data,$len);
	
	
	
}
//print_r(groestl256([11,23,53]));
//print_r(skein(array()));
//print_r(jh( array(0x2F, 0x7C)));
//print_r(keccak_( array(0xCC)));
//print_r(blake256( array(0xCC)));
 cryptonight(array(0xCC));


//$a=[1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16];
//$c=[51,46,3,4,5,6,7,8,9,10,11,55,13,14,15,16];
//$dst=array_fill(0,16,0);
//SubAndShiftAndMixAddRoundInPlace($a,[4,5,6,7,8,9,10,11,1,2,3,4,5,6,7,8]);

//mul_sum_xor_dst($a,$c,$dst);

//print_r($a);

?>