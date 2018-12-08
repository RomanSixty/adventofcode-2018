<?php

$input = file ( __DIR__ . '/input' );

$letters      = [];
$dependencies = [];

// let's break our brain and build a recursive
// array structure using references...

foreach ( $input as $line )
{
    $matches = [];

    preg_match ( '~(.) must be finished before step (.)~', $line, $matches );

    if ( !isset ( $letters [ $matches [ 1 ]] ) )
        $letters [ $matches [ 1 ]] = [];

    if ( isset ( $letters [ $matches [ 2 ]] ) )
        $letters [ $matches [ 1 ]][ $matches [ 2 ]] =& $letters [ $matches [ 2 ]];
    else
    {
        $letters [ $matches [ 2 ]] = [];
        $letters [ $matches [ 1 ]][ $matches [ 2 ]] =& $letters [ $matches [ 2 ]];
    }

    // collect number of dependencies to find the right order later

    if ( isset ( $dependencies [ $matches [ 2 ]] ) )
        $dependencies [ $matches [ 2 ]]++;
    else
        $dependencies [ $matches [ 2 ]] = 1;

    if ( !isset ( $dependencies [ $matches [ 1 ]] ) )
        $dependencies [ $matches [ 1 ]] = 0;
}

// part 1

$chain = [];
$stepsToDo = [ array_search ( 0, $dependencies ) ];
$dependencies2 = $dependencies; // we need a copy for the second part

do
{
    $stepsToDo = array_unique ( $stepsToDo );

    sort ( $stepsToDo );

    foreach ( $stepsToDo as $key => $letter )
    {
        // no more dependencies
        if ( empty ( $dependencies [ $letter ] ) )
        {
            $chain[] = $letter;

            unset ( $stepsToDo [ $key ] );

            $children = array_keys ( $letters [ $letter ] );

            foreach ( $children as $child )
                $dependencies [ $child ]--;

            $stepsToDo = array_merge ( $stepsToDo, $children );

            break;
        }
    }
}
while ( count ( $stepsToDo ) );

// part 2

$second = -1; // somebody is already working in the zeroeth second
$stepsToDo = [ array_search ( 0, $dependencies2 ) ];

$max_workers = 5;
$workers = [];

do
{
    // tic toc

    $second++;

    foreach ( $workers as $letter => &$timer )
    {
        $timer--;

        if ( $timer <= 0 )
        {
            $children = array_keys ( $letters [ $letter ] );

            foreach ( $children as $child )
                $dependencies2 [ $child ]--;

            $stepsToDo = array_merge ( $stepsToDo, $children );

            unset ( $workers [ $letter ] );
        }
    };

    if ( count ( $stepsToDo ) )
    {
        $stepsToDo = array_unique ( $stepsToDo );

        sort ( $stepsToDo );

        foreach ( $stepsToDo as $key => $letter )
        {
            // no more dependencies
            if ( empty ( $dependencies2 [ $letter ] ) )
            {
                $timeNeeded = 60 + ord ( $letter ) - 64; // A = 64

                // get a free worker
                if ( count ( $workers ) < $max_workers )
                {
                    $workers [ $letter ] = $timeNeeded;
                    unset ( $stepsToDo [ $key ] );
                }
            }
        }
    }
}
while ( count ( $stepsToDo ) || count ( $workers ) );

echo 'First Part: ' . implode ( '', $chain ) . "\n";
echo 'Second Part: ' . $second . "\n";