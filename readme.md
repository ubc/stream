## Stream 

install. 

Enable the wordpress plugin 

go to the directory. plugins/stream/js-app 
run node app.js 

Update the stream setting found in 
Settings -> Stream

## Protip
If you have upstart running do init script so you can run the app as a service.

Example:

create an init script in /etc/init: 

    vi pulsepress-stream.conf  
    
add something like the following:

    description  "pulsepress stream app"
    author	 "You"

    start on startup
    stop on shutdown

    script 
        # $HOME of user running
        export HOME="/root"
        exec /usr/local/bin/node /var/wwwnodejs/stream/js-app/app.js 2>&1 >> /var/log/node.log

    end script`

After start/stop like:

    sudo start pulsepress-stream  

    sudo stop pulsepress-stream
