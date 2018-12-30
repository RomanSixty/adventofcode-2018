<?php

$program = file ( __DIR__ . '/input' );

$ip_reg = 0;

$first_line = array_shift ( $program );

if ( preg_match ( '~#ip (\d)~', $first_line, $matches ) )
    $ip_reg = intval ( $matches [ 1 ] );

// let's "precompile" the program
foreach ( $program as &$line )
{
    $matches = [];

    preg_match ( '~^([[:alpha:]]{4}) (\d+) (\d+) (\d+)~', $line, $matches );

    array_shift ( $matches );

    $matches [ 1 ] = intval ( $matches [ 1 ] );
    $matches [ 2 ] = intval ( $matches [ 2 ] );
    $matches [ 3 ] = intval ( $matches [ 3 ] );

    $line = $matches;
}

function runProgram ( &$ip, &$registers, $program, $ip_reg )
{
    while ( isset ( $program [ $ip ] ) )
    {
        $instruction = $program [ $ip ];

        $registers [ $ip_reg ] = $ip;

        $registers [ $instruction [ 3 ]] = Opcodes::{$instruction[ 0 ]} ( $registers, $instruction [ 1 ], $instruction [ 2 ] );

        $ip = $registers [ $ip_reg ];

        $ip++;

        echo implode ( ',', $registers ) . "\n";
    }
}

$registers = [ 0, 0, 0, 0, 0, 0 ];
$ip        = 0;

runProgram ( $ip, $registers, $program, $ip_reg );

echo 'First Part: ' . $registers [ 0 ] . "\n";

// second part runs into a veeeery long loop
// so let's just stop here
die();

$registers = [ 1, 0, 0, 0, 0, 0 ];
$ip        = 0;

runProgram ( $ip, $registers, $program, $ip_reg );

echo 'Second Part: ' . $registers [ 0 ] . "\n";

class Opcodes
{
    public static function addr ( $registers, $A, $B ) { return $registers [ $A ] + $registers [ $B ]; }
    public static function addi ( $registers, $A, $B ) { return $registers [ $A ] + $B; }

    public static function mulr ( $registers, $A, $B ) { return $registers [ $A ] * $registers [ $B ]; }
    public static function muli ( $registers, $A, $B ) { return $registers [ $A ] * $B; }

    public static function banr ( $registers, $A, $B ) { return $registers [ $A ] & $registers [ $B ]; }
    public static function bani ( $registers, $A, $B ) { return $registers [ $A ] & $B; }

    public static function borr ( $registers, $A, $B ) { return $registers [ $A ] | $registers [ $B ]; }
    public static function bori ( $registers, $A, $B ) { return $registers [ $A ] | $B; }

    public static function setr ( $registers, $A, $B ) { return $registers [ $A ]; }
    public static function seti ( $registers, $A, $B ) { return $A; }

    public static function gtir ( $registers, $A, $B ) { return intval ( $A > $registers [ $B ] ); }
    public static function gtri ( $registers, $A, $B ) { return intval ( $registers [ $A ] > $B ); }
    public static function gtrr ( $registers, $A, $B ) { return intval ( $registers [ $A ] > $registers [ $B ]); }

    public static function eqir ( $registers, $A, $B ) { return intval ( $A == $registers [ $B ] ); }
    public static function eqri ( $registers, $A, $B ) { return intval ( $registers [ $A ] == $B ); }
    public static function eqrr ( $registers, $A, $B ) { return intval ( $registers [ $A ] == $registers [ $B ] ); }
}