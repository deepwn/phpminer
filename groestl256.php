<?php

#include <string.h>
#include "crypto_hash.h"
define('ROUNDS', 10);

$sbox = [
    0x63, 0x7C, 0x77, 0x7B, 0xF2, 0x6B, 0x6F, 0xC5, 0x30, 0x01, 0x67, 0x2B, 0xFE, 0xD7, 0xAB, 0x76,
    0xCA, 0x82, 0xC9, 0x7D, 0xFA, 0x59, 0x47, 0xF0, 0xAD, 0xD4, 0xA2, 0xAF, 0x9C, 0xA4, 0x72, 0xC0,
    0xB7, 0xFD, 0x93, 0x26, 0x36, 0x3F, 0xF7, 0xCC, 0x34, 0xA5, 0xE5, 0xF1, 0x71, 0xD8, 0x31, 0x15,
    0x04, 0xC7, 0x23, 0xC3, 0x18, 0x96, 0x05, 0x9A, 0x07, 0x12, 0x80, 0xE2, 0xEB, 0x27, 0xB2, 0x75,
    0x09, 0x83, 0x2C, 0x1A, 0x1B, 0x6E, 0x5A, 0xA0, 0x52, 0x3B, 0xD6, 0xB3, 0x29, 0xE3, 0x2F, 0x84,
    0x53, 0xD1, 0x00, 0xED, 0x20, 0xFC, 0xB1, 0x5B, 0x6A, 0xCB, 0xBE, 0x39, 0x4A, 0x4C, 0x58, 0xCF,
    0xD0, 0xEF, 0xAA, 0xFB, 0x43, 0x4D, 0x33, 0x85, 0x45, 0xF9, 0x02, 0x7F, 0x50, 0x3C, 0x9F, 0xA8,
    0x51, 0xA3, 0x40, 0x8F, 0x92, 0x9D, 0x38, 0xF5, 0xBC, 0xB6, 0xDA, 0x21, 0x10, 0xFF, 0xF3, 0xD2,
    0xCD, 0x0C, 0x13, 0xEC, 0x5F, 0x97, 0x44, 0x17, 0xC4, 0xA7, 0x7E, 0x3D, 0x64, 0x5D, 0x19, 0x73,
    0x60, 0x81, 0x4F, 0xDC, 0x22, 0x2A, 0x90, 0x88, 0x46, 0xEE, 0xB8, 0x14, 0xDE, 0x5E, 0x0B, 0xDB,
    0xE0, 0x32, 0x3A, 0x0A, 0x49, 0x06, 0x24, 0x5C, 0xC2, 0xD3, 0xAC, 0x62, 0x91, 0x95, 0xE4, 0x79,
    0xE7, 0xC8, 0x37, 0x6D, 0x8D, 0xD5, 0x4E, 0xA9, 0x6C, 0x56, 0xF4, 0xEA, 0x65, 0x7A, 0xAE, 0x08,
    0xBA, 0x78, 0x25, 0x2E, 0x1C, 0xA6, 0xB4, 0xC6, 0xE8, 0xDD, 0x74, 0x1F, 0x4B, 0xBD, 0x8B, 0x8A,
    0x70, 0x3E, 0xB5, 0x66, 0x48, 0x03, 0xF6, 0x0E, 0x61, 0x35, 0x57, 0xB9, 0x86, 0xC1, 0x1D, 0x9E,
    0xE1, 0xF8, 0x98, 0x11, 0x69, 0xD9, 0x8E, 0x94, 0x9B, 0x1E, 0x87, 0xE9, 0xCE, 0x55, 0x28, 0xDF,
    0x8C, 0xA1, 0x89, 0x0D, 0xBF, 0xE6, 0x42, 0x68, 0x41, 0x99, 0x2D, 0x0F, 0xB0, 0x54, 0xBB, 0x16
];

