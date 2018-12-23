<?php

$input = file ( __DIR__ . '/input' );

$bots = [];

$sum = [ 'x' => 0, 'y' => 0, 'z' => 0 ];

$max_radius = 0;
$master_bot = null;

foreach ( $input as $line )
{
    $matches = [];

    preg_match ( '~pos=<(-?\d+),(-?\d+),(-?\d+)>, r=(\d+)~', $line, $matches );

    $bot = [
        'x' => intval ( $matches [ 1 ] ),
        'y' => intval ( $matches [ 2 ] ),
        'z' => intval ( $matches [ 3 ] ),
        'r' => intval ( $matches [ 4 ] )
    ];

    if ( $matches [ 4 ] > $max_radius )
    {
        $max_radius = $matches [ 4 ];
        $master_bot = $bot;
    }

    // for second part
    foreach ( [ 'x', 'y', 'z' ] as $key => $ordinate )
        $sum [ $ordinate ] += $matches [ $key + 1 ];

    $bots[] = $bot;
}

$in_range = 0;

foreach ( $bots as $bot )
    if ( isInRange ( $bot, $master_bot ) )
        $in_range++;

// the first impulse for the second part might be to just count bots for
// every cell in 3D space... however the space to consider is WAY too large
// so we just start out guesswork at the average center of bot space
// and try to close in on the final position using bisection
// @see https://en.wikipedia.org/wiki/Bisection_method

$cur_pos = [
    'x' => ceil ( $sum [ 'x' ] / count ( $bots )),
    'y' => ceil ( $sum [ 'y' ] / count ( $bots )),
    'z' => ceil ( $sum [ 'z' ] / count ( $bots ))
];

$start_step_width = pow ( 2, 22 ); // let's start with a high power of 2

$best_count = $second_pass = 0;

isBetter ( $cur_pos ); // initialize $best_count

$neg = 1;

// we need multiple passes, one for closing in to the center,
// one for moving away from it

while ( $second_pass < $best_count )
{
    $second_pass = $best_count;

    $step_width = $start_step_width;
    $best_count_before = 0;

    $neg = $neg == -1 ? 1 : -1;

    while ( $best_count_before < $best_count )
    {
        $best_count_before = $best_count;

        while ( $step_width > 0 )
        {
            closeIn ( $cur_pos, $step_width, $neg );
            $step_width = floor ( $step_width / 2 );

            //echo "$best_count bots @ <$cur_pos[x],$cur_pos[y],$cur_pos[z]> $step_width\n";
        }
    }
}

/**
 * bisection function trying to close in on the final position
 *
 * @param array $cur_pos [ x | y | z ]
 * @param int $step_width current step width
 * @param int $neg try closer to 0 (-1) oder farther away (1)?
 */
function closeIn ( &$cur_pos, $step_width, $neg = -1 )
{
    // every combination of directions to try
    $directions = [
        [ 'x' => 1, 'y' => 0, 'z' => 0],
        [ 'x' => 0, 'y' => 1, 'z' => 0],
        [ 'x' => 0, 'y' => 0, 'z' => 1],
        [ 'x' => 1, 'y' => 1, 'z' => 0],
        [ 'x' => 1, 'y' => 0, 'z' => 1],
        [ 'x' => 0, 'y' => 1, 'z' => 1],
        [ 'x' => 1, 'y' => 1, 'z' => 1],
    ];

    foreach ( $directions as $direction )
    {
        $test_pos = [];

        foreach ( array_keys ( $direction ) as $dir )
            $test_pos [ $dir ] = $step_width * $neg * $direction [ $dir ];

        // as long as we get better results, move the current position
        while ( isBetter ( [ 'x' => $cur_pos [ 'x' ] + $test_pos [ 'x' ], 'y' => $cur_pos [ 'y' ] + $test_pos [ 'y' ], 'z' => $cur_pos [ 'z' ] + $test_pos [ 'z' ]] ) )
            foreach ( array_keys ( $direction ) as $dir )
                $cur_pos [ $dir ] += $test_pos [ $dir ];
    }
}

/**
 * is the current position better in terms of bot ranges?
 * @param array $pos [ x | y | z ]
 * @return bool
 */
function isBetter ( $pos )
{
    global $best_count;

    $cur_count = countInRange ( $pos );

    $best_count = max ( $best_count, $cur_count );

    return $best_count == $cur_count;
}

/**
 * how many bots's ranges extend to $pos?
 * @param array $pos [ x | y | z ]
 * @return int bot count
 */
function countInRange ( $pos )
{
    global $bots;

    $in_range = 0;

    foreach ( $bots as $bot )
        if ( isInRange ( $pos, $bot ) )
            $in_range++;

    return $in_range;
}

/**
 * simple check if a $pos is in range of $bot
 *
 * @param array $pos [ x | y | z ]
 * @param array $bot [ x | y | z ]
 *
 * @return bool
 */
function isInRange ( $pos, $bot )
{
    return manhattanDistance ( $pos, $bot ) <= $bot [ 'r' ];
}

/**
 * calculate Manhattan distance of two positions $a and $b
 *
 * @param array $a [ x | y | z ]
 * @param array $b [ x | y | z ]
 *
 * @return int distance
 *
 * @see https://en.wikipedia.org/wiki/Taxicab_geometry
 */
function manhattanDistance ( $a, $b )
{
    return abs ( $a [ 'x' ] - $b [ 'x' ] ) + abs ( $a [ 'y' ] - $b [ 'y' ] ) + abs ( $a [ 'z' ] - $b [ 'z' ] );
}

echo 'First Part: ' . $in_range . "\n";
echo 'Second Part: ' . manhattanDistance ( $cur_pos, [ 'x' => 0, 'y' => 0, 'z' => 0 ] ) . "\n";