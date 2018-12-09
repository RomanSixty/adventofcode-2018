<?php

$input = file_get_contents ( __DIR__ . '/input' );

// NOTE:
// there's a PHP bug in its garbage collector that causes segfaults with
// too deeply nested objects like in part 2
// see https://bugs.php.net/bug.php?id=72411
// so fuck it, let's just disable garbage collection
gc_disable();

$matches = [];

preg_match ( '~(\d+) .* (\d+) ~', $input, $matches );

// first temptation might be to use arrays to play this game
// however this becomes exponentially slower the more marbles are involved
// this is especially evident in the second part
// hence: let's use a doubly linked list
// https://en.wikipedia.org/wiki/Doubly_linked_list

class CircleItem
{
    public $value;

    public $next;
    public $prev;

    function __construct ( $value, $prev = null, $next = null )
    {
        $this -> value = $value;

        if ( !empty ( $prev ) )
        {
            $this -> prev = $prev;
            $this -> next = $next;
        }
        else
        {
            $this -> prev  = $this;
            $this -> next  = $this;
        }
    }
}

$players = array_fill ( 0, $matches [ 1 ], 0 );

$num_marbles_1 = $matches [ 2 ];
$num_marbles_2 = $num_marbles_1 * 100;

// first marble
$current_marble = new CircleItem ( 0 );

$current_player = 0;

for ( $number = 1; $number <= $num_marbles_2; $number++ )
{
    if ( $number % 23 == 0 )
    {
        $current_player = $current_player % count ( $players );

        $players [ $current_player ] += $number;

        $marble_to_remove = $current_marble -> prev -> prev -> prev -> prev -> prev -> prev -> prev;

        $players [ $current_player ] += $marble_to_remove -> value;

        // remove marble from chain
        $marble_to_remove -> next -> prev = $marble_to_remove -> prev;
        $marble_to_remove -> prev -> next = $marble_to_remove -> next;

        $current_marble = $marble_to_remove -> next;
    }
    else
    {
        $insert_after = $current_marble -> next;

        $current_marble = new CircleItem ( $number, $insert_after, $insert_after -> next );

        $insert_after -> next -> prev = $current_marble;
        $insert_after -> next = $current_marble;
    }

    if ( $number == $num_marbles_1 )
        $part_1 = max ( $players );

    $current_player++;
}

echo 'First Part: ' . $part_1 . "\n";
echo 'Second Part: ' . max ( $players ) . "\n";