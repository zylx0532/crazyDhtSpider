echo "loading..."
pid=`pidof php_dht_client_event_worker`
echo $pid
kill -USR1 $pid
echo "loading success"