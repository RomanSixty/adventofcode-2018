<?php

$input = file_get_contents ( __DIR__ . '/input' );

// first time a function is used in this advent of code
function reactPolymer ( $polymer )
{
    $search = str_split ( 'aAbBcCdDeEfFgGhHiIjJkKlLmMnNoOpPqQrRsStTuUvVwWxXyYzZZzYyXxWwVvUuTtSsRrQqPpOoNnMmLlKkJjIiHhGgFfEeDdCcBbAa', 2 );

    do
    {
        $lastLength = strlen ( $polymer );

        $polymer = str_replace ( $search, '', $polymer );
    }
    while ( strlen ( $polymer ) < $lastLength );

    return $lastLength;
}

$shortest = reactPolymer ( $input );

echo 'First Part: ' . $shortest . "\n";

foreach ( str_split ( 'abcdefghijklmnopqrstuvwxyz' ) as $unittype )
{
    $testPolymer = str_ireplace ( $unittype, '', $input );

    $reduces = reactPolymer ( $testPolymer );

    if ( $reduces < $shortest )
        $shortest = $reduces;
}

echo 'Second Part: ' . $shortest . "\n";