$mul2 = [
    0x00, 0x02, 0x04, 0x06, 0x08, 0x0A, 0x0C, 0x0E, 0x10, 0x12, 0x14, 0x16, 0x18, 0x1A, 0x1C, 0x1E,
    0x20, 0x22, 0x24, 0x26, 0x28, 0x2A, 0x2C, 0x2E, 0x30, 0x32, 0x34, 0x36, 0x38, 0x3A, 0x3C, 0x3E,
    0x40, 0x42, 0x44, 0x46, 0x48, 0x4A, 0x4C, 0x4E, 0x50, 0x52, 0x54, 0x56, 0x58, 0x5A, 0x5C, 0x5E,
    0x60, 0x62, 0x64, 0x66, 0x68, 0x6A, 0x6C, 0x6E, 0x70, 0x72, 0x74, 0x76, 0x78, 0x7A, 0x7C, 0x7E,
    0x80, 0x82, 0x84, 0x86, 0x88, 0x8A, 0x8C, 0x8E, 0x90, 0x92, 0x94, 0x96, 0x98, 0x9A, 0x9C, 0x9E,
    0xA0, 0xA2, 0xA4, 0xA6, 0xA8, 0xAA, 0xAC, 0xAE, 0xB0, 0xB2, 0xB4, 0xB6, 0xB8, 0xBA, 0xBC, 0xBE,
    0xC0, 0xC2, 0xC4, 0xC6, 0xC8, 0xCA, 0xCC, 0xCE, 0xD0, 0xD2, 0xD4, 0xD6, 0xD8, 0xDA, 0xDC, 0xDE,
    0xE0, 0xE2, 0xE4, 0xE6, 0xE8, 0xEA, 0xEC, 0xEE, 0xF0, 0xF2, 0xF4, 0xF6, 0xF8, 0xFA, 0xFC, 0xFE,
    0x1B, 0x19, 0x1F, 0x1D, 0x13, 0x11, 0x17, 0x15, 0x0B, 0x09, 0x0F, 0x0D, 0x03, 0x01, 0x07, 0x05,
    0x3B, 0x39, 0x3F, 0x3D, 0x33, 0x31, 0x37, 0x35, 0x2B, 0x29, 0x2F, 0x2D, 0x23, 0x21, 0x27, 0x25,
    0x5B, 0x59, 0x5F, 0x5D, 0x53, 0x51, 0x57, 0x55, 0x4B, 0x49, 0x4F, 0x4D, 0x43, 0x41, 0x47, 0x45,
    0x7B, 0x79, 0x7F, 0x7D, 0x73, 0x71, 0x77, 0x75, 0x6B, 0x69, 0x6F, 0x6D, 0x63, 0x61, 0x67, 0x65,
    0x9B, 0x99, 0x9F, 0x9D, 0x93, 0x91, 0x97, 0x95, 0x8B, 0x89, 0x8F, 0x8D, 0x83, 0x81, 0x87, 0x85,
    0xBB, 0xB9, 0xBF, 0xBD, 0xB3, 0xB1, 0xB7, 0xB5, 0xAB, 0xA9, 0xAF, 0xAD, 0xA3, 0xA1, 0xA7, 0xA5,
    0xDB, 0xD9, 0xDF, 0xDD, 0xD3, 0xD1, 0xD7, 0xD5, 0xCB, 0xC9, 0xCF, 0xCD, 0xC3, 0xC1, 0xC7, 0xC5,
    0xFB, 0xF9, 0xFF, 0xFD, 0xF3, 0xF1, 0xF7, 0xF5, 0xEB, 0xE9, 0xEF, 0xED, 0xE3, 0xE1, 0xE7, 0xE5
];

