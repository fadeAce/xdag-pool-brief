#### brief xdag pool tool

configuration state stats netconn in easy way

- configure the xdag/client/unix_sock.dat path in your pool script
- put this brief.php into /var/www/
- set nginx php-fpm on rooted at /var/www/
- go check state : http://ip:port/brief.php?param=state
- go check stats : http://ip:port/brief.php?param=stats
- go check net conn : http://ip:port/brief.php?param=netconn

it would return a JSON based body .e.g

    curl http://ip:port/brief.php?param=state

    state{ "version": "0.2.6", "state": "Trying to connect to the main network." }