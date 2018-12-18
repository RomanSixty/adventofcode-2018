<?php

$input = file ( __DIR__ . '/input' );

$field = [];

foreach ( $input as $y => $line )
    $field [ $y ] = str_split ( trim ( $line ), 1 );

$fields_encountered = [];

$part_1 = 0;

for ( $time = 1; $time <= 1000000000; $time++ )
{
    $next_field = $field;

    foreach ( $field as $y => $line )
        foreach ( $line as $x => $cell )
        {
            $adjacent = getAdjacent ( $x, $y );

            switch ( $cell )
            {
                case '.':
                    $next_field [ $y ][ $x ] = $adjacent [ '|' ] >= 3 ? '|' : '.';
                    break;

                case '|':
                    $next_field [ $y ][ $x ] = $adjacent [ '#' ] >= 3 ? '#' : '|';
                    break;

                case '#':
                    $next_field [ $y ][ $x ] = ($adjacent [ '#' ] >= 1 && $adjacent [ '|' ] >= 1) ? '#' : '.';
                    break;
            }
        }

    $field = $next_field;

    if ( $time == 10 )
        $part_1 = getLumberValue();

    $field_id = serialize ( $field );

    if ( isset ( $fields_encountered [ $field_id ] ) )
    {
        $loop_start = $fields_encountered [ $field_id ];
        break;
    }

    $fields_encountered [ $field_id ] = $time;
}

$loop_length = count ( $fields_encountered ) - $loop_start + 1;

$remaining = ( 1000000000 - $loop_start ) % $loop_length;

$field = unserialize ( array_search ( $loop_start + $remaining, $fields_encountered ) );

echo 'First Part: ' . $part_1 . "\n";
echo 'Second Part: ' . getLumberValue() . "\n";

// for debugging purposes
function printField()
{
    global $field;

    foreach ( $field as $y => $line )
        echo implode ( '', $line ) . "\n";

    echo "\n\n";
}

function getAdjacent ( $x, $y )
{
    global $field;

    $adjacent = [ '.' => 0, '|' => 0, '#' => 0 ];

    if ( $y > 0 )
    {
        if ( $x > 0 )
            $adjacent [ $field [ $y - 1 ][ $x - 1 ]]++;

        $adjacent [ $field [ $y - 1 ][ $x ]]++;

        if ( $x < 49 )
            $adjacent [ $field [ $y - 1 ][ $x + 1 ]]++;
    }

    if ( $x > 0 )
        $adjacent [ $field [ $y ][ $x - 1 ]]++;

    if ( $x < 49 )
        $adjacent [ $field [ $y ][ $x + 1 ]]++;

    if ( $y < 49 )
    {
        if ( $x > 0 )
            $adjacent [ $field [ $y + 1 ][ $x - 1 ]]++;

        $adjacent [ $field [ $y + 1 ][ $x ]]++;

        if ( $x < 49 )
            $adjacent [ $field [ $y + 1 ][ $x + 1 ]]++;
    }

    return $adjacent;
}

function getLumberValue()
{
    global $field;

    $trees = $lumberyards = 0;

    foreach ( $field as $y => $line )
        foreach ( $line as $x => $cell )
        {
            if ( $field [ $y ][ $x ] == '#' )
                $lumberyards++;
            elseif ( $field [ $y ][ $x ] == '|' )
                $trees++;
        }

    return $trees * $lumberyards;
}