function mix_bytes($i0, $i1, $i2, $i3, $i4, $i5, $i6, $i7, array &$output, $output_offset) {

    global $mul2;

    $t0 = 0;
    $t1 = 0;
    $t2 = 0;
    $t3 = 0;
    $t4 = 0;
    $t5 = 0;
    $t6 = 0;
    $t7 = 0;
    $x0 = 0;
    $x1 = 0;
    $x2 = 0;
    $x3 = 0;
    $x4 = 0;
    $x5 = 0;
    $x6 = 0;
    $x7 = 0;
    $y0 = 0;
    $y1 = 0;
    $y2 = 0;
    $y3 = 0;
    $y4 = 0;
    $y5 = 0;
    $y6 = 0;
    $y7 = 0;

    $t0 = ($i0 ^ $i1) & 0xff;
    $t1 = ($i1 ^ $i2) & 0xff;
    $t2 = ($i2 ^ $i3) & 0xff;
    $t3 = ($i3 ^ $i4) & 0xff;
    $t4 = ($i4 ^ $i5) & 0xff;
    $t5 = ($i5 ^ $i6) & 0xff;
    $t6 = ($i6 ^ $i7) & 0xff;
    $t7 = ($i7 ^ $i0) & 0xff;

    $x0 = ($t0 ^ $t3) & 0xff;
    $x1 = ($t1 ^ $t4) & 0xff;
    $x2 = ($t2 ^ $t5) & 0xff;
    $x3 = ($t3 ^ $t6) & 0xff;
    $x4 = ($t4 ^ $t7) & 0xff;
    $x5 = ($t5 ^ $t0) & 0xff;
    $x6 = ($t6 ^ $t1) & 0xff;
    $x7 = ($t7 ^ $t2) & 0xff;

    $y0 = ($t0 ^ $t2 ^ $i6) & 0xff;
    $y1 = ($t1 ^ $t3 ^ $i7) & 0xff;
    $y2 = ($t2 ^ $t4 ^ $i0) & 0xff;
    $y3 = ($t3 ^ $t5 ^ $i1) & 0xff;
    $y4 = ($t4 ^ $t6 ^ $i2) & 0xff;
    $y5 = ($t5 ^ $t7 ^ $i3) & 0xff;
    $y6 = ($t6 ^ $t0 ^ $i4) & 0xff;
    $y7 = ($t7 ^ $t1 ^ $i5) & 0xff;

    $x3 = (($x3 & 0x80) ? ($x3 << 1) ^ 0x1B : ($x3 << 1)) & 0xff;
    $x0 = (($x0 & 0x80) ? ($x0 << 1) ^ 0x1B : ($x0 << 1)) & 0xff;

    $t0 = ($x3 ^ $y7) & 0xff;
    $t0 = (($t0 & 0x80) ? ($t0 << 1) ^ 0x1B : ($t0 << 1)) & 0xff;
    $t5 = ($x0 ^ $y4) & 0xff;
    $t5 = (($t5 & 0x80) ? ($t5 << 1) ^ 0x1B : ($t5 << 1)) & 0xff;

    $output[$output_offset + 0] = ($t0 ^ $y4);
    $output[$output_offset + 5] = ($t5 ^ $y1);

    $output[$output_offset + 1] = $mul2[$mul2[$x4] ^ $y0] ^ $y5;
    $output[$output_offset + 2] = $mul2[$mul2[$x5] ^ $y1] ^ $y6;
    $output[$output_offset + 3] = $mul2[$mul2[$x6] ^ $y2] ^ $y7;
    $output[$output_offset + 4] = $mul2[$mul2[$x7] ^ $y3] ^ $y0;
    $output[$output_offset + 6] = $mul2[$mul2[$x1] ^ $y5] ^ $y2;
    $output[$output_offset + 7] = $mul2[$mul2[$x2] ^ $y6] ^ $y3;
}

