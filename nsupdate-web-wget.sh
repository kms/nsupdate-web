#!/bin/sh
# nsupdate-web
# Updates BIND records.
#
# (c) Karl-Martin Skontorp <kms@skontorp.net> ~ http://22pf.org/
# Licensed under the GNU GPL 2.0 or later.

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
