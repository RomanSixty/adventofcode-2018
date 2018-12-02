<?php

$input = file ( __DIR__ . '/input' );

// part 1

$two_letters = $three_letters = 0;

foreach ( $input as $box_id )
{
    $letters = str_split  ( $box_id );

    $lettercount = [];

    foreach ( $letters as $l )
    {
        if ( !isset ( $lettercount [ $l ] ) )
            $lettercount [ $l ] = 0;

        $lettercount [ $l ]++;
    }

    $lettercount = array_flip ( $lettercount );

    if ( isset ( $lettercount [ 2 ] ) )
        $two_letters++;

    if ( isset ( $lettercount [ 3 ] ) )
        $three_letters++;
}

$output = $two_letters * $three_letters;

echo 'First Part: ' . $output . "\n";

// part 2

// find the two corresponding IDs
while ( $box_id_1 = array_pop ( $input ) )
{
    foreach ( $input as $box_id_2 )
    {
        if ( levenshtein ( $box_id_1, $box_id_2 ) == 1 )
        {
            $box_id_1 = trim ( $box_id_1 );
            $box_id_2 = trim ( $box_id_2 );

            break 2;
        }
    }
}

// find all identical characters

$output = '';

for ( $i = 0; $i < strlen ( $box_id_1 ); $i++ )
    if ( $box_id_1{$i} == $box_id_2{$i} )
        $output .= $box_id_1{$i};

echo 'Second Part: ' . $output . "\n";