function perm_P(array &$input, array &$output) {
    global $sbox;
    $r0 = 0;
    $r1 = 0;
    $r2 = 0;
    $r3 = 0;
    $r4 = 0;
    $r5 = 0;
    $r6 = 0;
    $r7 = 0;
    $round = 0;
    $state = array_fill(0, 64, 0);
    $write = &$state;
    $read = &$input;
    $p_tmp = NULL;

    for ($round = 0; $round < ROUNDS; $round++) {

        $round &= 0xff;

        $r0 = $sbox[(($read[0] & 0xff) ^ $round) & 0xff];
        $r1 = $sbox[($read[9] & 0xff)];
        $r2 = $sbox[($read[18] & 0xff)];
        $r3 = $sbox[($read[27] & 0xff)];
        $r4 = $sbox[($read[36] & 0xff)];
        $r5 = $sbox[($read[45] & 0xff)];
        $r6 = $sbox[($read[54] & 0xff)];
        $r7 = $sbox[($read[63] & 0xff)];

        mix_bytes($r0, $r1, $r2, $r3, $r4, $r5, $r6, $r7, $write, 0);

        $r0 = $sbox[(($read[8] & 0xff) ^ $round ^ 0x10) & 0xff];
        $r1 = $sbox[($read[17] & 0xff)];
        $r2 = $sbox[($read[26] & 0xff)];
        $r3 = $sbox[($read[35] & 0xff)];
        $r4 = $sbox[($read[44] & 0xff)];
        $r5 = $sbox[($read[53] & 0xff)];
        $r6 = $sbox[($read[62] & 0xff)];
        $r7 = $sbox[($read[7] & 0xff)];
        mix_bytes($r0, $r1, $r2, $r3, $r4, $r5, $r6, $r7, $write, 8);

        $r0 = $sbox[(($read[16] & 0xff) ^ $round ^ 0x20) & 0xff];
        $r1 = $sbox[($read[25] & 0xff)];
        $r2 = $sbox[($read[34] & 0xff)];
        $r3 = $sbox[($read[43] & 0xff)];
        $r4 = $sbox[($read[52] & 0xff)];
        $r5 = $sbox[($read[61] & 0xff)];
        $r6 = $sbox[($read[6] & 0xff)];
        $r7 = $sbox[($read[15] & 0xff)];
        mix_bytes($r0, $r1, $r2, $r3, $r4, $r5, $r6, $r7, $write, 16);

        $r0 = $sbox[(($read[24] & 0xff) ^ $round ^ 0x30) & 0xff];
        $r1 = $sbox[($read[33] & 0xff)];
        $r2 = $sbox[($read[42] & 0xff)];
        $r3 = $sbox[($read[51] & 0xff)];
        $r4 = $sbox[($read[60] & 0xff)];
        $r5 = $sbox[($read[5] & 0xff)];
        $r6 = $sbox[($read[14] & 0xff)];
        $r7 = $sbox[($read[23] & 0xff)];
        mix_bytes($r0, $r1, $r2, $r3, $r4, $r5, $r6, $r7, $write, 24);

        $r0 = $sbox[(($read[32] & 0xff) ^ $round ^ 0x40) & 0xff];
        $r1 = $sbox[($read[41] & 0xff)];
        $r2 = $sbox[($read[50] & 0xff)];
        $r3 = $sbox[($read[59] & 0xff)];
        $r4 = $sbox[($read[4] & 0xff)];
        $r5 = $sbox[($read[13] & 0xff)];
        $r6 = $sbox[($read[22] & 0xff)];
        $r7 = $sbox[($read[31] & 0xff)];
        mix_bytes($r0, $r1, $r2, $r3, $r4, $r5, $r6, $r7, $write, 32);

        $r0 = $sbox[(($read[40] & 0xff) ^ $round ^ 0x50) & 0xff];
        $r1 = $sbox[($read[49] & 0xff)];
        $r2 = $sbox[($read[58] & 0xff)];
        $r3 = $sbox[($read[3] & 0xff)];
        $r4 = $sbox[($read[12] & 0xff)];
        $r5 = $sbox[($read[21] & 0xff)];
        $r6 = $sbox[($read[30] & 0xff)];
        $r7 = $sbox[($read[39] & 0xff)];
        mix_bytes($r0, $r1, $r2, $r3, $r4, $r5, $r6, $r7, $write, 40);

        $r0 = $sbox[(($read[48] & 0xff) ^ $round ^ 0x60) & 0xff];
        $r1 = $sbox[($read[57] & 0xff)];
        $r2 = $sbox[($read[2] & 0xff)];
        $r3 = $sbox[($read[11] & 0xff)];
        $r4 = $sbox[($read[20] & 0xff)];
        $r5 = $sbox[($read[29] & 0xff)];
        $r6 = $sbox[($read[38] & 0xff)];
        $r7 = $sbox[($read[47] & 0xff)];
        mix_bytes($r0, $r1, $r2, $r3, $r4, $r5, $r6, $r7, $write, 48);

        $r0 = $sbox[(($read[56] & 0xff) ^ $round ^ 0x70) & 0xff];
        $r1 = $sbox[($read[1] & 0xff)];
        $r2 = $sbox[($read[10] & 0xff)];
        $r3 = $sbox[($read[19] & 0xff)];
        $r4 = $sbox[($read[28] & 0xff)];
        $r5 = $sbox[($read[37] & 0xff)];
        $r6 = $sbox[($read[46] & 0xff)];
        $r7 = $sbox[($read[55] & 0xff)];
        mix_bytes($r0, $r1, $r2, $r3, $r4, $r5, $r6, $r7, $write, 56);

        if ($round == 0)
            $read = &$output;

        $p_tmp = &$read;
        $read = &$write;
        $write = &$p_tmp;
    }
}

