<?php

class state {

    public $hashbitlen; // max 0xffffffff
    public $databitlen; // max 0xffffffff
    public $datasize_in_buffer;
    public $H; //128
    public $A; //256
    public $roundconstant; //64
    public $buffer; //64

}

/* The constant for the Round 0 of E8 */
$roundconstant_zero = [0x6, 0xa, 0x0, 0x9, 0xe, 0x6, 0x6, 0x7, 0xf, 0x3, 0xb, 0xc, 0xc, 0x9, 0x0, 0x8, 0xb, 0x2, 0xf,
    0xb, 0x1, 0x3, 0x6, 0x6, 0xe, 0xa, 0x9, 0x5, 0x7, 0xd, 0x3, 0xe, 0x3, 0xa, 0xd, 0xe, 0xc, 0x1, 0x7, 0x5, 0x1, 0x2, 0x7,
    0x7, 0x5, 0x0, 0x9, 0x9, 0xd, 0xa, 0x2, 0xf, 0x5, 0x9, 0x0, 0xb, 0x0, 0x6, 0x6, 0x7, 0x3, 0x2, 0x2, 0xa
];

/* The two Sboxes S0 and S1 */
$S = [
    [9, 0, 4, 11, 13, 12, 3, 15, 1, 10, 2, 6, 7, 5, 8, 14],
    [3, 12, 6, 13, 5, 7, 1, 9, 15, 2, 0, 4, 11, 10, 14, 8]
];


/* the round function of E8 */

function R8(state $state) {
    global $S;
    $tem = Array_fill(0, 256, 0);
    $t = 0;
    $roundconstant_expanded = Array_fill(0, 256, 0);

    /* expand the round constant into 256 one-bit element */
    for ($i = 0; $i < count($roundconstant_expanded); $i++)
        $roundconstant_expanded[$i] = ($state->roundconstant[$i >> 2] >> (3 - ($i & 3))) & 1;


    /* S box layer, each constant bit selects one Sbox from S0 and S1 */
    for ($i = 0; $i < count($tem); $i++)
        $tem[$i] = $S[$roundconstant_expanded[$i]][$state->A[$i]]; /* constant bits are used to determine which Sbox to use */


    /* MDS Layer */
    for ($i = 0; $i < 256; $i = $i + 2) {
        // L(tem[i], tem[i+1])
        $tem[$i + 1] ^= (((($tem[$i]) << 1) >> 0) ^ (($tem[$i]) >> 3) ^ ((($tem[$i]) >> 2) & 2)) & 0xf;
        $tem[$i] ^= (((($tem[$i + 1]) << 1) >> 0) ^ (($tem[$i + 1]) >> 3) ^ ((($tem[$i + 1]) >> 2) & 2)) & 0xf;
    }

    /* The following is the permuation layer P_8$

      /*initial swap Pi_8 */
    for ($i = 0; $i < 256; $i = $i + 4) {
        $t = $tem[$i + 2];
        $tem[$i + 2] = $tem[$i + 3];
        $tem[$i + 3] = $t;
    }

    /* permutation P'_8 */
    for ($i = 0; $i < 128; $i = $i + 1) {
        $state->A[$i] = $tem[$i << 1];
        $state->A[$i + 128] = $tem[($i << 1) + 1];
    }

    /* final swap Phi_8 */
    for ($i = 128; $i < 256; $i = $i + 2) {
        $t = $state->A[$i];
        $state->A[$i] = $state->A[$i + 1];
        $state->A[$i + 1] = $t;
    }
}

/* The following function generates the next round constant from the current
  round constant;  R6 is used for generating round constants for E8, with
  the round constants of R6 being set as 0;
 */

