<?php

$inputs = explode ( "\n\n", file_get_contents ( __DIR__ . '/input' ) );

$groups = [];

$index = 0;

// immune system
foreach ( explode ( "\n", $inputs [ 0 ] ) as $immune )
    if ( false !== $data = parseUnits ( $immune, 'immune' ) )
        $groups [ 'immune' . ++$index ] = $data;

// infection
foreach ( explode ( "\n", $inputs [ 1 ] ) as $infection )
    if ( false !== $data = parseUnits ( $infection, 'infection' ) )
        $groups [ 'infection' . ++$index ] = $data;

function parseUnits ( $line, $type )
{
    $matches = [];

    if ( preg_match ( '~^(\d+) units each with (\d+) hit points (?:\((?:(immune|weak) to ([^;)]+)(?:; (?:(immune|weak) to ([^;)]+))?)?)?\)+ )?with an attack that does (\d+) ([a-z]+) damage at initiative (\d+)$~', $line, $matches ) )
    {
        $data = [
            'type'        => $type,
            'units'       => $matches [ 1 ],
            'hitpoints'   => $matches [ 2 ],
            'attack'      => $matches [ 7 ],
            'attack_type' => $matches [ 8 ],
            'initiative'  => $matches [ 9 ],
            'immune'      => [],
            'weak'        => []
        ];

        if ( !empty ( $matches [ 4 ] ) )
            $data [ $matches [ 3 ]] = explode ( ', ', $matches [ 4 ] );

        if ( !empty ( $matches [ 6 ] ) )
            $data [ $matches [ 5 ]] = explode ( ', ', $matches [ 6 ] );

        return $data;
    }

    return false;
}

function battle ( $groups )
{
    while ( fightingContinues ( $groups ) )
    {
        $before = serialize ( $groups );

        // get effective damage for each group
        foreach ( $groups as $key => $group )
        {
            $groups [ $key ][ 'effective_power' ] = $group [ 'units' ] * $group [ 'attack' ];

            $groups [ $key ][ 'attacks' ] = $groups [ $key ][ 'attacked' ] = false;
        }

        uasort ( $groups, function ( $a, $b ) {
            if ( $a [ 'effective_power' ] == $b [ 'effective_power' ] )
                return $a [ 'initiative' ] > $b [ 'initiative' ] ? -1 : 1;
            else
                return $a [ 'effective_power' ] > $b [ 'effective_power' ] ? -1 : 1;
        });

        // phase 1: select target

        foreach ( $groups as $groupkey => $group )
        {
            $enemies = [];

            foreach ( $groups as $key => &$other )
                if ( $group [ 'type' ] != $other [ 'type' ] && !$other [ 'attacked' ] && $other [ 'units' ] > 0 )
                {
                    if ( in_array ( $group [ 'attack_type' ], $other [ 'immune' ] ) )
                        continue;
                    elseif ( in_array ( $group [ 'attack_type' ], $other [ 'weak' ] ) )
                        $other [ 'damage_received' ] = 2 * $group [ 'effective_power' ];
                    else
                        $other [ 'damage_received' ] = $group [ 'effective_power' ];

                    $enemies[] = $key;
                }

            usort ( $enemies, function ( $a, $b ) use ( $groups ) {
                if ( $groups [ $a ][ 'damage_received' ] == $groups [ $b ][ 'damage_received' ] )
                {
                    if ( $groups [ $a ][ 'effective_power' ] == $groups [ $b ][ 'effective_power' ] )
                        return $groups [ $a ][ 'initiative' ] > $groups [ $b ][ 'initiative' ] ? -1 : 1;

                    return $groups [ $a ][ 'effective_power' ] > $groups [ $b ][ 'effective_power' ] ? -1 : 1;
                }
                return $groups [ $a ][ 'damage_received' ] > $groups [ $b ][ 'damage_received' ] ? -1 : 1;
            });

            if ( count ( $enemies ) )
            {
                $groups [ $enemies [ 0 ]][ 'attacked' ] = true;
                $groups [ $groupkey ][ 'attacks' ] = $enemies [ 0 ];
            }
        }

        // phase 2: fight

        uasort ( $groups, function ( $a, $b ) {
            return $a [ 'initiative' ] > $b [ 'initiative' ] ? -1 : 1;
        });

        foreach ( array_keys ( $groups ) as $key )
        {
            if ( !empty ( $groups [ $key ][ 'attacks' ] ) && $groups [ $key ][ 'units' ] > 0 )
            {
                $damage_dealt = $groups [ $key ][ 'units' ] * $groups [ $key ][ 'attack' ];

                if ( in_array ( $groups [ $key ][ 'attack_type' ], $groups [ $groups [ $key ][ 'attacks' ]][ 'weak' ] ) )
                    $damage_dealt *= 2;

                $kills = floor ( $damage_dealt / $groups [ $groups [ $key ][ 'attacks' ]][ 'hitpoints' ] );

                $groups [ $groups [ $key ][ 'attacks' ]][ 'units' ] = max ( 0, $groups [ $groups [ $key ][ 'attacks' ]][ 'units' ] - $kills );
            }
        }

        // check for stalemates
        if ( serialize ( $groups ) == $before )
            return 0;
    }

    $units_left = 0;

    foreach ( $groups as $group )
        if ( $group [ 'type' ] == 'immune' )
            $units_left += $group [ 'units' ];
        else
            $units_left -= $group [ 'units' ];

    return $units_left;
}

function fightingContinues ( $groups )
{
    $immune = $infection = false;

    foreach ( $groups as $group )
        if ( $group [ 'units' ] > 0 )
            ${$group [ 'type' ]} = true;

    return $immune && $infection;
}

echo 'First Part: ' . abs ( battle ( $groups ) ) . "\n";

function boostImmuneSystem ( &$groups, $value )
{
    foreach ( $groups as &$group )
        if ( $group [ 'type' ] == 'immune' )
            $group [ 'attack' ] += $value;
}

$step = pow ( 2, 7 );
boostImmuneSystem ( $groups, $step );
$best_result = 0;

do
{
    $step = floor ( $step / 2 );

    $units_left = battle ( $groups );

    if ( $units_left > 0 )
    {
        $best_result = $units_left;
        boostImmuneSystem ( $groups, -$step );
    }
    else
        boostImmuneSystem ( $groups, $step );
}
while ( $step > 0 );

echo 'Second Part: ' . $best_result . "\n";