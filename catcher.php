<?php

/**
 * Simple PHP script to catch any unwanted tries.
 */


/** Set the Log Path */

$logPath = "/var/log/catcher.log";



function getIpAddress() {

    if (!empty($_SERVER["REMOTE_ADDR"])) {

        return $_SERVER["REMOTE_ADDR"];

    } elseif (!empty($_SERVER["HTTP_CLIENT_IP"])) {

        return $_SERVER["HTTP_CLIENT_IP"];

    } elseif (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {

        return $_SERVER["HTTP_X_FORWARDED_FOR"];
    }

    return false;

}

/**
 * Get the users IP address
 * A lot of articles online have the function to get the Real IP address.
 * So chose a method that suits you! (example: what do you trust more: REMOTE_ADDR or HTTP_X_FORWARDED_FOR or etc...
 * Before you use any of the tutorials out there.. just read this:
 * http://blog.ircmaxell.com/2012/11/anatomy-of-attack-how-i-hacked.html
 *
 * For a example.. i will just use the following..
 * Keep in mind that sometimes you may have a load-balancer, and if this script catches the LB's IP, nobody will access your site :)
 */

/** Only log Post since those are the attacks we track */


if($_SERVER['REQUEST_METHOD'] != 'GET') {

    if (!$userIP = getIpAddress()) {

        error_log($userIP . " [" . date('Y/m/d H:i:s') . "] [error] Cant figure out ip address " . var_export($_SERVER,
                TRUE) . " \n", 3, $logPath);

    } else {

        error_log($userIP . " [" . date('Y/m/d H:i:s') . "] Attack attempt on " . $_SERVER["PHP_SELF"] . "\n", 3,
            $logPath);
    }
}


header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
echo "404 Not Found <br/>";
echo "

<pre>
....................../´¯/)
....................,/¯../
.................../..../
............./´¯/'...'/´¯¯`·¸
........../'/.../..../......./¨¯\
........('(...´...´.... ¯~/'...')
.........\.................'...../
..........''...\.......... _.·´
............\..............(
..............\.............\...
</pre>

";

exit;


