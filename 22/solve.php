<?php

$input = file ( __DIR__ . '/input' );

list ( $dummy, $depth  ) = explode ( ' ', $input [ 0 ] );
list ( $dummy, $target ) = explode ( ' ', $input [ 1 ] );

$target = explode ( ',', $target );

$padding = 50; // don't let this be too small

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

// for convenience reasons we treat tools as integers in a way, that its value
// equals the value of the geological type where it cannot be used... so:
// 0 : rocky  : neither
// 1 : wet    : torch
// 2 : narrow : climbing gear

function findShortestTime()
{
    global $cave;

    $queue = new SPLPriorityQueue();

    $queue -> setExtractFlags ( SplPriorityQueue::EXTR_BOTH );

    $queue -> insert ( [ 0, 0, 1 ], 0 );

    while ( !$queue -> isEmpty() )
    {
        $q = $queue -> extract();

        list ( $x, $y, $tool ) = $q [ 'data' ];

        if ( isset ( $cave [ $y ][ $x ][ 'distance' ][ $tool ] ) )
            continue;

        // higher is better in SPLPriorityQueue...
        // but for our problem it's worse, so we use it as a negative value

        $cave [ $y ][ $x ][ 'distance' ][ $tool ] = abs ( $q [ 'priority' ] );

        // tool is allowed
        foreach ( getAdjacent ( $x, $y ) as $adjacent )
            if ( $cave [ $adjacent [ 1 ]][ $adjacent [ 0 ]][ 'type' ] != $tool )
                $queue -> insert ( [ $adjacent [ 0 ], $adjacent [ 1 ], $tool ], -( $cave [ $y ][ $x ][ 'distance' ][ $tool ] + 1 ) );

        // tool is not allowed, so change it and try again
        $other_tool = ( $tool + 1 ) % 3 == $cave [ $y ][ $x ][ 'type' ] ? ( $tool + 2 ) % 3 : ( $tool + 1 ) % 3;

        if ( !isset ( $cave [ $y ][ $x ][ 'distance' ][ $other_tool ] ) )
            $queue -> insert ( [ $x, $y, $other_tool], -( $cave [ $y ][ $x ][ 'distance' ][ $tool ] + 7 ) );
    }
}

function getAdjacent ( $x, $y )
{
    global $cave;

    $adjacent = [];

    if ( $x < count ( $cave [ 0 ] ) - 1 )
        $adjacent[] = [ $x + 1, $y ];

    if ( $y < count ( $cave ) - 1 )
        $adjacent[] = [ $x, $y + 1 ];

    if ( $x > 0 )
        $adjacent[] = [ $x - 1, $y ];

    if ( $y > 0 )
        $adjacent[] = [ $x, $y - 1 ];

    return $adjacent;
}

findShortestTime();

echo 'First Part: ' . $risk_level . "\n";
echo 'Second Part: ' . $cave [ $target [ 1 ]][ $target [ 0 ]][ 'distance' ][ 1 ] . "\n";