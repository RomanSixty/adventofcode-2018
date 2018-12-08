<?php

$input = file ( __DIR__ . '/input' );

$points = [];

// bounds of the plane
// areas extending beyond those lead to infinity

$x_min = null;
$y_min = null;
$x_max = null;
$y_max = null;

foreach ( $input as $line )
{
    list ( $y, $x ) = explode ( ', ', trim ( $line ) );

    $x_min = ( $x_min === null ) ? $x : min ( $x, $x_min );
    $y_min = ( $y_min === null ) ? $y : min ( $y, $y_min );

    $x_max = ( $x_max === null ) ? $x : max ( $x, $x_max );
    $y_max = ( $y_max === null ) ? $y : max ( $y, $y_max );

    $points[] = [ $x , $y, 0 ];
}

$max_distance = $x_max - $x_min + $y_max - $y_min;

$plane = [];

for ( $x = $x_min; $x <= $x_max; $x++ )
    for ( $y = $y_min; $y <= $y_max; $y++ )
        distances ( $points, $plane, $x, $y );

/**
 * get distances of all coordinates to all points
 * also find the closest point for every coordinate
 *
 * @param array $all_points list of all points we got
 * @param array $plane rectangle of all coordinates within the boundaries
 * @param int $x x-coordinate for plane point to check
 * @param int $y y-coordinate for plane point to check
 */
function distances ( &$all_points, &$plane, &$x, &$y )
{
    global $max_distance;

    $distance = $max_distance;
    $closest  = false;

    foreach ( $all_points as $key => $b )
    {
        $plane [ $x ][ $y ][ 'distances' ][ $key ] = abs ( $b [ 0 ] - $x ) + abs ( $b [ 1 ] - $y );

        if ( $plane [ $x ][ $y ][ 'distances' ][ $key ] < $distance )
        {
            $distance = $plane [ $x ][ $y ][ 'distances' ][ $key ];

            $closest = $key;
        }

        // equidistance to more than one point
        elseif ( $plane [ $x ][ $y ][ 'distances' ][ $key ] == $distance )
            $closest = false;
    }

    // there is a single closest point
    if ( $closest )
        $all_points [ $closest ][ 2 ]++;
}

// part 1

$largest = 0;

foreach ( $points as $point )
    $largest = max ( $point [ 2 ], $largest );

echo 'First Part: ' . $largest . "\n";

// part 2

$size = 0;

for ( $x = $x_min; $x <= $x_max; $x++ )
    for ( $y = $y_min; $y <= $y_max; $y++ )
    {
        $curr_distances = array_sum ( $plane [ $x ][ $y ][ 'distances' ] );

        if ( $curr_distances < 10000 )
            $size++;
    }

echo 'Second Part: ' . $size . "\n";