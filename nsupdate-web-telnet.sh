#!/bin/sh
# nsupdate web-interface
# $Id$
# Karl-Martin Skontorp <kms@skontorp.net>

# Example

echo -e "GET /nsupdate-web/?hostname=hermelin.skontorp.net&ttl=30&key=xxx HTTP/1.1\nHost: skontorp.net\n\n" | telnet skontorp.net 80
