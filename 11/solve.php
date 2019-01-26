<?php

$input = file_get_contents ( __DIR__ . '/input' );

define ( 'GRIDSIZE', 300 );

// grid is offset by 1 in each direction to save some > 0 comparisons
$grid = [];

$grid [ 0 ] = array_fill ( 0, GRIDSIZE + 1, 0 );

for ( $y = 1; $y <= GRIDSIZE; $y++ )
{
    $grid [ $y ][ 0 ] = 0;

    for ( $x = 1; $x <= GRIDSIZE; $x++ )
    {
        $grid [ $y ][ $x ] = floor ( ( pow($x,2)*$y + 20*$x*$y + 100*$y + $x*$input + 10*$input ) / 100 ) % 10 - 5;

        // @see https://en.wikipedia.org/wiki/Summed-area_table

        $grid [ $y ][ $x ] += $grid [ $y - 1 ][ $x ];
        $grid [ $y ][ $x ] += $grid [ $y ][ $x - 1 ];
        $grid [ $y ][ $x ] -= $grid [ $y - 1 ][ $x - 1 ];
    }
}

$part_1 = '0,0';

$max_value = 0;
$max_size  = 1;
$max_cell  = 0;

for ( $square_size = 2; $square_size <= GRIDSIZE; $square_size++ )
{
    $largest_v = $largest_x = $largest_y = 0;

    for ( $y = 1; $y <= GRIDSIZE; $y++ )
        for ( $x = 1; $x <= GRIDSIZE; $x++ )
        {
            // stop if our square wouldn't fit the grid anymore
            if ( $y >= GRIDSIZE - $square_size || $x >= GRIDSIZE - $square_size )
                continue;

            $value =  $grid [ $y + $square_size - 1 ][ $x + $square_size - 1 ];
            $value -= $grid [ $y - 1 ][ $x + $square_size - 1 ];
            $value -= $grid [ $y + $square_size - 1 ][ $x - 1 ];
            $value += $grid [ $y - 1 ][ $x - 1 ];

            if ( $value > $largest_v )
            {
                $largest_v = $value;
                $largest_x = $x;
                $largest_y = $y;
            }
        }

    if ( $square_size == 3 )
        $part_1 = $largest_x . ',' . $largest_y;

    if ( $largest_v > $max_value )
    {
        $max_value = $largest_v;
        $max_size  = $square_size;
        $max_cell  = $largest_x . ',' . $largest_y;
    }
}

echo 'First Part: ' . $part_1 . "\n";
echo 'Second Part: ' . $max_cell . ',' . $max_size . "\n";