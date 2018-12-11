<?php

$input = file_get_contents ( __DIR__ . '/input' );

define ( 'GRIDSIZE', 300 );

$unit_grid = [];
$work_grid = [];

for ( $cell = 0; $cell < GRIDSIZE * GRIDSIZE; $cell++ )
{
    // I count from 0, this algorithm does not...
    $x = $cell % GRIDSIZE + 1;
    $y = floor ( $cell / GRIDSIZE ) + 1;

    $unit_grid [ $cell ] = floor ( ( pow($x,2)*$y + 20*$x*$y + 100*$y + $x*$input + 10*$input ) / 100 ) % 10 - 5;
}

$part_1 = 0;

$max_value = max ( $unit_grid );
$max_size  = 1;
$max_cell  = 0;

$work_grid = $unit_grid;

// this may take a while... about 3½ minutes on my slow machine
// but it's still orders of magnitude faster than my company's PHP competition :)
// I'm pretty sure it can still be optimized though

for ( $square_size = 2; $square_size <= GRIDSIZE; $square_size++ )
{
    $cells_to_add = [ $square_size - 1 ];

    for ( $row = 1; $row < $square_size - 1; $row++ )
        $cells_to_add[] = GRIDSIZE * $row + $square_size - 1;

    for ( $col = 0; $col < $square_size; $col++ )
        $cells_to_add[] = GRIDSIZE * ( $square_size - 1 ) + $col;

    for ( $cell = 0; $cell < GRIDSIZE * GRIDSIZE; $cell++ )
    {
        // stop if our square wouldn't fit the grid anymore
        if ( $cell % GRIDSIZE > GRIDSIZE - $square_size || floor ( $cell / GRIDSIZE ) > GRIDSIZE - $square_size )
            continue;

        foreach ( $cells_to_add as $cta )
            $work_grid [ $cell ] += $unit_grid [ $cell + $cta ];
    }

    $value = max ( $work_grid );

    if ( $square_size == 3 )
        $part_1 = array_search ( $value, $work_grid );

    if ( $value > $max_value )
    {
        $max_value = $value;
        $max_size  = $square_size;
        $max_cell  = array_search ( $value, $work_grid );
    }
}

echo 'First Part: ' . ( $part_1 % GRIDSIZE + 1 ) . ',' . ( floor ( $part_1 / GRIDSIZE ) + 1 ) . "\n";
echo 'Second Part: ' . ( $max_cell % GRIDSIZE + 1 ) . ',' . ( floor ( $max_cell / GRIDSIZE ) + 1 ) . ',' . $max_size . "\n";