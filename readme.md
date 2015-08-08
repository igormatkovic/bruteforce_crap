# Block server attacks on non existing WP login paths

I for one, monitor my server logs all the time. And to be honest i get pissed when
i see 404 errors on blog/wp-login.php

Its fine if you try once and get a 404 error back... but why would you waste bandwidth and my
log space to try all the dam combos you have in your database!!

Its not like after 100 tries my 404's will change to something else.

To get rid of these i use the following solution.

# NOTICE
This is a solution for **NON WORDPRESS** sites. If you set this up on a wordpress site.
Then you will block a lot of people!


 - Create fake paths and log the misses.
 - Run Fail2Ban to look at the logs and ban the IP's with UFW

## Set up Fail2Ban to monitor our logs

Since im using UFW on my Ubuntu servers i will show that.. but you can use iptables if you like
This will tell Fail2Ban how to block or unblock a IP address with UFW
Create a file: /etc/fail2ban/action.d/ufw.conf and add the following contents
```sh
[Definition]
actionstart =
actionstop =
actioncheck =
actionban = ufw insert 1 deny from <ip> to any
actionunban = ufw delete deny from <ip> to any
```


Next we need to set up a jail
This will tell Fail2Ban where to look and what action to take.
Ban time is negative.. that means forever! OR you can set a value in seconds
Create a file: /etc/fail2ban/jail.d/catcher.conf
```bash
[catcher]
enabled = true
filter = catcher
action = ufw
logpath = /var/log/catcher.log
bantime = -1
maxretry = 3
```

And create a empty log and allow php to write there:
```bash
touch /var/log/catcher.log
chown www-data:www-data /var/log/catcher.log
```

We need also to set up our Filter.
Fail2Ban uses regex to read logs
Create a file: /etc/fail2ban/filter.d/catcher.conf

```bash
[INCLUDES]
before = common.conf
[Definition]
_daemon = catcher
failregex = ^<HOST> .* Attack attempt on .*
ignoreregex =
```

Fail2Ban is set up. we need to Set up our PHP script to write to the log file:

## PHP logging setup
First copy the [catcher.php](catcher.php) file from this repo to your server.
I would put it outside your main projects.
For example: /var/www/catcher.php

Edit the file log path to match your defined fail2ban jail log path
And modify the ASCII art i have added... (yes its a middle finger)

Also in that file there is a method to get the users IP address, you can google for other solutions
to get the real ip address and replace it with what ever you feel most comfortable with.



## Nginx Location Filter Set Up
 
Next we have to redirect all the fake locations to that file.

To make it easier to share this code between multiple sites we will create a include file  

Create a folder and a file **/etc/nginx/includes/wordpresscrap**  

add the following in there.
```bash
    # The usual suspects
    location ~ ^/(wp-admin|phpmyadmin|pma|webmin|myadmin) {
        fastcgi_pass unix:/var/run/php5-fpm.sock;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME /var/www/catcher.php;
        fastcgi_intercept_errors off;
    }

    # They look for wp-login in any random folder.. so block it all
    location ~* /wp-login.php {
        fastcgi_pass unix:/var/run/php5-fpm.sock;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME /var/www/catcher.php;
        fastcgi_intercept_errors off;
    }
```


Now just add the included file into your nginx sites conf.

```sh
server {
    listen 80;
    server_name mydomain;
    ...
    
    include includes/wordpresscrapp;
    
```


Restart fail2ban and nginx to take affect



```sh
service nginx restart && service fail2ban restart
```


## Testing

To test out does it all work. 
To thest his its better if you use a different server. Because if the test works you will block your self out!


``sh
curl -X POST -d "username=something" http://mydomain.com/wp-login.php
```

Since we allow only 3 tries, on the 4 try you will be blocked.

To check the status run:

```sh
ufw status
```

And to delete your test IP from UFW run:
```sh
ufw delete 1
```



