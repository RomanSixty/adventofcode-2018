<?php

$input = file ( __DIR__ . '/input' );

$current_guard = null;
$sleep_start   = false;
$guards        = [];

sort ( $input );

// parse guard log into a multidimensional array
// guard_id => minute after midnight => number of times asleep

foreach ( $input as $logentry )
{
    $matches = [];

    preg_match ( '~(\d+):(\d+)\] (wakes up|falls asleep|Guard #(\d+) begins shift)~', $logentry, $matches );

    // new guard entry
    if ( count ( $matches ) == 5 )
    {
        $current_guard = $matches [ 4 ];
        continue;
    }

    // sleep or wakeup time of current guard
    else
    {
        if ( $matches [ 3 ] == 'falls asleep' )
        {
            $sleep_start = (int) $matches [ 2 ];
        }
        else // 'wakes up'
        {
            for ( $sleep_minute = $sleep_start; $sleep_minute < $matches [ 2 ]; $sleep_minute++ )
            {
                if ( !isset ( $guards [ $current_guard ][ $sleep_minute ] ) )
                    $guards [ $current_guard ][ $sleep_minute ] = 0;

                $guards [ $current_guard ][ $sleep_minute ]++;
            }

            $sleep_start = false;
        }
    }
}

// this time we solve both parts in the same loop

$max_minutes       = 0; // max minutes a single guard was asleep
$minute_slept_most = 0; // which minute was slept the most

$p1_chosen_guard  = null;
$p1_chosen_minute = null;
$p2_chosen_guard  = null;
$p2_chosen_minute = null;

foreach ( $guards as $guard_id => $minutes )
{
    // part 2
    // pretty straightforward

    foreach ( $minutes as $m => $count )
    {
        if ( $count > $minute_slept_most )
        {
            $minute_slept_most = $count;
            $p2_chosen_minute  = $m;
            $p2_chosen_guard   = $guard_id;
        }
    }

    // part 1
    // use PHP's array functions to our advantage

    $sum_minutes = array_sum ( $minutes );

    if ( $max_minutes < $sum_minutes )
    {
        $max_minutes      = $sum_minutes;
        $p1_chosen_guard  = $guard_id;

        // sort array with the most slept minute first and return that array key
        // that way we don't need to iterate over every entry ourselves

        arsort ( $minutes );

        $p1_chosen_minute = key ( $minutes );
    }
}

echo 'First Part: ' . $p1_chosen_guard * $p1_chosen_minute . "\n";
echo 'Second Part: ' . $p2_chosen_guard * $p2_chosen_minute . "\n";