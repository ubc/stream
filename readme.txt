=== Stream ===
Contributors: enej, devindra, ctlt-dev, ubcdev
Tags: node, js, nodejs
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adds a framework for integrating wordpress plugins with Node JS.

== Description ==



== Installation ==

1. Extract the zip file into wp-content/plugins/ in your WordPress installation.
2. Go to plugins page to activate.
3. Make sure you have nodejs installed on your server. http://nodejs.org/download/
4. Go to the directory, plugins/stream/js-app 
5. In command line, run "node app.js"
6. Update the stream setting found in Settings -> Stream

Would recommend running the service using something like forever https://github.com/nodejitsu/forever

Info here: http://blog.nodejitsu.com/keep-a-nodejs-server-up-with-forever

OR 

Note: If you have upstart (http://upstart.ubuntu.com), running do init script so you can run the app as a service.

Example:

create an init script in /etc/init: 

    vi pulsepress-stream.conf  
    
add something like the following:

    description "pulsepress stream app"
    author	 "You"

    start on runlevel [2345]
    stop on shutdown

    script 
        # $HOME of user running
        export HOME="/root"
        exec /usr/local/bin/node /var/wwwnodejs/stream/js-app/app.js 2>&1 >> /var/log/node.log

    end script

After start/stop like:

    sudo start pulsepress-stream  

    sudo stop pulsepress-stream


== Changelog ==

= 1.0 =
* Initial release
