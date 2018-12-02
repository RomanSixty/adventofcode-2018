<?php

$input = file ( __DIR__ . '/input' );

$output = 0;

// part 1

foreach ( $input as $i )
	$output += $i;

echo 'First Part: ' . $output . "\n";

// part 2

$i      = 0;
$output = 0;
$freqs  = [ 0 => true ];
$size   = count ( $input );

while ( true )
{
	$output += $input [ $i % $size ];

	if ( isset ( $freqs [ $output ] ) )
		break;

	$freqs [ $output ] = true;

	$i++;
}

echo 'Second Part: ' . $output . "\n";
