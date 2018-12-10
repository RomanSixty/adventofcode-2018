<?php

$input = file ( __DIR__ . '/input' );

$matches = [];
$points  = [];

foreach ( $input as $line )
{
    preg_match ( '~position=< *([0-9-]+), *([0-9-]+)> velocity=< *([0-9-]+), *([0-9-]+)>~', $line, $matches );

    $points[] = [
        'pos_x' => intval ( $matches [ 1 ] ),
        'pos_y' => intval ( $matches [ 2 ] ),
        'vel_x' => intval ( $matches [ 3 ] ),
        'vel_y' => intval ( $matches [ 4 ] )
    ];
}

/**
 * get the area of the rectangle including all current points
 * @param array $points
 * @return int area in square units
 */
function getArea ( $points )
{
    extract ( getBounds ( $points ) );

    return ( $x_max - $x_min ) * ( $y_max - $y_min );
}

/**
 * get the outermost limits of the current situation
 * @param array $points
 * @return array min and max of x and y
 */
function getBounds ( $points )
{
    $x_min = 1000;
    $y_min = 1000;
    $x_max = -1000;
    $y_max = -1000;

    foreach ( $points as $point )
    {
        $x_min = min ( $point [ 'pos_x' ], $x_min );
        $y_min = min ( $point [ 'pos_y' ], $y_min );

        $x_max = max ( $point [ 'pos_x' ], $x_max );
        $y_max = max ( $point [ 'pos_y' ], $y_max );
    }

    return compact ( 'x_min', 'y_min', 'x_max', 'y_max' );
}

$area_latest = getArea ( $points );

$seconds = 0;

// let's iterate over the stuff and check the bounds of the plane where all points fit
// if they are all closest to each other it may be an interesting time to watch

while ( true )
{
    $new_points = $points;

    foreach ( $new_points as &$point )
    {
        $point [ 'pos_x' ] += $point [ 'vel_x' ];
        $point [ 'pos_y' ] += $point [ 'vel_y' ];
    }

    if ( getArea ( $new_points ) < $area_latest )
    {
        $points = $new_points;
        $area_latest = getArea ( $points );

        $seconds++;
    }
    else
        break;
}


echo "First Part: \n\n";

extract ( getBounds ( $points ) );

for ( $y = $y_min; $y <= $y_max; $y++ )
{
    for ( $x = $x_min; $x <= $x_max; $x++ )
    {
        $c = ' ';

        foreach ( $points as $point )
            if ( $point [ 'pos_x' ] == $x && $point [ 'pos_y' ] == $y )
            {
                $c = '#';
                break;
            }

        echo $c;
    }

    echo "\n";
}

echo "\n\n";

echo 'Second Part: ' . $seconds . "\n";