<?php

$input = explode ( "\n\n\n", file_get_contents ( __DIR__ . '/input' ) );

$opcodes = [
    'addr' => 'A+B',
    'addi' => 'A+Y',
    'mulr' => 'A*B',
    'muli' => 'A*Y',
    'banr' => 'A&B',
    'bani' => 'A&Y',
    'borr' => 'A|B',
    'bori' => 'A|Y',
    'setr' => 'A',
    'seti' => 'X',
    'gtir' => 'X>B',
    'gtri' => 'A>Y',
    'gtrr' => 'A>B',
    'eqir' => 'X==B',
    'eqri' => 'A==Y',
    'eqrr' => 'A==B'
];

$possible_opcodes = [];

for ( $i = 0; $i < count ( $opcodes ); $i++ )
    foreach ( array_keys ( $opcodes ) as $opcode )
        $possible_opcodes [ $i ][ $opcode ] = true;

$dump = [];

//                                     1     2     3     4         5    6    7    8                9    10    11    12
preg_match_all ( '~Before: \[(\d), (\d), (\d), (\d)\]\s+(\d+) (\d) (\d) (\d)\s+After:  \[(\d), (\d), (\d), (\d)\]~ms', $input [ 0 ], $dump,  PREG_SET_ORDER );

$part_1 = 0;

foreach ( $dump as &$testcase )
{
    $matches = 0;

    foreach ( $opcodes as $opcode => $instruction )
    {
        $registers = [ intval ( $testcase [ 1 ] ), intval ( $testcase [ 2 ] ), intval ( $testcase [ 3 ] ), intval ( $testcase [ 4 ] )];

        runInstruction ( $registers, $instruction, $testcase [ 6 ], $testcase [ 7 ], $testcase [ 8 ] );

        if ( $registers [ 0 ] == $testcase [ 9 ] && $registers [ 1 ] == $testcase [ 10 ] && $registers [ 2 ] == $testcase [ 11 ] && $registers [ 3 ] == $testcase [ 12 ] )
            $matches++;
        else
            unset ( $possible_opcodes [ $testcase [ 5 ]][ $opcode ] );
    }

    if ( $matches >= 3 )
        $part_1++;
}

$opcode_numbers = [];

while ( count ( $possible_opcodes ) )
    foreach ( $possible_opcodes as $opcode_number => &$possibilities )
    {
        if ( count ( $possibilities ) == 1 )
        {
            $opcode_numbers [ $opcode_number ] = key ( $possibilities );
            unset ( $possible_opcodes [ $opcode_number ] );
        }
        else
            foreach ( $opcode_numbers as $opcode )
                unset ( $possibilities [ $opcode ] );
    }

$test_program = [];

preg_match_all ( '~\s(\d+) (\d) (\d) (\d)~ms', $input [ 1 ], $test_program, PREG_SET_ORDER );

$registers = [ 0, 0, 0, 0 ];

foreach ( $test_program as $line )
    runInstruction ( $registers, $opcodes [ $opcode_numbers [ $line [ 1 ]]], $line [ 2 ], $line [ 3 ], $line [ 4 ] );

function runInstruction ( &$registers, $instruction, $A, $B, $C )
{
    $replacements = [
        'A' => intval ( $registers [ $A ] ),
        'B' => intval ( $registers [ $B ] ),
        'X' => intval ( $A ),
        'Y' => intval ( $B ),
        'C' => intval ( $C )
    ];

    eval ( strtr ( '$registers[C] = intval(' . $instruction . ')', $replacements ) . ';' );
}

echo 'First Part: ' . $part_1 . "\n";
echo 'Second Part: ' . $registers [ 0 ] . "\n";