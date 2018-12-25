<?php

// this function may be used to print the cave for
// debug reasons

function printCave ( $hilight = [ 0, 0 ] )
{
    global $cave, $target;

    foreach ( $cave as $y => $row )
    {
        foreach ( $row as $x => $cell )
        {
            switch ( $cell [ 'type' ] )
            {
                case 0: $char = '.'; break;
                case 1: $char = '='; break;
                case 2: $char = '|'; break;
            }

            if ( $x == 0 && $y == 0 )
                $char = 'M';

            if ( $x == $target [ 0 ] && $y == $target [ 1 ] )
                $char = 'T';

            if ( $x == $hilight [ 0 ] && $y == $hilight [ 1 ] )
                echo "\033[0;31m" . $char . "\033[0m";
            elseif ( isset ( $cell [ 'distance' ] ) )
                echo "\033[0;100m" . $char . "\033[0m";
            else
                echo $char;
        }

        echo "\n";
    }
}