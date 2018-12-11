<?php

$input = file_get_contents ( __DIR__ . '/input' );

define ( 'GRIDSIZE', 300 );

$unit_grid = [];
$work_grid = [];

for ( $cell = 0; $cell < GRIDSIZE * GRIDSIZE; $cell++ )
    $unit_grid [ $cell ] = getFuel ( $cell % GRIDSIZE, floor ( $cell / GRIDSIZE ), $input );

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
    $cells_to_add = getAdditionalCells ( $square_size );

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

/**
 * calculate one cell's fuel value
 *
 * @param int $x
 * @param int $y
 * @param int $serial
 *
 * @return int
 */
function getFuel ( $x, $y, $serial )
{
    // I count from 0, this algorithm does not...
    $x++;
    $y++;

    return floor ( ( pow($x,2)*$y + 20*$x*$y + 100*$y + $x*$serial + 10*$serial ) / 100 ) % 10 - 5;
}

/**
 * which cell numbers (based on current cell) do we have to add with each iteration?
 * @param int $size
 * @return array list of cell numbers
 */
function getAdditionalCells ( $size = 3 )
{
    $coords = [];

    $coords[] = $size - 1;

    for ( $row = 1; $row < $size - 1; $row++ )
        $coords[] = GRIDSIZE * $row + $size - 1;

    for ( $col = 0; $col < $size; $col++ )
        $coords[] = GRIDSIZE * ( $size - 1 ) + $col;

    return $coords;
}

echo 'First Part: ' . ( $part_1 % GRIDSIZE + 1 ) . ',' . ( floor ( $part_1 / GRIDSIZE ) + 1 ) . "\n";
echo 'Second Part: ' . ( $max_cell % GRIDSIZE + 1 ) . ',' . ( floor ( $max_cell / GRIDSIZE ) + 1 ) . ',' . $max_size . "\n";