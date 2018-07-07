<?php

require_once 'shit/u64.php';

define('HASH_DATA_AREA', 136);
define('KECCAK_ROUNDS', 24);

function ROTL64(o_u64 $x, $y) {
    return $x->rotateLeft($y);
    #var_dump($x, $y);die;
    #return ((($x) << ($y)) | (($x) >> (64 - ($y))));
}

$keccakf_rndc = array(
    new o_u64(0x00000000, 0x00000001), new o_u64(0x00000000, 0x00008082), new o_u64(0x80000000, 0x0000808a),
    new o_u64(0x80000000, 0x80008000), new o_u64(0x00000000, 0x0000808b), new o_u64(0x00000000, 0x80000001),
    new o_u64(0x80000000, 0x80008081), new o_u64(0x80000000, 0x00008009), new o_u64(0x00000000, 0x0000008a),
    new o_u64(0x00000000, 0x00000088), new o_u64(0x00000000, 0x80008009), new o_u64(0x00000000, 0x8000000a),
    new o_u64(0x00000000, 0x8000808b), new o_u64(0x80000000, 0x0000008b), new o_u64(0x80000000, 0x00008089),
    new o_u64(0x80000000, 0x00008003), new o_u64(0x80000000, 0x00008002), new o_u64(0x80000000, 0x00000080),
    new o_u64(0x00000000, 0x0000800a), new o_u64(0x80000000, 0x8000000a), new o_u64(0x80000000, 0x80008081),
    new o_u64(0x80000000, 0x00008080), new o_u64(0x00000000, 0x80000001), new o_u64(0x80000000, 0x80008008)
);


$keccakf_rotc = array(
    1, 3, 6, 10, 15, 21, 28, 36, 45, 55, 2, 14,
    27, 41, 56, 8, 25, 43, 62, 18, 39, 61, 20, 44
);

$keccakf_piln = array(
    10, 7, 11, 17, 18, 3, 5, 16, 8, 21, 24, 4,
    15, 23, 19, 13, 12, 2, 20, 14, 22, 9, 6, 1
);

// update the state with given number of rounds

/**
 * 
 * @param o_u64[] $st
 * @param int $rounds
 */
function keccakf(/* uint64_t[25] */string &$st, int $rounds) {
    global $keccakf_piln, $keccakf_rotc, $keccakf_rndc;
    /* int */ $i = 0;
    $j = 0;
    $round = 0;
    #$t = new o_u64(0x0, 0x0);
    #$bc = array_fill(0, 5, NULL);
    #for ($bc_i = 0; $bc_i < 5; ++$bc_i)
    #    $bc[$bc_i] = new o_u64(0x0, 0x0);
    $bc = str_repeat(chr(0), 5 * 8);

    for ($round = 0; $round < $rounds; ++$round) {

        // Theta
        $th = '';

        for ($i = 0; $i < 5; ++$i) {

            $st0 = substr($st, $i, 8);
            $st5 = substr($st, $i + 5, 8);
            $st10 = substr($st, $i + 10, 8);
            $st15 = substr($st, $i + 15, 8);
            $st20 = substr($st, $i + 20, 8);

            $th .= ($st0 ^ $st5 ^ $st10 ^ $st15 ^ $st20);
            #$bc[$i]->setxor64($st[$i]
            #        , $st[$i + 5], $st[$i + 10], $st[$i + 15], $st[$i + 20]);
        }
        $bc = $bc ^ $th;

        for ($i = 0; $i < 5; ++$i) {
            $bc0 = substr($bc, ($i + 4) % 5, 8);
            $bc1 = substr($bc, ($i + 1) % 5, 8);

            $t = $bc0 ^ $bc1; //$bc[($i + 4) % 5]->__xor(ROTL64($bc[($i + 1) % 5], 1));
            $st[$i]->setxorOne($t);
            $st[$i + 5]->setxorOne($t);
            $st[$i + 10]->setxorOne($t);
            $st[$i + 15]->setxorOne($t);
            $st[$i + 20]->setxorOne($t);
        }

        // Rho Pi
        $t = clone $st[1];
        for ($i = 0; $i < 24; ++$i) {
            $j = (int) $keccakf_piln[$i];
            $bc[0] = clone $st[$j];
            $st[$j] = ROTL64($t, $keccakf_rotc[$i]);
            $t = clone $bc[0];
        }

        //  Chi
        for ($j = 0; $j < 25; $j += 5) {
            for ($i = 0; $i < 5; ++$i)
                $bc[$i] = clone $st[$j + $i];

            for ($i = 0; $i < 5; ++$i)
                $st[$j + $i]->setxorOne($bc[($i + 1) % 5]->not()->__and($bc[($i + 2) % 5]));
        }

        //  Iota
        $st[0]->setxorOne($keccakf_rndc[$round]);
    }
}

// compute a keccak hash (md) of given byte length from "in"
#typedef uint64_t state_t[25];
#function keccak(const uint8_t *in, int inlen, uint8_t *md, int mdlen) {
function keccak(string $in, int $inlen, string &$md, int $mdlen) {
    $st = str_repeat(chr(0), 25 * 8);
    $temp = str_repeat(chr(0), 144);

    $rsiz = 200 === $mdlen ? HASH_DATA_AREA : (200 - 2 * $mdlen);
    $rsizw = $rsiz / 8;

    for (; $inlen >= $rsiz
    ; $inlen -= $rsiz, $in = substr($in, $rsiz)) {


        $st ^= $in;

        #for ($i = 0; $i < $rsizw; ++$i)
        #    $st[$i]->setxorOne(___decodeLELong($in, $i * 8));

        keccakf($st, KECCAK_ROUNDS);
    }

    // last block and padding
    #_memcpy($temp, 0, $in, 0, $inlen);
    #$temp = substr($in, 0, $inlen);
    for ($i = 0; $i < $inlen; ++$i)
        $temp{$i} = $in{$i};

    $temp{$inlen++} = "\x01";
    #memset(temp + inlen, 0, rsiz - inlen);
    for ($g = 0; $g < $rsiz - $inlen; ++$g)
        $temp{$g + $inlen} = "\x00";
    $temp{$rsiz - 1} = $temp{$rsiz - 1} | 0x80;

    $st ^= $temp;

#    for ($i = 0; $i < $rsizw; ++$i)
    #       $st[$i]->setxorOne(___decodeLELong($temp, $i * 8));


    keccakf($st, KECCAK_ROUNDS);
    #_memcpy($md, 0, $st, 0, $mdlen);
    #for ($i = 0; $i < 25; ++$i)
    #    ___encodeLELong($st[$i], $md, $i * 8);
    $md = $st;
}

#function keccak1600(const uint8_t *in, int inlen, uint8_t *md) {
#function keccak1600(array $in, $inlen, array &$md) {
#    keccak($in, $inlen, $md, 200);
#}
#require_once 'c.php';
$in = "\xCC";
$md = "";
keccak($in, 1, $md, 200);
$h = bin2hex($md);
var_dump($h);