function update_roundconstant(state $state) {
    global $S;

    $i = 0;
    $tem = Array_fill(0, 64, 0);
    $t = 0;

    /* Sbox layer */
    for ($i = 0; $i < count($tem); $i++)
        $tem[$i] = $S[0][$state->roundconstant[$i]];


    /* MDS layer */
    for ($i = 0; $i < 64; $i = $i + 2) {
        //L(tem[i], tem[i+1])
        $tem[$i + 1] ^= (((($tem[$i]) << 1) >> 0) ^ (($tem[$i]) >> 3) ^ ((($tem[$i]) >> 2) & 2)) & 0xf;
        $tem[$i] ^= (((($tem[$i + 1]) << 1) >> 0) ^ (($tem[$i + 1]) >> 3) ^ ((($tem[$i + 1]) >> 2) & 2)) & 0xf;
    }

    /* The following is the permutation layer P_6 */

    /* initial swap Pi_6 */
    for ($i = 0; $i < 64; $i = $i + 4) {
        $t = $tem[$i + 2];
        $tem[$i + 2] = $tem[$i + 3];
        $tem[$i + 3] = $t;
    }

    /* permutation P'_6 */
    for ($i = 0; $i < 32; $i = $i + 1) {
        $state->roundconstant[$i] = $tem[$i << 1];
        $state->roundconstant[$i + 32] = $tem[($i << 1) + 1];
    }

    /* final swap Phi_6 */
    for ($i = 32; $i < 64; $i = $i + 2) {
        $t = $state->roundconstant[$i];
        $state->roundconstant[$i] = $state->roundconstant[$i + 1];
        $state->roundconstant[$i + 1] = $t;
    }
}

/* initial group at the begining of E_8: group the bits of H into 4-bit elements of A.
  After the grouping, the i-th, (i+256)-th, (i+512)-th, (i+768)-th bits of state.H
  become the i-th 4-bit element of state.A
 */

function E8_initialgroup(state $state) {
    $t0 = 0;
    $t1 = 0;
    $t2 = 0;
    $t3 = 0;
    $tem = Array_fill(0, 256, 0);

    /* t0 is the i-th bit of H, i = 0, 1, 2, 3, ... , 127 */
    /* t1 is the (i+256)-th bit of H */
    /* t2 is the (i+512)-th bit of H */
    /* t3 is the (i+768)-th bit of H */
    for ($i = 0; $i < count($tem); $i++) {
        $t0 = ($state->H[$i >> 3] >> (7 - ($i & 7))) & 1;
        $t1 = ($state->H[($i + 256) >> 3] >> (7 - ($i & 7))) & 1;
        $t2 = ($state->H[($i + 512) >> 3] >> (7 - ($i & 7))) & 1;
        $t3 = ($state->H[($i + 768) >> 3] >> (7 - ($i & 7))) & 1;
        $tem[$i] = (((($t0 << 3) >> 0) | (($t1 << 2) >> 0) | (($t2 << 1) >> 0) | ($t3 << 0)) & 0xFF) >> 0;
    }

    /* padding the odd-th elements and even-th elements separately */
    for ($i = 0; $i < 128; $i++) {
        $state->A[$i << 1] = $tem[$i];
        $state->A[($i << 1) + 1] = $tem[$i + 128];
    }
}

function E8_finaldegroup(state $state) {
    $t0 = 0;
    $t1 = 0;
    $t2 = 0;
    $t3 = 0;
    $tem = Array_fill(0, 256, 0);

    for ($i = 0; $i < 128; $i++) {
        $tem[$i] = $state->A[$i << 1];
        $tem[$i + 128] = $state->A[($i << 1) + 1];
    }

    for ($i = 0; $i < 128; $i++) {
        $state->H[$i] = 0;
    }

    for ($i = 0; $i < 256; $i++) {
        $t0 = ($tem[$i] >> 3) & 1;
        $t1 = ($tem[$i] >> 2) & 1;
        $t2 = ($tem[$i] >> 1) & 1;
        $t3 = ($tem[$i] >> 0) & 1;

        $state->H[$i >> 3] |= $t0 << (7 - ($i & 7));
        $state->H[($i + 256) >> 3] |= ($t1 << (7 - ($i & 7))) >> 0;
        $state->H[($i + 512) >> 3] |= ($t2 << (7 - ($i & 7))) >> 0;
        $state->H[($i + 768) >> 3] |= ($t3 << (7 - ($i & 7))) >> 0;
    }
}

/* bijective function E8 */

