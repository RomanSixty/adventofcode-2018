<?php

// this one is identical to solve.php except that I left all debug
// code in it, e.g. for mapping the maze and printing the path

$input = str_split ( trim ( file_get_contents ( __DIR__ . '/input' ) ) );

$map = [];

$x = $y = 0;

$min = $max = [ 'x' => 0, 'y' => 0 ];

makeRoom ( $x, $y );

$map [ $y ][ $x ][ 'char' ] = 'X';

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

    // door
    if ( !empty ( $diff_x ) || !empty ( $diff_y ) )
    {
        $door_char = $diff_x != 0 ? '█' : '█';

        $map [ $y + $diff_y ][ $x + $diff_x ][ 'char' ] = $door_char;
    }

    // new room
    $x += 2 * $diff_x;
    $y += 2 * $diff_y;

    $map [ $y ][ $x ][ 'char' ] = '█';

    // walls
    $map [ $y - 1 ][ $x - 1 ][ 'char' ] = '#';
    $map [ $y - 1 ][ $x + 1 ][ 'char' ] = '#';
    $map [ $y + 1 ][ $x - 1 ][ 'char' ] = '#';
    $map [ $y + 1 ][ $x + 1 ][ 'char' ] = '#';

    // possible other doors
    if ( empty ( $map [ $y ][ $x + 1 ][ 'char' ] ) ) $map [ $y ][ $x + 1 ][ 'char' ] = '?';
    if ( empty ( $map [ $y ][ $x - 1 ][ 'char' ] ) ) $map [ $y ][ $x - 1 ][ 'char' ] = '?';
    if ( empty ( $map [ $y + 1 ][ $x ][ 'char' ] ) ) $map [ $y + 1 ][ $x ][ 'char' ] = '?';
    if ( empty ( $map [ $y - 1 ][ $x ][ 'char' ] ) ) $map [ $y - 1 ][ $x ][ 'char' ] = '?';

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
$farthest_room      = [];

for ( $y = $min [ 'y' ]; $y <= $max [ 'y' ]; $y++ )
    for ( $x = $min [ 'x' ]; $x <= $max [ 'x' ]; $x++ )
    {
        if ( !isset ( $map [ $y ][ $x ][ 'dist' ] ) )
            continue;

        $longest_short_path = max ( $longest_short_path, $map [ $y ][ $x ][ 'dist' ] );

        if ( $longest_short_path == $map [ $y ][ $x ][ 'dist' ] )
            $farthest_room = [ $x, $y ];

        if ( $map [ $y ][ $x ][ 'dist' ] >= 1000 )
            $rooms_with_1000++;
    }

function printMap()
{
    global $map, $min, $max;

    echo str_pad ( '', $max [ 'x' ] - $min [ 'x' ] + 3, '#' ) . "\n";

    for ( $y = $min [ 'y' ]; $y <= $max [ 'y' ]; $y++ )
    {
        echo '#';

        for ( $x = $min [ 'x' ]; $x <= $max [ 'x' ]; $x++ )
        {
            if ( empty ( $map [ $y ][ $x ] ) )
                $map [ $y ][ $x ][ 'char' ] = ' ';
            elseif ( $map [ $y ][ $x ][ 'char' ] == '?' )
                $map [ $y ][ $x ][ 'char' ] = '#';

            if ( $map [ $y ][ $x ][ 'char' ] == 'X' || isset ( $map [ $y ][ $x ][ 'mark' ] ) )
                echo "\033[0;31m" . $map [ $y ][ $x ][ 'char' ] . "\033[0m";
            else
                echo $map [ $y ][ $x ][ 'char' ];
        }

        echo "#\n";
    }

    echo str_pad ( '', $max [ 'x' ] - $min [ 'x' ] + 3, '#' ) . "\n\n";
}

// mark path
$x = $farthest_room [ 0 ];
$y = $farthest_room [ 1 ];

while ( $x != 0 || $y != 0 )
{
    $map [ $y ][ $x ][ 'mark' ] = true;
    echo $map [ $y ][ $x ][ 'dist' ] . "\n";

    foreach ( $map [ $y ][ $x ][ 'adjacent' ] as $adjacent )
        if ( $map [ $adjacent [ 1 ]][ $adjacent [ 0 ]][ 'dist' ] == $map [ $y ][ $x ][ 'dist' ] - 1 )
        {
            if ( $adjacent [ 0 ] != $x )
            {
                if ( $adjacent [ 0 ] > $x )
                    $map [ $y ][ $x+1 ][ 'mark' ] = true;
                else
                    $map [ $y ][ $x-1 ][ 'mark' ] = true;
            }
            else
            {
                if ( $adjacent [ 1 ] > $y )
                    $map [ $y+1 ][ $x ][ 'mark' ] = true;
                else
                    $map [ $y-1 ][ $x ][ 'mark' ] = true;
            }

            $x = $adjacent [ 0 ];
            $y = $adjacent [ 1 ];
            continue 2;
        }

    break;
}

printMap();

echo 'First Part: ' . $longest_short_path . "\n";
echo 'Second Part: ' . $rooms_with_1000 . "\n";