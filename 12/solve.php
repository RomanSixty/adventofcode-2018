<?php

$input = file ( __DIR__ . '/input' );

$max_generations = 50000000000;

$state = trim ( str_replace ( 'initial state: ', '', array_shift ( $input ) ) );

array_shift ( $input ); // empty line

$rules = [];

foreach ( $input as $line )
{
    list ( $key, $val ) = explode ( ' => ', trim ( $line ) );

    $rules [ $key ] = $val;
}

$zeropot = 0;

$state_cache [ $state ] = $zeropot;

for ( $generation = 1; $generation <= $max_generations; $generation++ )
{
    $new_state = '..';

    // padding
    $state = '....' . $state . '....';
    $zeropot += 4;

    for ( $i = 2; $i < strlen ( $state ) - 2; $i++ )
    {
        $section = substr ( $state, $i-2, 5 );
        $new_state .= $rules [ $section ];
    }

    // trimming
    while ( $new_state{0} == '.' )
    {
        $new_state = substr ( $new_state, 1 );
        $zeropot--;
    }
    $new_state = preg_replace ( '~\.+$~', '', $new_state );

    $state = $new_state;

    if ( $generation == 20 )
        $part_1 = getSum ( $state, $zeropot );

    // we already encountered this state
    if ( isset ( $state_cache [ $state ] ) )
        break;

    $state_cache [ $state ] = $zeropot;
}

// remove all cache entries before our loop started
foreach ( array_keys ( $state_cache ) as $key )
{
    if ( $key == $state )
        break;

    unset ( $state_cache [ $key ] );
}

$remaining_generations = floor ( ( $max_generations - $generation ) / count ( $state_cache ) );
$zeropot += $remaining_generations * ( $zeropot - $state_cache [ $state ] );

// this should work for longer loops, although my own had only length 1
$rest = ( $max_generations - $remaining_generations ) % count ( $state_cache );
for ( $i = 0; $i < $rest; $i++ )
    $zeropot += array_shift ( $state_cache );

$part_2 = getSum ( $state, $zeropot );

function getSum ( $state, $zeropot )
{
    $sum = 0;

    for ( $pos = 0; $pos < strlen ( $state ); $pos++ )
        if ( $state { $pos } == '#' )
            $sum += $pos - $zeropot;

    return $sum;
}

echo 'First Part: ' . $part_1 . "\n";
echo 'Second Part: ' . $part_2 . "\n";