function E8(state $state) {
    global $roundconstant_zero;


    /* initialize the round constant */
    for ($i = 0; $i < 64; $i++)
        $state->roundconstant[$i] = $roundconstant_zero[$i];


    /* initial group at the begining of E_8: group the H value into 4-bit elements and store them in A */
    E8_initialgroup($state);

    /* 42 rounds */
    for ($i = 0; $i < 42; $i++) {
        R8($state);
        update_roundconstant($state);
    }

    /* de-group at the end of E_8:  decompose the 4-bit elements of A into the 1024-bit H */
    E8_finaldegroup($state);
}

function F8(state $state) {

    // console.log('@F8 - chk1') 
#        dump_H();

    /* xor the message with the first half of H */
    for ($i = 0; $i < 64; $i++) {
        $state->H[$i] ^= $state->buffer[$i];
    }

    /* Bijective function E8 */
    E8($state);

    /* xor the message with the last half of H */
    for ($i = 0; $i < 64; $i++) {
        $state->H[$i + 64] ^= $state->buffer[$i];
    }

    // console.log('@F8 - chk2') 
#        dump_H();
}

/* mock memcpy: copy 'len' elements 
  from array 'arr2' starting at index 'off2'
  to array 'arr1' starting at index 'off1'
 */

function _memcpy(array &$arr1, $off1, $arr2, $off2, $len) {
    for ($i = 0; $i < $len; $i++) {
        $arr1[$off1 + $i] = $arr2[$off2 + $i];
    }
}

function __Init(state $state, $hashbitlen) {
    $state->databitlen = 0;
    $state->datasize_in_buffer = 0;
    $state->hashbitlen = $hashbitlen;
    for ($i = 0; $i < count($state->buffer); $i++) {
        $state->buffer[$i] = 0;
    }
    for ($i = 0; $i < count($state->H); $i++) {
        $state->H[$i] = 0;
    }
    $state->H[1] = $hashbitlen & 0xff;
    $state->H[0] = ($hashbitlen >> 8) & 0xff;
    F8($state);
}

/* hash each 512-bit message block, except the last partial block */

function __Update(state $state, $data, $databitlen) {
    //$index = 0; /*the starting address of the data to be compressed*/

    $state->databitlen += $databitlen;
    $index = 0;

    /* if there is remaining data in the buffer, fill it to a full message block first */
    /* we assume that the size of the data in the buffer is the multiple of 8 bits if it is not at the end of a message */


    /* There is data in the buffer, but the incoming data is insufficient for a full block */
    if (($state->datasize_in_buffer > 0) && (($state->datasize_in_buffer + $databitlen) < 512)) {
        if (($databitlen & 7) == 0) {
            _memcpy($state->buffer, $state->datasize_in_buffer >> 3, $data, 0, 64 - ($state->datasize_in_buffer >> 3));
        } else {
            _memcpy($state->buffer, $state->datasize_in_buffer >> 3, $data, 0, 64 - ($state->datasize_in_buffer >> 3) + 1);
        }
        $state->datasize_in_buffer += $databitlen;
        $databitlen = 0;
    }

    /* There is data in the buffer, and the incoming data is sufficient for a full block */
    if (($state->datasize_in_buffer > 0) && (($state->datasize_in_buffer + $databitlen) >= 512)) {
        _memcpy($state->buffer, $state->datasize_in_buffer >> 3, $data, 0, 64 - ($state->datasize_in_buffer >> 3));
        $index = 64 - ($state->datasize_in_buffer >> 3);
        $databitlen = $databitlen - (512 - $state->datasize_in_buffer);
        F8($state);
        $state->datasize_in_buffer = 0;
    }


    /* hash the remaining full message blocks */
    for (; $databitlen >= 512; ($index = $index + 64), ($databitlen = $databitlen - 512)) {
        _memcpy($state->buffer, 0, $data, $index, 64);
        F8($state);
    }


    /* store the partial block into buffer, assume that -- if part of the last byte is not part of the message, then that part consists of 0 bits */
    if ($databitlen > 0) {
        if (($databitlen & 7) == 0) {
            _memcpy($state->buffer, 0, $data, $index, ($databitlen & 0x1ff) >> 3);
        } else {
            _memcpy($state->buffer, 0, $data, $index, (($databitlen & 0x1ff) >> 3) + 1);
        }
        $state->datasize_in_buffer = $databitlen;
    }
}

