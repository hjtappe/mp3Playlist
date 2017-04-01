#!/bin/bash

set -u
set -e

function usage()
{
	echo "Usage: ssh user@host target_filename < source_file" >&2
	echo "" >&2
	echo "Cat a file to the Archive, with a given file name." >&2
	echo "Do not use scp, but a restricted ssh access." >&2
	echo "authorized_keys options:" >&2
	echo "from="source.ip,127.0.0.1",no-port-forwarding,no-X11-forwarding,no-agent-forwarding,no-pty,command="~/copy-script.sh" ssh-rsa ..." >&2
}


if [ -z "${SSH_ORIGINAL_COMMAND:-}" ]; then
	usage
	exit 1
fi

FILENAME=`basename "${SSH_ORIGINAL_COMMAND}"`
SUFFIX="${FILENAME##*.}"

if [ "$SUFFIX" != "mp3" ]; then
	echo >&2 "Only file names with *.mp3 suffix allowed."
	exit 1
fi

echo "cat > ~/Aufnahmen/${FILENAME}"
cat > ~/Aufnahmen/"${FILENAME}"
RET=$?
if [ $RET -eq 0 ]; then
	# cleanup old recordings
	find ~/Aufnahmen/ -maxdepth 1 -mindepth 1 -type f -mtime +62 -name "*.mp3" -exec rm -v "{}" \;
fi
