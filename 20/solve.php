<?php

$input = str_split ( trim ( file_get_contents ( __DIR__ . '/input' ) ) );

$map = [];

$x = $y = 0;

$min = $max = [ 'x' => 0, 'y' => 0 ];

makeRoom ( $x, $y );

function parseInput ( $x, $y )
{
    global $input, $min, $max;

    $orig_x = $x;
    $orig_y = $y;

    while ( $char = array_shift ( $input ) )
    {
        switch ( $char )
        {
            case 'N': makeRoom ( $x, $y, 0, -1 ); break;
            case 'E': makeRoom ( $x, $y, 1,  0 ); break;
            case 'S': makeRoom ( $x, $y, 0,  1 ); break;
            case 'W': makeRoom ( $x, $y, -1, 0 ); break;

            case '(': parseInput ( $x, $y ); break;
            case ')': return;

            case '|':
                $x = $orig_x;
                $y = $orig_y;
                break;
        }

        $min [ 'x' ] = min ( $min [ 'x' ], $x );
        $max [ 'x' ] = max ( $max [ 'x' ], $x );
        $min [ 'y' ] = min ( $min [ 'y' ], $y );
        $max [ 'y' ] = max ( $max [ 'y' ], $y );
    }
}

function makeRoom ( &$x, &$y, $diff_x = 0, $diff_y = 0 )
{
    global $map;

    // new room
    $x += 2 * $diff_x;
    $y += 2 * $diff_y;

    // connecting two rooms
    if ( !empty ( $diff_x ) || !empty ( $diff_y ) )
    {
        $map [ $y ][ $x ][ 'adjacent' ][] = [ $x - 2*$diff_x, $y - 2*$diff_y ]; // B -> A
        $map [ $y - 2*$diff_y ][ $x - 2*$diff_x ][ 'adjacent' ][] = [ $x, $y ]; // A -> B

        if ( !isset ( $map [ $y ][ $x ][ 'dist' ] ) )
            $map [ $y ][ $x ][ 'dist' ] = PHP_INT_MAX;

        // we wouldn't recognize closed loops using this method
        // luckily our input doesn't have them
        foreach ( $map [ $y ][ $x ][ 'adjacent' ] as $adjacent )
            $map [ $y ][ $x ][ 'dist' ] = min ( $map [ $adjacent [ 1 ]][ $adjacent [ 0 ]][ 'dist' ] + 1, $map [ $y ][ $x ][ 'dist' ] );
    }
    else
        $map [ $y ][ $x ][ 'dist' ] = 0;

}

parseInput ( $x, $y );

$longest_short_path = 0;
$rooms_with_1000    = 0;

for ( $y = $min [ 'y' ]; $y <= $max [ 'y' ]; $y++ )
    for ( $x = $min [ 'x' ]; $x <= $max [ 'x' ]; $x++ )
    {
        if ( !isset ( $map [ $y ][ $x ][ 'dist' ] ) )
            continue;

        $longest_short_path = max ( $longest_short_path, $map [ $y ][ $x ][ 'dist' ] );

        if ( $map [ $y ][ $x ][ 'dist' ] >= 1000 )
            $rooms_with_1000++;
    }

echo 'First Part: ' . $longest_short_path . "\n";
echo 'Second Part: ' . $rooms_with_1000 . "\n";