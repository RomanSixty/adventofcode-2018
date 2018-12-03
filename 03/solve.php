<?php

$input = file ( __DIR__ . '/input' );

// just for code readability

define ( 'ID',     1 );
define ( 'LEFT',   2 );
define ( 'TOP',    3 );
define ( 'WIDTH',  4 );
define ( 'HEIGHT', 5 );

// prepare claims data structure, we need that for both parts

$claims = [];

foreach ( $input as $claim )
{
    $claim_parts = [];

    preg_match ( '~^#(\d+) @ (\d+),(\d+): (\d+)x(\d+)$~', $claim, $claim_parts );

    $claims[] = $claim_parts;
}

// part 1

$fabric = [];

$overlap = 0;

foreach ( $claims as $claim )
{
    for ( $w = 0; $w < $claim [ WIDTH ]; $w++ )
        for ( $h = 0; $h < $claim [ HEIGHT ]; $h++ )
        {
            $x = $claim [ LEFT ] + $w;
            $y = $claim [ TOP  ] + $h;

            if ( isset ( $fabric [ $x ][ $y ] ) )
            {
                // using 0 as initial value here makes part 2 a little simpler
                // because !empty() will suffice to check if there are collisions

                if ( $fabric [ $x ][ $y ] == 0 )
                    $overlap++;

                $fabric [ $x ][ $y ]++;
            }
            else
                $fabric [ $x ][ $y ] = 0;
        }
}

echo 'First Part: ' . $overlap . "\n";

// part 2

$best_claim = null;

foreach ( $claims as $claim )
{
    $did_overlap = false;

    for ( $w = 0; $w < $claim [ WIDTH ]; $w++ )
        for ( $h = 0; $h < $claim [ HEIGHT ]; $h++ )
            if ( !empty ( $fabric [ $claim [ LEFT ] + $w ][ $claim [ TOP ] + $h ] ) )
                $did_overlap = true;

    if ( !$did_overlap )
    {
	    $best_claim = $claim [ ID ];
	    break;
    }
}

echo 'Second Part: #' . $best_claim . "\n";