function perm_Q(array &$input, array &$output) {
    global $sbox;
    $r0 = 0;
    $r1 = 0;
    $r2 = 0;
    $r3 = 0;
    $r4 = 0;
    $r5 = 0;
    $r6 = 0;
    $r7 = 0;
    $round = 0;
    $state = array_fill(0, 64, 0);
    $write = &$state;
    $read = &$input;
    $p_tmp = NULL;

    for ($round = 0; $round < ROUNDS; $round++) {
        $r0 = $sbox[($read[8] ^ 0xFF) & 0xff];
        $r1 = $sbox[($read[25] ^ 0xFF) & 0xff];
        $r2 = $sbox[($read[42] ^ 0xFF) & 0xff];
        $r3 = $sbox[($read[59] ^ 0xFF) & 0xff];
        $r4 = $sbox[($read[4] ^ 0xFF) & 0xff];
        $r5 = $sbox[($read[21] ^ 0xFF) & 0xff];
        $r6 = $sbox[($read[38] ^ 0xFF) & 0xff];
        $r7 = $sbox[($read[55] ^ 0x9F ^ $round) & 0xff];
        mix_bytes($r0, $r1, $r2, $r3, $r4, $r5, $r6, $r7, $write, 0);

        $r0 = $sbox[($read[16] ^ 0xFF) & 0xff];
        $r1 = $sbox[($read[33] ^ 0xFF) & 0xff];
        $r2 = $sbox[($read[50] ^ 0xFF) & 0xff];
        $r3 = $sbox[($read[3] ^ 0xFF) & 0xff];
        $r4 = $sbox[($read[12] ^ 0xFF) & 0xff];
        $r5 = $sbox[($read[29] ^ 0xFF) & 0xff];
        $r6 = $sbox[($read[46] ^ 0xFF) & 0xff];
        $r7 = $sbox[($read[63] ^ 0x8F ^ $round) & 0xff];
        mix_bytes($r0, $r1, $r2, $r3, $r4, $r5, $r6, $r7, $write, 8);

        $r0 = $sbox[($read[24] ^ 0xFF) & 0xff];
        $r1 = $sbox[($read[41] ^ 0xFF) & 0xff];
        $r2 = $sbox[($read[58] ^ 0xFF) & 0xff];
        $r3 = $sbox[($read[11] ^ 0xFF) & 0xff];
        $r4 = $sbox[($read[20] ^ 0xFF) & 0xff];
        $r5 = $sbox[($read[37] ^ 0xFF) & 0xff];
        $r6 = $sbox[($read[54] ^ 0xFF) & 0xff];
        $r7 = $sbox[($read[7] ^ 0xFF ^ $round) & 0xff];
        mix_bytes($r0, $r1, $r2, $r3, $r4, $r5, $r6, $r7, $write, 16);

        $r0 = $sbox[($read[32] ^ 0xFF) & 0xff];
        $r1 = $sbox[($read[49] ^ 0xFF) & 0xff];
        $r2 = $sbox[($read[2] ^ 0xFF) & 0xff];
        $r3 = $sbox[($read[19] ^ 0xFF) & 0xff];
        $r4 = $sbox[($read[28] ^ 0xFF) & 0xff];
        $r5 = $sbox[($read[45] ^ 0xFF) & 0xff];
        $r6 = $sbox[($read[62] ^ 0xFF) & 0xff];
        $r7 = $sbox[($read[15] ^ 0xEF ^ $round) & 0xff];
        mix_bytes($r0, $r1, $r2, $r3, $r4, $r5, $r6, $r7, $write, 24);

        $r0 = $sbox[($read[40] ^ 0xFF) & 0xff];
        $r1 = $sbox[($read[57] ^ 0xFF) & 0xff];
        $r2 = $sbox[($read[10] ^ 0xFF) & 0xff];
        $r3 = $sbox[($read[27] ^ 0xFF) & 0xff];
        $r4 = $sbox[($read[36] ^ 0xFF) & 0xff];
        $r5 = $sbox[($read[53] ^ 0xFF) & 0xff];
        $r6 = $sbox[($read[6] ^ 0xFF) & 0xff];
        $r7 = $sbox[($read[23] ^ 0xDF ^ $round) & 0xff];
        mix_bytes($r0, $r1, $r2, $r3, $r4, $r5, $r6, $r7, $write, 32);

        $r0 = $sbox[($read[48] ^ 0xFF) & 0xff];
        $r1 = $sbox[($read[1] ^ 0xFF) & 0xff];
        $r2 = $sbox[($read[18] ^ 0xFF) & 0xff];
        $r3 = $sbox[($read[35] ^ 0xFF) & 0xff];
        $r4 = $sbox[($read[44] ^ 0xFF) & 0xff];
        $r5 = $sbox[($read[61] ^ 0xFF) & 0xff];
        $r6 = $sbox[($read[14] ^ 0xFF) & 0xff];
        $r7 = $sbox[($read[31] ^ 0xCF ^ $round) & 0xff];
        mix_bytes($r0, $r1, $r2, $r3, $r4, $r5, $r6, $r7, $write, 40);

        $r0 = $sbox[($read[56] ^ 0xFF) & 0xff];
        $r1 = $sbox[($read[9] ^ 0xFF) & 0xff];
        $r2 = $sbox[($read[26] ^ 0xFF) & 0xff];
        $r3 = $sbox[($read[43] ^ 0xFF) & 0xff];
        $r4 = $sbox[($read[52] ^ 0xFF) & 0xff];
        $r5 = $sbox[($read[5] ^ 0xFF) & 0xff];
        $r6 = $sbox[($read[22] ^ 0xFF) & 0xff];
        $r7 = $sbox[($read[39] ^ 0xBF ^ $round) & 0xff];
        mix_bytes($r0, $r1, $r2, $r3, $r4, $r5, $r6, $r7, $write, 48);

        $r0 = $sbox[($read[0] ^ 0xFF) & 0xff];
        $r1 = $sbox[($read[17] ^ 0xFF) & 0xff];
        $r2 = $sbox[($read[34] ^ 0xFF) & 0xff];
        $r3 = $sbox[($read[51] ^ 0xFF) & 0xff];
        $r4 = $sbox[($read[60] ^ 0xFF) & 0xff];
        $r5 = $sbox[($read[13] ^ 0xFF) & 0xff];
        $r6 = $sbox[($read[30] ^ 0xFF) & 0xff];
        $r7 = $sbox[($read[47] ^ 0xAF ^ $round) & 0xff];
        mix_bytes($r0, $r1, $r2, $r3, $r4, $r5, $r6, $r7, $write, 56);

        if ($round == 0)
            $read = &$output;

        $p_tmp = &$read;
        $read = &$write;
        $write = &$p_tmp;
    }
}

