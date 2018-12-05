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

    preg_match ( '~:(\d+)\] (wakes up|falls asleep|Guard #(\d+) begins shift)~', $logentry, $matches );

    if ( count ( $matches ) == 4 ) // new guard entry
    {
        $current_guard = $matches [ 3 ];
        continue;
    }
    else // sleep or wakeup time of current guard
    {
        if ( $matches [ 2 ] == 'falls asleep' )
        {
            $sleep_start = (int) $matches [ 1 ];
        }
        else // 'wakes up'
        {
            for ( $sleep_minute = $sleep_start; $sleep_minute < $matches [ 1 ]; $sleep_minute++ )
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
    // part 1

    $sum_minutes = array_sum ( $minutes );

    if ( $max_minutes < $sum_minutes )
    {
        $max_minutes      = $sum_minutes;
        $p1_chosen_guard  = $guard_id;

        $p1_chosen_minute = array_search ( max ( $minutes ), $minutes );
    }

    // part 2

    foreach ( $minutes as $m => $count )
    {
        if ( $count > $minute_slept_most )
        {
            $minute_slept_most = $count;
            $p2_chosen_minute  = $m;
            $p2_chosen_guard   = $guard_id;
        }
    }
}

echo 'First Part: ' . $p1_chosen_guard * $p1_chosen_minute . "\n";
echo 'Second Part: ' . $p2_chosen_guard * $p2_chosen_minute . "\n";