#!/bin/sh
# nsupdate web-interface
# $Id$
# Karl-Martin Skontorp <kms@skontorp.net>

# Config
WGET=/usr/bin/wget

URL="http://skontorp.net/nsupdate-web/"
HOSTNAME="hermelin.skontorp.net"
TTL="30"
KEY="dZX5yu2wnZ/yavpxpMmUwZuGbPdwPonegkCzCD3VU9kZ5rKDeCePyx9aJg%2btbmDFSgWkM5cJDctoF5d3MmpRi"
IP=""

# Execute
PARAMS="?hostname=$HOSTNAME&ttl=$TTL&key=$KEY&ip=$IP"

$WGET -q -O - $URL$PARAMS