function crypto_hash_gl(array &$out, array &$in, $inlen) {
    #if ($inlen >= (1ULL << 16))
#	return -1;

    $msg_len = $inlen;
    $padded_len = (intval(intval($msg_len + 9 - 1) / 64) * 64) + 64;

    $pad_block_len = ($padded_len - $msg_len);
    $pad_block = array_fill(0, $pad_block_len, 0);

    /* Append 1-bit */
    #memset($pad_block, 0, $pad_block_len);
    $pad_block[0] = 0x80;

    /* Add number of blocks (note atmega128 does not have more memory so upper 40 bits are implicit '0') */
    $blocks = ($padded_len >> 6) & 0xff;
    $pad_block[$pad_block_len - 1] = ($blocks & 0xFF);

    /* Start hashing the padded message */
    $h_state = array_fill(0, 64, 0);
    $p_state = array_fill(0, 64, 0);
    $q_state = array_fill(0, 64, 0);
    $x_state = array_fill(0, 64, 0);
    $buf = array_fill(0, 64, 0);

    //memset(h_state, 0, 64);

    $h_state[62] = 0x01;

    /* Go through each block of data */
    $i = 0;
    $t = 0;
    $block = 0;
    $remaining = 0;
    $message_left_len = $msg_len & 0xffffffff;

    for ($block = 0; $block < $blocks; $block++) {

        if ((($block * 64) + 64) < $msg_len) { // one whole block 
            for ($t = 0; $t < 64; ++$t)
                $buf[$t] = $in[$t + (64 * $block)] & 0xff;

            $message_left_len -= 64;
        } else if ($message_left_len > 0) { // part message, part padding block 
            $remaining = 64 - $message_left_len;

            for ($t = 0; $t < $message_left_len; ++$t)
                $buf[$t] = $in[$t + (64 * $block)] & 0xff;
            for ($t = 0; $t < $remaining; ++$t)
                $buf[$t + $message_left_len] = $pad_block[$t] & 0xff;

            $message_left_len = 0;
        } else { // only padding
            for ($t = 0; $t < 64; ++$t)
                $buf[$t] = $pad_block[$t + $remaining] & 0xff;
        }

        for ($i = 0; $i < 64; $i++)
            $x_state[$i] = ($buf[$i] ^ $h_state[$i]) & 0xff;

        perm_P($x_state, $p_state);
        perm_Q($buf, $q_state);



        for ($i = 0; $i < 64; $i++)
            $h_state[$i] ^= ($p_state[$i] ^ $q_state[$i]) & 0xff;
    }

    perm_P($h_state, $p_state);

    for ($i = 32; $i < 64; $i++)
        $out[$i - 32] = ($h_state[$i] ^ $p_state[$i]) & 0xff;

    return 0;
}
/*
error_reporting(E_ALL);
ini_set('display_errors', 'On');




$test = array(
    array(
        'f48290b1bcacee406a0429b993adb8fb3d065f4b09cbcdb464a631d4a0080aaf',
        'The quick brown fox jumps over the lazy dog.'
    ),
  
    array(
        '8c7ad62eb26a21297bc39c2d7293b4bd4d3399fa8afab29e970471739e28b301',
        'The quick brown fox jumps over the lazy dog'
    ),
    array('f3c1bb19c048801326a7efbcf16e3d7887446249829c379e1840d1a3a1e7d4d2',
        'abc'),
    array('22c23b160e561f80924d44f2cc5974cd5a1d36f69324211861e63b9b6cb7974c',
        'abcdbcdecdefdefgefghfghighijhijkijkljklmklmnlmnomnopnopq'),
);
$out = array_fill(0, 32, 0);

foreach ($test as $t) {
    list($validhex, $str) = $t;

    $sss = str_split($str);
    $a = !empty($sss) ?
            array_map('ord', !empty($sss) ? $sss : array()) :
            array();

    crypto_hash($out, $a, count($a));

    $x = bin2hex(implode('', array_map('chr', $out)));

    if ($x === $validhex) {
        printf("OK\n");
    } else {
        printf("FAILED Blyad\n");
        var_dump($a, $x, $validhex);
    }
    #die;
}

*/