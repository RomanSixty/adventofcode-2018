<?php

$input = file_get_contents ( __DIR__ . '/input' );

$matches = [];

preg_match_all ( '~(x|y)=(\d+), (x|y)=(\d+)\.\.(\d+)~ms', $input, $matches, PREG_SET_ORDER );

$plan = [];

$min = [ 'x' => 500, 'y' => 1000 ];
$max = [ 'x' =>   0, 'y' =>    0 ];

foreach ( $matches as $match )
{
    ${$match [ 1 ]} = intval ( $match [ 2 ] );

    for ( ${$match [ 3 ]} = $match [ 4 ]; ${$match [ 3 ]} <= $match [ 5 ]; ${$match [ 3 ]}++ )
        $plan [ $y ][ $x ] = '#';

    $min [ $match [ 1 ]] = min ( $min [ $match [ 1 ]], intval ( $match [ 2 ] ) );
    $max [ $match [ 1 ]] = max ( $max [ $match [ 1 ]], intval ( $match [ 2 ] ) );

    $min [ $match [ 3 ]] = min ( $min [ $match [ 3 ]], intval ( $match [ 4 ] ) );
    $max [ $match [ 3 ]] = max ( $max [ $match [ 3 ]], intval ( $match [ 5 ] ) );
}

for ( $y = $min [ 'y' ]; $y <= $max [ 'y' ]; $y++ )
    for ( $x = $min [ 'x' ]; $x <= $max [ 'x' ]; $x++ )
        if ( empty ( $plan [ $y ][ $x ] ) )
            $plan [ $y ][ $x ] = '.';

$total    = 0;
$retained = 0;

trickle ( 500, $min [ 'y' ] );

/**
 * water trickling vertically
 * @param int $x source of trickling
 * @param int $y source of trickling
 */
function trickle ( $x, $y )
{
    global $plan, $max, $total;

    while ( $y <= $max [ 'y' ] && $plan [ $y ][ $x ] == '.' )
    {
        $total++;

        $plan [ $y ][ $x ] = '|';

        $y++;
    }

    if ( $y > $max [ 'y' ] || $plan [ $y ][ $x ] == '|' || $plan [ $y ][ $x ] == 'o' )
        return;

    flow ( $x, $y );
}

/**
 * water flowing left and right on a surface
 * @param int $x source of flow
 * @param int $y source of flow
 */
function flow ( $x, $y )
{
    global $plan, $min, $max, $total;

    $overflow = false;

    $level = [];

    while ( !$overflow && $y <= $max [ 'y' ] )
    {
        $y--;

        $level = [];

        if ( $plan [ $y ][ $x ] == '.' )
        {
            $plan [ $y ][ $x ] = '~';
            $level[] = $x;
            $total++;
        }

        for ( $i = $x + 1; $i <= $max [ 'x' ]; $i++ )
            if ( !spread ( $overflow, $level, $i, $y ) )
                break;

        for ( $i = $x - 1; $i >= $min [ 'x' ]; $i-- )
            if ( !spread ( $overflow, $level, $i, $y ) )
                break;
    }

    foreach ( $level as $l )
        $plan [ $y ][ $l ] = 'o';
}

/**
 * everything has to be checked for both flow directions
 *
 * @param bool  $overflow are we flowing over an edge?
 * @param array $level    collect all water on this level to mark it when overflown
 * @param int $x          coordinate of current flow state
 * @param int $y          coordinate of current flow state
 *
 * @return bool           false if we need to break the flow (wall or edge)
 */
function spread ( &$overflow, &$level, $x, $y )
{
    global $plan, $total;

    if ( $plan [ $y ][ $x ] == '#' )
        return false;

    if ( $plan [ $y ][ $x ] == '|' )
        return true;

    if ( $plan [ $y ][ $x ] == '.' )
    {
        $total++;

        $plan [ $y ][ $x ] = '~';
        $level[] = $x;
    }

    if ( $plan [ $y + 1 ][ $x ] == '.' )
    {
        trickle ( $x, $y + 1 );
        $overflow = true;
        return false;
    }

    return true;
}

// this is by far the most unsatisfying solution yet...
// mark all trickle and overflows as retained when certain
// conditions are met
for ( $y = $min [ 'y' ]; $y <= $max [ 'y' ]; $y++ )
    for ( $x = $min [ 'x' ]; $x <= $max [ 'x' ]; $x++ )
    {
        if ( $plan [ $y ][ $x ] == '|' || $plan [ $y ][ $x ] == 'o' )
        {
            if ( $x - 1 >= $min [ 'x' ] && $x + 1 <= $max [ 'x' ] )
            {
                if ( $plan [ $y ][ $x + 1 ] == '~' || $plan [ $y ][ $x - 1 ] == '~' )
                    $plan [ $y ][ $x ] = '~';
                else
                    $plan [ $y ][ $x ] = '.';
            }
        }

        if ( $plan [ $y ][ $x ] == '~' && $plan [ $y + 1 ][ $x ] == '|' )
            $plan [ $y + 1 ][ $x ] = '~';
    }

for ( $y = $min [ 'y' ]; $y <= $max [ 'y' ]; $y++ )
    for ( $x = $min [ 'x' ]; $x <= $max [ 'x' ]; $x++ )
        if ( $plan [ $y ][ $x ] == '~' )
            $retained++;

echo 'First Part: ' . $total . "\n";
echo 'Second Part: ' . $retained . "\n";

// for debugging purposes
// # : wall
// | : trickle direction
// ~ : flow direction
// o : overflowing water
function printPlan()
{
    global $plan, $min, $max;

    for ( $y = $min [ 'y' ]; $y <= $max [ 'y' ]; $y++ )
    {
        for ( $x = $min [ 'x' ]; $x <= $max [ 'x' ]; $x++ )
            echo $plan [ $y ][ $x ];

        echo "\n";
    }

    echo "\n\n";
}