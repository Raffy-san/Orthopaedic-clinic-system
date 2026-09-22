<?php 
function elapsedTime($fromDateTime)
{
    $start = strtotime($fromDateTime);
    $now = time();
    $diff = $now - $start;

    if ($diff < 0) {
        return 'Not started';
    }

    $minutes = floor($diff / 60);
    $hours = floor($minutes / 60);
    $minutes = $minutes % 60;

    if ($hours > 0) {
        return $hours . 'h ' . $minutes . 'm';
    }
    return $minutes . 'm';
}