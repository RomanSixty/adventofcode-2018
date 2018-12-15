<?php
$input = file_get_contents ( __DIR__ . '/input' );

$length = strlen ( $input );

$recipes = '37';

$elf_1 = 0;
$elf_2 = 1;

$part_1_pending = $part_2_pending = true;

$part_1 = $part_2 = '';

while ( $part_1_pending || $part_2_pending )
{
    $new_recipe = $recipes { $elf_1 } + $recipes { $elf_2 };

    if ( $new_recipe >= 10 )
        $recipes .= floor ( $new_recipe / 10 );

    $part_2_pending &= checkPart2 ( $part_2, $recipes, $input, $length );

    $recipes .= $new_recipe % 10;

    $part_2_pending &= checkPart2 ( $part_2, $recipes, $input, $length );

    $cur_length = strlen ( $recipes );

    $elf_1 = ( $elf_1 + $recipes { $elf_1 } + 1 ) % $cur_length;
    $elf_2 = ( $elf_2 + $recipes { $elf_2 } + 1 ) % $cur_length;

    if ( $part_1_pending && $cur_length == $input + 10 )
    {
        $part_1 = substr ( $recipes, -10 );

        $part_1_pending = false;
    }
}

function checkPart2 ( &$part_2, $recipes, $input, $length )
{
    $last = substr ( $recipes, -$length );

    if ( $last == $input )
    {
        $part_2 = strlen ( $recipes ) - $length;

        return false;
    }

    return true;
}

echo 'First Part: ' . $part_1 . "\n";
echo 'Second Part: ' . $part_2 . "\n";