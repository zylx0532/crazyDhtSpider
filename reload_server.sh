echo "loading..."
pid=`pidof php_dht_server_event_worker`
echo $pid
kill -USR1 $pid
echo "loading success"