<?php

ini_set('xdebug.max_nesting_level', 200000);

$start = microtime ( true );

$input = file ( __DIR__ . '/input' );

list ( $dummy, $depth ) = explode ( ' ', $input [ 0 ] );
list ( $dummy, $target ) = explode ( ' ', $input [ 1 ] );

$target = explode ( ',', $target );

$padding = 3;

//$target = [ 10, 10 ];
//$depth = 510;

$cave = [];
$risk_level = 0;

for ( $y = 0; $y <= $target [ 1 ] + $padding; $y++ )
    for ( $x = 0; $x <= $target [ 0 ] + $padding; $x++ )
    {
        if ( $y == 0 )
            $geo_index = $x * 16807;
        elseif ( $x == 0 )
            $geo_index = $y * 48271;
        elseif ( $x == $target [ 0 ] && $y == $target [ 1 ] )
            $geo_index = 0;
        else
            $geo_index = $cave [ $y - 1 ][ $x ][ 'erosion' ] * $cave [ $y ][ $x - 1 ][ 'erosion' ];

        $erosion = ( $geo_index + $depth ) % 20183;

        $type = $erosion % 3;

        $cave [ $y ][ $x ] = [
            'erosion'   => $erosion,
            'type'      => $type
        ];

        if ( $x <= $target [ 0 ] && $y <= $target [ 1 ] )
            $risk_level += $type;
    }

findPath ( 0, 0, 0 );
printCave();

echo 'First Part: ' . $risk_level . "\n";
echo 'Second Part: ' . $cave [ $target [ 1 ]][ $target [ 0 ]][ 'distance' ] . "\n";

function findPath ( $x, $y, $distance, $tool = 1 )
{
    static $best_so_far = 1050;

    global $cave, $target;

    //echo "$distance | $best_so_far\n";
    //printCave ( [ $x, $y ] );

    if ( !isset ( $cave [ $y ][ $x ][ 'distance' ] ) )
        $cave [ $y ][ $x ][ 'distance' ] = $distance;
    else
    {
        if ( $cave [ $y ][ $x ][ 'distance' ] < $distance )
            return;

        $cave [ $y ][ $x ][ 'distance' ] = $distance;
    }

    if ( $x == $target [ 0 ] && $y == $target [ 1 ] )
    {
        $new_distance = $tool == 1 ? $distance : $distance + 7;
        $best_so_far = min ( $best_so_far, $new_distance );
        $cave [ $y ][ $x ][ 'distance' ] = $best_so_far;
    }

    if ( $distance >= $best_so_far )
        return;

    foreach ( getAdjacent ( $x, $y, $tool ) as $adjacent )
    {
        $next = $cave [ $adjacent [ 1 ]][ $adjacent [ 0 ]];

        if ( $next [ 'type' ] != $tool )
            findPath ( $adjacent [ 0 ], $adjacent [ 1 ], $distance + 1, $tool );
        elseif ( isset ( $next [ 'distance' ] ) && $next [ 'distance' ] < $distance + 8 )
            continue;
        else
        {
            $next_tool = ( $tool + 1 ) % 3 == $cave [ $y ][ $x ][ 'type' ] ? ( $tool + 2 ) % 3 : ( $tool + 1 ) % 3;

            findPath ( $adjacent [ 0 ], $adjacent [ 1 ], $distance + 8, $next_tool );
        }
    }
}

function getAdjacent ( $x, $y, $tool )
{
    global $cave;

    if ( $x < count ( $cave [ 0 ] ) - 1 )
        $adjacent[] = [ $x + 1, $y ];

    if ( $y < count ( $cave ) - 1 )
        $adjacent[] = [ $x, $y + 1 ];

    if ( $x > 0 )
        $adjacent[] = [ $x - 1, $y ];

    if ( $y > 0 )
        $adjacent[] = [ $x, $y - 1 ];

    usort ( $adjacent, function ( $a, $b ) use ( $tool ) {
        global $cave, $target;

        if ( $b [ 0 ] == $target [ 0 ] && $b [ 1 ] == $target [ 1 ] )
            return 1;

        //if ( isset ( $cave [ $a [ 1 ]][ $a [ 0 ]][ 'distance' ] ) && isset ( $cave [ $b [ 1 ]][ $b [ 0 ]][ 'distance' ] ) && $cave [ $a [ 1 ]][ $a [ 0 ]][ 'distance' ] < $cave [ $b [ 1 ]][ $b [ 0 ]][ 'distance' ] )
        //    return 1;

        return ( $tool == $cave [ $a [ 1 ]][ $a [ 0 ]][ 'type' ] ) ? 1 : -1;
    });

    return $adjacent;
}

function printCave ( $hilight = [ 0, 0 ] )
{
    global $cave, $target;

    foreach ( $cave as $y => $row )
    {
        foreach ( $row as $x => $cell )
        {
            switch ( $cell [ 'type' ] )
            {
                case 0: $char = '.'; break;
                case 1: $char = '='; break;
                case 2: $char = '|'; break;
            }

            if ( $x == 0 && $y == 0 )
                $char = 'M';

            if ( $x == $target [ 0 ] && $y == $target [ 1 ] )
                $char = 'T';

            if ( $x == $hilight [ 0 ] && $y == $hilight [ 1 ] )
                echo "\033[0;31m" . $char . "\033[0m";
            elseif ( isset ( $cell [ 'distance' ] ) )
                echo "\033[0;100m" . $char . "\033[0m";
            else
                echo $char;
        }

        echo "\n";
    }
}

$end = microtime ( true );

echo $end - $start . 'ms';