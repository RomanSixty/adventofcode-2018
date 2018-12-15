<?php

$game = new Game ( file_get_contents ( __DIR__ . '/input' ) );

// a class... woah!
class Game
{
    var $players     = [];
    var $sim_players = [];

    var $graph = []; // each possibly empty cell => all the connected ones

    var $width = 0;
    var $plan = '';

    var $debug = false; // print maps for each step

    var $counter = 0;

    var $elf_strength = 3;

    function __construct ( $input )
    {
        $this -> width = strpos ( $input, "\n" );
        $this -> plan  = str_replace ( "\n", '', $input );

        // get all players
        for ( $i = 0; $i < strlen ( $this -> plan ); $i++ )
        {
            $cell = $this -> plan { $i };

            switch ( $cell )
            {
                case '.':
                    $this -> graph [ $i ] = $this -> getAdjacentCells ( $i );
                    continue 2;

                case 'G':
                case 'E':
                    $this -> plan { $i } = '.';
                    $this -> graph [ $i ] = $this -> getAdjacentCells ( $i );
                    break;

                default: continue 2;
            }

            // also collect my own adjacent cells
            // so that not every enemy has to calculate it on their own
            $this -> players[] = [
                'position'  => $i,
                'type'      => $cell,
                'hitpoints' => 200,
                'dead'      => false
            ];
        }
    }

    public function run ( $elf_strength = 3 )
    {
        $this -> sim_players  = $this -> players;
        $this -> elf_strength = $elf_strength;

        $this -> counter = 0;

        while ( true )
        {
            $this -> debug && $this -> printPlan();

            foreach ( $this -> sim_players as $player_id => $player )
            {
                if ( $this -> sim_players [ $player_id ][ 'dead' ] )
                    continue;

                $enemies = $this -> getEnemyPositions ( $player );

                if ( !$enemies )
                    break 2;

                // someone to hit close by
                if ( isset ( $enemies [ 'position' ] ) )
                    $this -> attack ( $enemies [ 'position' ] );
                else
                {
                    $paths = [];

                    foreach ( $enemies as $enemy )
                        if ( $new_path = $this -> pathToEnemy ( $player [ 'position' ], $enemy ) )
                            $paths[] = $new_path;

                    if ( count ( $paths ) )
                    {
                        usort ( $paths, function ( $a, $b ) {
                            if ( count ( $a ) == count ( $b ) )
                            {
                                if ( $a [ count ( $a ) - 1 ] == $b [ count ( $b ) - 1 ] )
                                    return ( $a [ 1 ] < $b [ 1 ] ) ? -1 : 1;
                                else
                                    return ( $a [ count ( $a ) - 1 ] < $b [ count ( $b ) - 1 ] ) ? -1 : 1;
                            }
                            else
                                return ( count ( $a ) < count ( $b ) ) ? -1 : 1;
                        });

                        $this -> sim_players [ $player_id ][ 'position' ] = $paths [ 0 ][ 1 ];

                        // attack after movement
                        $enemies = $this -> getEnemyPositions ( $this -> sim_players [ $player_id ] );

                        // someone to hit close by
                        if ( isset ( $enemies [ 'position' ] ) )
                            $this -> attack ( $enemies [ 'position' ] );
                    }
                }
            }

            if ( $this -> scrapeOffTheDead() && $elf_strength > 3 )
                return false;

            usort ( $this -> sim_players, function ( $a, $b ) {
                return ( $a [ 'position' ] < $b [ 'position' ] ) ? -1 : 1;
            });

            $this -> counter++;
        }

        $this -> scrapeOffTheDead();

        $this -> debug && $this -> printPlan();

        return true;
    }

    public function getLatestScore()
    {
        $score = 0;

        foreach ( $this -> sim_players as $p )
            $score += $p [ 'hitpoints' ];

        $score *= $this -> counter;

        return $score;
    }

    /**
     * get all directly adjacent non-wall cells
     * @param int $position cell
     * @return array of cells
     */
    private function getAdjacentCells ( $position )
    {
        $adjacent = [];

        if ( $this -> plan { $position - $this -> width } != '#' ) $adjacent[] = $position - $this -> width;
        if ( $this -> plan { $position -              1 } != '#' ) $adjacent[] = $position -              1;
        if ( $this -> plan { $position +              1 } != '#' ) $adjacent[] = $position +              1;
        if ( $this -> plan { $position + $this -> width } != '#' ) $adjacent[] = $position + $this -> width;

        return $adjacent;
    }

