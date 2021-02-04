#!/bin/bash
echo "loading..."
pid=`pidof php_dht_client_event_worker`
echo $pid
for i in $pid
do
kill -USR1 $i
done
echo "loading success"