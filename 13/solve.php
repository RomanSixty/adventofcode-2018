<?php

$input = file_get_contents ( __DIR__ . '/input' );
$width = strpos ( $input, "\n" );
$plan  = str_replace ( "\n", '', $input );

$directions = [
    '^' => -$width,
    '>' => 1,
    'v' => $width,
    '<' => -1
];

$carts = [];

// get all the cart positions and directions and clean up plan
for ( $i = 0; $i < strlen ( $plan ); $i++ )
{
    $cell = $plan{$i};

    switch ( $cell )
    {
        case '^':
        case 'v': $plan{$i} = '|'; break;
        case '>':
        case '<': $plan{$i} = '-'; break;
        default: continue 2;
    }

    $carts[] = [
        'position'  => $i,
        'direction' => $cell,
        'lastturn'  => 0
    ];
}

$part_1 = null;

while ( count ( $carts ) > 1 )
{
    $killed_carts = [];

    foreach ( $carts as $cart_id => &$cart )
    {
        if ( isset ( $killed_carts [ $cart_id ] ) )
            continue;

        $cart [ 'position' ] += $directions [ $cart [ 'direction' ]];

        // collision check
        foreach ( $carts as $other_id => $other_cart )
        {
            if ( $cart [ 'position' ] != $other_cart [ 'position' ] || $other_id == $cart_id || isset ( $killed_carts [ $other_id ] ) )
                continue;

            if ( $part_1 === null )
                $part_1 = $cart [ 'position' ];

            $killed_carts [ $other_id ] = true;
            $killed_carts [ $cart_id  ] = true;

            continue 2;
        }

        switch ( $plan [ $cart [ 'position' ]] )
        {
            case '/':
                switch ( $cart [ 'direction' ] )
                {
                    case '^': $cart [ 'direction' ] = '>'; break;
                    case '>': $cart [ 'direction' ] = '^'; break;
                    case 'v': $cart [ 'direction' ] = '<'; break;
                    case '<': $cart [ 'direction' ] = 'v'; break;
                }
                break;

            case '\\':
                switch ( $cart [ 'direction' ] )
                {
                    case '^': $cart [ 'direction' ] = '<'; break;
                    case '>': $cart [ 'direction' ] = 'v'; break;
                    case 'v': $cart [ 'direction' ] = '>'; break;
                    case '<': $cart [ 'direction' ] = '^'; break;
                }
                break;

            case '+':
                switch ( $cart [ 'direction' ] . ( $cart [ 'lastturn' ] % 3 ) )
                {
                    case '^0':
                    case 'v2': $cart [ 'direction' ] = '<'; break;
                    case '^2':
                    case 'v0': $cart [ 'direction' ] = '>'; break;
                    case '>0':
                    case '<2': $cart [ 'direction' ] = '^'; break;
                    case '>2':
                    case '<0': $cart [ 'direction' ] = 'v'; break;
                }

                $cart [ 'lastturn' ]++;

                break;
        }
    }

    foreach ( $killed_carts as $cart_id => $dummy )
        unset ( $carts [ $cart_id ] );

    // orders of carts change when they move around...
    usort ( $carts, function ( $a, $b ) {
        return ( $a [ 'position' ] < $b [ 'position' ] ) ? -1 : 1;
    });
}

$part_2 = $carts [ 0 ][ 'position' ];

echo 'First Part: ' . ( $part_1 % $width ) . ',' . floor ($part_1 / $width ) . "\n";
echo 'Second Part: ' . ( $part_2 % $width ) . ',' . floor ($part_2 / $width ) . "\n";

/**
 * not needed, but for reference
 */
function printPlan()
{
    global $plan, $carts, $width;

    $step = $plan;

    foreach ( $carts as $cart )
        $step { $cart [ 'position' ] } = $cart [ 'direction' ];

    echo implode ( "\n", str_split ( $step, $width ) ) . "\n\n";
}