    /**
     * get positions of all enemies of the currently active player
     *
     * @param array $active_player
     *
     * @return array|bool list of enemies or false if none found
     */
    private function getEnemyPositions ( $active_player )
    {
        $enemy_positions = [];
        $close_enough    = [];

        $enemies_found = false;

        foreach ( $this -> sim_players as $player_id => $player )
        {
            if ( $player [ 'type' ] == $active_player [ 'type' ] || $player [ 'dead' ] )
                continue;

            $enemies_found = true;

            // first check, if we're already in striking distance
            if ( in_array ( $player [ 'position' ], $this -> graph [ $active_player [ 'position' ]] ) )
            {
                $close_enough[] = $player;
                continue;
            }

            $enemy_positions[] = $player [ 'position' ];
        }

        if ( !empty ( $close_enough ) )
        {
            // striking distance? return best target
            usort ( $close_enough, function ( $a, $b ) {
                if ( $a [ 'hitpoints' ] == $b [ 'hitpoints' ] )
                    return ( $a [ 'position' ] < $b [ 'position' ] ) ? -1 : 1;
                else
                    return ( $a [ 'hitpoints' ] < $b [ 'hitpoints' ] ) ? -1 : 1;
            });

            return $close_enough [ 0 ];
        }

        if ( $enemies_found )
            return $enemy_positions;

        return false;
    }

    /**
     * pathfinding
     *
     * @see https://en.wikipedia.org/wiki/Breadth-first_search
     * @see https://github.com/lextoumbourou/bfs-php
     *
     * @param int $start beginning cell of a path
     * @param int $end   ending cell of a path
     *
     * @return bool|array path or false if none found
     */
    private function pathToEnemy ( $start, $end )
    {
        $queue = new SplQueue();

        $queue -> enqueue ( [ $start ] );

        $checked [ $start ] = true;

        while ( $queue -> count() > 0 )
        {
            $path = $queue -> dequeue();

            $cell = $path [ count ( $path ) - 1 ];

            if ( $cell === $end )
                return $path;

            foreach ( $this -> graph [ $cell ] as $adjacent )
            {
                if ( !isset ( $checked [ $adjacent ] ) )
                {
                    $checked [ $adjacent ] = true;

                    foreach ( $this -> sim_players as $player )
                        if ( $player [ 'position' ] == $adjacent && !$player [ 'dead' ] && $player [ 'position' ] != $end )
                            continue 2;

                    $new_path   = $path;
                    $new_path[] = $adjacent;

                    $queue -> enqueue ( $new_path );
                }
            };
        }

        return false;
    }

    /**
     * hit me baby!
     * @param int $target player in this cell will be attacked
     */
    private function attack ( $target )
    {
        foreach ( $this -> sim_players as $player_id => $player )
            if ( $player [ 'position' ] == $target )
            {
                $this -> sim_players [ $player_id ][ 'hitpoints' ] -= $player [ 'type' ] == 'G' ? $this -> elf_strength : 3;

                if ( $this -> sim_players [ $player_id ][ 'hitpoints' ] <= 0 )
                    $this -> sim_players [ $player_id ][ 'dead' ] = true;

                break;
            }
    }

    /**
     * remove the dead from the battlefield
     * @return bool true if an elf was killed
     */
    private function scrapeOffTheDead()
    {
        $elf_killed = false;

        foreach ( $this -> sim_players as $player_id => $player )
            if ( $player [ 'dead' ] )
            {
                if ( $player [ 'type' ] == 'E' )
                    $elf_killed = true;

                unset ( $this -> sim_players [ $player_id ] );
            }

        return $elf_killed;
    }

    /**
     * for debugging purposes
     */
    private function printPlan()
    {
        echo $this -> counter . "\n";

        $rows = [];

        $round = $this -> plan;

        foreach ( $this -> sim_players as $player )
        {
            $round { $player [ 'position' ] } = $player [ 'type' ];

            $rows [ floor ( $player [ 'position' ] / $this -> width ) ][] = $player [ 'type' ] . '(' . $player [ 'hitpoints' ] . ')';
        }

        $map = str_split ( $round, $this -> width );

        foreach ( $rows as $r => $p )
            $map [ $r ] .= '   ' . implode ( ', ', $p );

        echo implode ( "\n", $map ) . "\n\n";
    }
}

$game -> run();

echo 'First Part: ' . $game -> getLatestScore() . "\n";

for ( $elf_strength = 4; $elf_strength < 200; $elf_strength++ )
    if ( $game -> run ( $elf_strength ) )
    {
        $part_2 = $game -> getLatestScore();
        break;
    }

echo 'Second Part: ' . $part_2 . "\n";