/* function dump_H() {
  var retval = '';
  for (var i = 0; i < hash_state.H.length; i++) {
  var t = (hash_state.H[i]).toString(16);
  if (t.length < 2) {
  t = "0" + t;
  }
  retval = retval + t + " ";
  }
  // console.log("H = " + retval)
  } */

/* padding the message, truncate the hash value H and obtain the message digest */

function __Final(state $state, array &$hashval) {

    if (($state->databitlen & 0x1ff) == 0) {
        /* pad the message when databitlen is multiple of 512 bits, then process the padded block */
        for ($i = 0; $i < 64; $i++) {
            $state->buffer[$i] = 0;
        }

        $state->buffer[0] = 0x80;
        $state->buffer[63] = $state->databitlen & 0xff;
        $state->buffer[62] = ($state->databitlen >> 8) & 0xff;
        $state->buffer[61] = ($state->databitlen >> 16) & 0xff;
        $state->buffer[60] = ($state->databitlen >> 24) & 0xff;
        F8($state);
    } else {
        /* set the rest of the bytes in the buffer to 0 */
        if (($state->datasize_in_buffer & 7) == 0) {
            for ($i = ($state->databitlen & 0x1ff) >> 3; $i < 64; $i++) {
                $state->buffer[$i] = 0;
            }
        } else {
            for ($i = (($state->databitlen & 0x1ff) >> 3) + 1; $i < 64; $i++) {
                $state->buffer[$i] = 0;
            }
        }

        /* pad and process the partial block when databitlen is not multiple of 512 bits, then hash the padded blocks */
        $state->buffer[(($state->databitlen & 0x1ff) >> 3)] |= 1 << (7 - ($state->databitlen & 7));
        F8($state);
        for ($i = 0; $i < 64; $i++) {
            $state->buffer[$i] = 0;
        }
        $state->buffer[63] = $state->databitlen & 0xff;
        $state->buffer[62] = ($state->databitlen >> 8) & 0xff;
        $state->buffer[61] = ($state->databitlen >> 16) & 0xff;
        $state->buffer[60] = ($state->databitlen >> 24) & 0xff;
        F8($state);
    }

    /* trunacting the final hash value to generate the message digest */
    switch ($state->hashbitlen) {
        case 224:
            _memcpy($hashval, 0, $state->H, 100, 28);
            break;
        case 256:
            _memcpy($hashval, 0, $state->H, 96, 32);
            break;
        case 384:
            _memcpy($hashval, 0, $state->H, 80, 48);
            break;
        case 512:
            _memcpy($hashval, 0, $state->H, 64, 64);
            break;
    }

    // return(SUCCESS);
}

function jh(array $data, $hashbitlen, $databitlen) {

    $hash_state = new state();
    $hash_state->hashbitlen = 0; // max 0xffffffff
    $hash_state->databitlen = 0; // max 0xffffffff
    $hash_state->datasize_in_buffer = 0;
    $hash_state->H = Array_fill(0, 128, 0);
    $hash_state->A = Array_fill(0, 256, 0);
    $hash_state->roundconstant = Array_fill(0, 64, 0);
    $hash_state->buffer = Array_fill(0, 64, 0);

    $hashval = Array_fill(0, 128, 0);
    for ($i = 0; $i < 128; $i++)
        $hashval[$i] = 0;

    if ($hashbitlen == 224 || $hashbitlen == 256 || $hashbitlen == 384 || $hashbitlen == 512) {
        __Init($hash_state, $hashbitlen);
        __Update($hash_state, $data, $databitlen);
        __Final($hash_state, $hashval);

        return $hashval;
    } else {
        return NULL;
    }
}

$in = array(0x2F, 0x7C);
$out = jh($in, 256, 2 * 8);
var_dump(bin2hex(implode('', array_map('chr', $out))));
#31a1c50b522fb75a7f1cad9f4b6abf6b8b4b6fc2f56a8fc0405854429bdfadc0