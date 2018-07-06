<?php

/**
 * 
 * @param type $p0 uint8_t
 * @param type $p1 uint8_t
 * @param type $p2 uint8_t
 * @param type $p3 uint8_t
 * @return type uint32_t
 */
/* inplace */

function __U8TO32($p0, $p1, $p2, $p3) {

    return (((($p0 & 0xff) << 24) | (($p1 & 0xff) << 16)) & 0xffffffff|
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
function __U32TO8(&$p0, &$p1, &$p2, &$p3, $v) {
    $v &= 0xffffffff;
    $p0 = ($v >> 24) & 0xff;
    $p1 = ($v >> 16) & 0xff;
    $p2 = ($v >> 8) & 0xff;
    $p3 = $v & 0xff;
}

/* inplace */

$sigma = array(
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

$cst = array(
    0x243F6A88, 0x85A308D3, 0x13198A2E, 0x03707344,
    0xA4093822, 0x299F31D0, 0x082EFA98, 0xEC4E6C89,
    0x452821E6, 0x38D01377, 0xBE5466CF, 0x34E90C6C,
    0xC0AC29B7, 0xC97C50DD, 0x3F84D5B5, 0xB5470917
);

$padding = array(
    0x80, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
    0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0
);

final class state_ {

    /**
     *
     * @var uint32_t 
     */
    public $h = array();

    /**
     *
     * @var uint32_t 
     */
    public $s = array();

    /**
     *
     * @var uint32_t 
     */
    public $t = array();

    /**
     *
     * @var int
     */
    public $buflen;

    /**
     *
     * @var int
     */
    public $nullt;

    /**
     *
     * @var uint8_t
     */
    public $buf;

    public function __construct() {
        $this->h = array_fill(0, 8, 0);
        $this->s = array_fill(0, 4, 0);
        $this->t = array_fill(0, 2, 0);

        $this->buf = array_fill(0, 64, 0);
    }

}

/**
 * 
 * @param type $x uint32_t
 * @param type $n uint8_t
 * @return type uint32_t
 */
#function RO T($x, $n) {
#    $x &= 0xffffffff;
#    return ($x << (32 - $n)) | ($x >> $n) & 0xffffffff;
#}
/* inplace */
function _64to32($x){//强制去除后四字节
$a=(($x >>0)& 0xff)<<0;
	$b=(($x >>8)& 0xff)<<8;
	$c=(($x>>16)& 0xff)<<16;
	$d=(($x >>24)& 0xff)<<24;
return $a|$b|$c|$d;
}

function ROT($x, $n) {
$x=_64to32($x);
    return (trans2(($x) << (32 - $n)) | trans2(($x >> $n)& 0xffffffff ));
}


/* inplace */

/* inplace */

function trans($nb){
	$n=$nb& 0xffffffff;
	   if($n>0x7fffffff){
             $n--; $n=~$n; $n&=0x7fffffff; $n=-$n;
         }
		 return $n;
}

function trans2($num) {
   $num = unpack('l', pack('l', $num));
   return $num[1] ;
}
function G($a, $b, $_c, $d, $e, array &$v, array $m, $i) {
    global /* $v, $m, $i, */$sigma, $cst;

    $v[$a] =trans($v[$a]+ trans(($m[$sigma[$i][$e]] ^ $cst[$sigma[$i][$e + 1]]) )  +($v[$b]));

		
    $v[$d] =trans(ROT(($v[$d]) ^ ($v[$a]), 16)  );
	
    $v[$_c] =trans ($v[$_c] +$v[$d] ) ;

	
//print_r(trans2(($v[$b]) ^ ($v[$_c])));
	
    $v[$b] = trans(ROT(($v[$b]) ^ ($v[$_c]), 12) )  ;
	
    $v[$a] =trans($v[$a]+trans( (($m[$sigma[$i][$e + 1]] ^ $cst[$sigma[$i][$e]]) )   + ($v[$b])));

    $v[$d] =trans(ROT($v[$d] ^ $v[$a], 8) );
		
    $v[$_c] = trans($v[$_c]+($v[$d] ));

    $v[$b] = trans(ROT($v[$b] ^ $v[$_c], 7) ) ;
	
}

/* inplace */

/**
 * 
 * @param state_ $S state_ *S
 * @param array $block const uint8_t *block
 */
/* inplace */function blake256_compress(  $S,   $block) {

    #global $sigma, $cst;
    //uint32_t v[16], m[16], i;
    $v = array_fill(0, 16, 0);
    $m = array_fill(0, 16, 0);
    #$i = 0;

    for ($i = 0, $p = 0; $i < 16 && $p < 64;)
        $m[$i++] = __U8TO32($block[$p++], $block[$p++]
                , $block[$p++], $block[$p++]);

    for ($i = 0; $i < 8; ++$i) {
        $v[$i] = $S->h[$i] ;
    }/* END unroll */

    $v[8] = ($S->s[0] ^ 0x243F6A88)  ;
    $v[9] = $S->s[1] ^ 0x85A308D3;
    $v[10] = $S->s[2] ^ 0x13198A2E;
    $v[11] = $S->s[3] ^ 0x03707344;
    $v[12] = 0xA4093822;
    $v[13] = 0x299F31D0;
    $v[14] = 0x082EFA98;
    $v[15] = 0xEC4E6C89;

    if ($S->nullt == 0) {
        $v[12] ^= $S->t[0];
        $v[13] ^= $S->t[0];
        $v[14] ^= $S->t[1];
        $v[15] ^= $S->t[1];
    }
	
for ($i = 0; $i < 16; ++$i) {
        $v[$i] = trans($v[$i] );
    }
		
	
    /* START unroll */for ($i = 0; $i < 14; ++$i) {
		
        G(0, 4, 8, 12, 0, $v, $m, $i);
	
        G(1, 5, 9, 13, 2, $v, $m, $i);
	
        G(2, 6, 10, 14, 4, $v, $m, $i);
			
        G(3, 7, 11, 15, 6, $v, $m, $i);
        G(3, 4, 9, 14, 14, $v, $m, $i);
        G(2, 7, 8, 13, 12, $v, $m, $i);
        G(0, 5, 10, 15, 8, $v, $m, $i);
        G(1, 6, 11, 12, 10, $v, $m, $i);
	
    }/* END unroll */
	
		
		
//print_r( $v);

    /* START unroll */ for ($i = 0; $i < 16; ++$i) {
        $S->h[$i % 8] ^= $v[$i] & 0xffffffff;
    }/* END unroll */



    /* START unroll */ for ($i = 0; $i < 8; ++$i) {
        $S->h[$i] ^= $S->s[$i % 4] ;
    }/* END unroll */


}/* inplace */

/**
 * 
 * @param state_ $S state_ *S
 */
function blake256_init(state_ $S) {
    $S->h[0] = 0x6A09E667;
    $S->h[1] = 0xBB67AE85;
    $S->h[2] = 0x3C6EF372;
    $S->h[3] = 0xA54FF53A;
    $S->h[4] = 0x510E527F;
    $S->h[5] = 0x9B05688C;
    $S->h[6] = 0x1F83D9AB;
    $S->h[7] = 0x5BE0CD19;
    $S->t[0] = $S->t[1] = $S->buflen = $S->nullt = 0;
    $S->s[0] = $S->s[1] = $S->s[2] = $S->s[3] = 0;
}

/**
 * 
 * @param state_ $S state_ *S
 * @param array $data const uint8_t *data
 * @param uint64 $datalen uint64_t datalen = number of bits
 */
function blake256_update(state_ $S, array $data, $datalen) {

    /* int */$left = $S->buflen >> 3;
    /* int */ $fill = 64 - $left;

    if ($left && ((($datalen >> 3) & 0x3F) >= /* (unsigned) */$fill)) {
		
        #memcpy((void *) (S->buf + left), (void *) data, fill);
        for ($x = 0; $x < $fill; ++$x)
            $S->buf[$x + $left] = $data[$x];

        $S->t[0] += 512;
		
        if ($S->t[0] == 0)
            $S->t[1] ++;
	
        blake256_compress($S, $S->buf);
		
        #$data += $fill;
        $data = array_slice($data, $fill);
		
        $datalen -= ($fill << 3);
	
        $left = 0;
    }

    while ($datalen >= 512) {
        $S->t[0] += 512;

        if ($S->t[0] === 0)
            ++$S->t[1];

        blake256_compress($S, $data);
        #$data += 64;
        $data = array_slice($data, 64);
        $datalen -= 512;
    }

    if ($datalen > 0) {
        #memcpy((void *) (S->buf + left), (void *) data, datalen >> 3);
        for ($x = 0; $x < ($datalen >> 3); ++$x)
            $S->buf[$x + $left] = $data[$x];

        $S->buflen = ($left << 3) + ((int) $datalen);
    } else
        $S->buflen = 0;
}

/**
 * 
 * @global array $padding
 * @param state_ $S state_ *S
 * @param array $digest uint8_t *digest
 * @param type $pa uint8_t
 * @param type $pb uint8_t
 */
function blake256_final_h(state_ $S, array &$digest, $pa, $pb) {

    global $padding;

    #uint8_t msglen[8];
    $msglen = array_fill(0, 8, 0);
    #uint32_t lo = S->t[0] + S->buflen, hi = S->t[1];
    $lo = ($S->t[0] + $S->buflen) & 0xffffffff;
    $hi = $S->t[1] & 0xffffffff;
    if ($lo < /* (unsigned) */$S->buflen)
        ++$hi;
	
	
    __U32TO8($msglen[0], $msglen[1], $msglen[2], $msglen[3], $hi);
    __U32TO8($msglen[4], $msglen[5], $msglen[6], $msglen[7], $lo);

	
    if ($S->buflen == 440) { /* one padding byte */
        $S->t[0] -= 8;
        blake256_update($S, array($pa), 8);
    } else {

        if ($S->buflen < 440) { /* enough space to fill the block  */

            $S->nullt = $S->buflen === 0 ? 1 : $S->nullt;
            $S->t[0] -= 440 - $S->buflen;
			
            blake256_update($S, $padding, 440 - $S->buflen);
			//print_r($S->nullt);
			
			
        } else { /* need 2 compressions */
            $S->t[0] -= 512 - $S->buflen;
            blake256_update($S, $padding, 512 - $S->buflen);
            $S->t[0] -= 440;
            #blake256_update($S, $padding + 1, 440);
            blake256_update($S, array_slice($padding, 1), 440);
            $S->nullt = 1;
        }
        blake256_update($S, array($pb), 8);
        $S->t[0] -= 8;
    }
    $S->t[0] -= 64;

    blake256_update($S, $msglen, 64);

    #$digest = array_fill(0, 32, 0);

    __U32TO8($digest[0], $digest[1], $digest[2], $digest[3], $S->h[0]);
    __U32TO8($digest[4], $digest[5], $digest[6], $digest[7], $S->h[1]);
    __U32TO8($digest[8], $digest[9], $digest[10], $digest[11], $S->h[2]);
    __U32TO8($digest[12], $digest[13], $digest[14], $digest[15], $S->h[3]);
    __U32TO8($digest[16], $digest[17], $digest[18], $digest[19], $S->h[4]);
    __U32TO8($digest[20], $digest[21], $digest[22], $digest[23], $S->h[5]);
    __U32TO8($digest[24], $digest[25], $digest[26], $digest[27], $S->h[6]);
    __U32TO8($digest[28], $digest[29], $digest[30], $digest[31], $S->h[7]);
}

/**
 * 
 * @param state_ $S state_ *S
 * @param array $digest uint8_t *digest
 */
function blake256_final(state_ $S, /* uint8_t * */ array &$digest) {
    blake256_final_h($S, $digest, 0x81, 0x01);
}

/**
 * 
 * @param array $out uint8_t *out
 * @param array $in const uint8_t *in
 * @param type $inlen uint64_t inlen = number of bytes
 */
function blake256_hash(/* uint8_t * */array &$out, /* const uint8_t * */ array $in, /* uint64_t */ $inlen) {
    $S = new state_;
    blake256_init($S);
    blake256_update($S, $in, $inlen * 8);
    blake256_final($S, $out);
}

/* 1208051793,989430589,2588628151,
 * 281098870,1399103462,2315590707,2872526399,260670745,
  0,0,0,0,
  7680,0,


  $out = array();
  $in = array_fill(0, 72, 0);
  $test1 = array(
  0x0c, 0xe8, 0xd4, 0xef, 0x4d, 0xd7, 0xcd, 0x8d,
  0x62, 0xdf, 0xde, 0xd9, 0xd4, 0xed, 0xb0, 0xa7,
  0x74, 0xae, 0x6a, 0x41, 0x92, 0x9a, 0x74, 0xda,
  0x23, 0x10, 0x9e, 0x8f, 0x11, 0x13, 0x9c, 0x87
  );
  $test2 =
  array(
  0xd4, 0x19, 0xba, 0xd3, 0x2d, 0x50, 0x4f, 0xb7,
  0xd4, 0x4d, 0x46, 0x0c, 0x42, 0xc5, 0x59, 0x3f,
  0xe5, 0x44, 0xfa, 0x4c, 0x13, 0x5d, 0xec, 0x31,
  0xe2, 0x1b, 0xd9, 0xab, 0xdc, 0xc2, 0x2d, 0x41
  );
  blake256_hash($out, $in, 72);
  #var_dump($out);
  #die;
  for ($i = 0; $i < 32; ++$i) {
  printf("%d\t%d\n", $out[$i], $test2[$i]);
  }
 */
#var_dump($hash);

/*
$vects = array(
    array(
        '8f69d890786569cc878e9995a0ebf5e319746482ab56b8184fec5267190e6ade',
        'abcdefghbcdefghicdefghijdefghijkefghijklfghijklmghijklmnhijklmnoijklmnopjklmnopqklmnopqrlmnopqrsmnopqrstnopqrstu'
    ),
    array("4181475cb0c22d58ae847e368e91b4669ea2d84bcd55dbf01fe24bae6571dd08",
        'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec a diam lectus. Sed sit amet ipsum mauris. Maecenas congue ligula ac quam viverra nec consectetur ante hendrerit. Donec et mollis dolor. Praesent et diam eget libero egestas mattis sit amet vitae augue. Nam tincidunt congue enim, ut porta lorem lacinia consectetur. Donec ut libero sed arcu vehicula ultricies a non tortor. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean ut gravida lorem. Ut turpis felis, pulvinar a semper sed, adipiscing id dolor. Pellentesque auctor nisi id magna consequat sagittis. Curabitur dapibus enim sit amet elit pharetra tincidunt feugiat nisl imperdiet. Ut convallis libero in urna ultrices accumsan. Donec sed odio eros. Donec viverra mi quis quam pulvinar at malesuada arcu rhoncus. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. In rutrum accumsan ultricies. Mauris vitae nisi at sem facilisis semper ac in est.',
    ),
    array("7576698ee9cad30173080678e5965916adbb11cb5245d386bf1ffda1cb26c9d7",
        "The quick brown fox jumps over the lazy dog"),
    array("07663e00cf96fbc136cf7b1ee099c95346ba3920893d18cc8851f22ee2e36aa6",
        "BLAKE"),
    array("716f6e863f744b9ac22c97ec7b76ea5f5908bc5b2f67c61510bfc4751384ea7a",
        ""),
    array("18a393b4e62b1887a2edf79a5c5a5464daf5bbb976f4007bea16a73e4c1e198e",
        "'BLAKE wins SHA-3! Hooray!!!' (I have time machine)"),
    array("fd7282ecc105ef201bb94663fc413db1b7696414682090015f17e309b835f1c2",
        "Go"),
    array("1e75db2a709081f853c2229b65fd1558540aa5e7bd17b04b9a4b31989effa711",
        "HELP! I'm trapped in hash!"),
    array("af95fffc7768821b1e08866a2f9f66916762bfc9d71c4acb5fd515f31fd6785a", // test with one padding byte
        "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec a diam lectus. Sed sit amet ipsum mauris. Maecenas congu",
    ),
);*/

#var_dump(bin2hex(implode('', array_map('chr', array(65,129,71,92,176,194,45,88,174,132,126,54,142,145,180,102,158,162,216,75,205,85,219,240,31,226,75,174,101,113,221,8)))));die;
#4181475cb0c22d58ae847e368e91b4669ea2d84bcd55dbf01fe24bae6571dd08
# Blyead
/* 7 should be.... 65,129,71,92,176,194,45,88,174,132,126,54,142,145,180,102,
 * 158,162,216,75,205,85,219,240,31,226,75,174,101,113,221,8

 * 
 * 
 * string(114) "148,222,123,45,188,9,220,147,226,109,50,6,151,22,
 * 217,31,214,67,134,72,239,104,80,168,178,194,46,243,56,212,113,248"

 * 
 *  */
/*
foreach ($vects as $i => $v) {
    list($hash_x, $data) = $v;

    $o = array();
    blake256_hash($o, array_map('ord', str_split($data)), strlen($data));
    $h = bin2hex(implode('', array_map('chr', $o)));

    if ($h === $hash_x) {
        printf("TEST OK\n");
    } else {
        printf("$i test failed!!!\n");
        var_dump(implode(',', $o));
    }
    #die;
}*/