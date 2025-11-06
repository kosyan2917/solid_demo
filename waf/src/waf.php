<?php

$sqli_regex = [
    "/(['|\"])+/s",
    "/(&|\|)+/s",
    "/(or|and)+/is",
    "/(union|select|from)+/is",
    "/\/\*\*\//",
    "/\s/"
];
function waf($input)
{
    global $sqli_regex;
    foreach ($sqli_regex as $pattern) 
    {
        if(preg_match($pattern,$input))
        {
            return true;
        }
        else
        {
            continue;
        }
    }
}





?>