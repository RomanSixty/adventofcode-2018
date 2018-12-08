<?php

$input = explode ( ' ', file_get_contents ( __DIR__ . '/input' ) );

/**
 * part 1: recursive function to build the tree of nodes
 *
 * @param array $current_node apply children to this node
 * @param array $entries get new data from here
 *
 * @return int sum of metadata entries of subtree
 */
function buildTree ( &$current_node, &$entries )
{
    if ( count ( $entries ) == 0 )
        return 0;

    $num_children = array_shift ( $entries );
    $num_meta     = array_shift ( $entries );

    $current_node = [
        'children' => [],
        'metadata' => []
    ];

    $sum = 0;

    for ( $children = 0; $children < $num_children; $children++ )
        $sum += buildTree ( $current_node [ 'children' ][], $entries );

    for ( $meta = 0; $meta < $num_meta; $meta++ )
        $current_node [ 'metadata' ][] = array_shift ( $entries );

    $sum += array_sum ( $current_node [ 'metadata' ] );

    return $sum;
}

/**
 * part 2: recursively calculate the value of the tree
 *
 * @param array $current_node working node
 *
 * @return int value of subtree
 */
function getValue ( &$current_node )
{
    if ( count ( $current_node [ 'children' ] ) == 0 )
        return array_sum ( $current_node [ 'metadata' ] );

    $value = 0;

    foreach ( $current_node [ 'metadata' ] as $possible_node )
        if ( isset ( $current_node [ 'children' ][ $possible_node - 1 ] ) )
            $value += getValue ( $current_node [ 'children' ][ $possible_node - 1 ] );

    return $value;
}

$tree = [];

echo 'First Part: ' . buildTree ( $tree [ 0 ], $input ) . "\n";
echo 'Second Part: ' . getValue ( $tree [ 0 ] ) . "\n";