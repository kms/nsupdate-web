#!/bin/sh
# nsupdate web-interface
# $Id$
# Karl-Martin Skontorp <kms@skontorp.net>

# Config
WGET=/usr/bin/wget

URL="http://skontorp.net/nsupdate-web/"
HOSTNAME="hermelin.skontorp.net"
TTL="30" # Seconds TTL
KEY="xxx" # Remember to URL encode this!
IP="" # Default

# Execute
PARAMS="?hostname=$HOSTNAME&ttl=$TTL&key=$KEY&ip=$IP"

$WGET -q -O - $URL$PARAMS
