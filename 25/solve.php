<?php

$inputs = file ( __DIR__ . '/input' );

$stars = $constellations = [];

foreach ( $inputs as $line )
    $stars[] = explode ( ',', trim ( $line ) );

$constellations[][] = array_pop ( $stars );

while ( $star = array_pop ( $stars ) )
{
    $constellation_found = false;

    foreach ( $constellations as $key => $constellation )
        foreach ( $constellation as $other_star )
            if ( manhattanDistance ( $star, $other_star ) <= 3 )
            {
                if ( $constellation_found !== false )
                {
                    $constellations [ $constellation_found ] = array_merge ( $constellations [ $key ], $constellations [ $constellation_found ] );
                    unset ( $constellations [ $key ] );
                }
                else
                {
                    $constellations [ $key ][] = $star;

                    $constellation_found = $key;
                }

                continue 2;
            }

    if ( $constellation_found === false )
        $constellations[][] = $star;
}

function manhattanDistance ( $a, $b )
{
    return abs ( $a [ 0 ] - $b [ 0 ] ) + abs ( $a [ 1 ] - $b [ 1 ] ) + abs ( $a [ 2 ] - $b [ 2 ] ) + abs ( $a [ 3 ] - $b [ 3 ] );
}

echo 'First Part: ' . count ( $constellations ) . "\n";
echo "Second Part: not available\n";