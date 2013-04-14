<?php

namespace Ratchet\SocketIO\Protocol\Version1\Session;

use Guzzle\Http\Message\RequestInterface;

/**
 * Manage the various versions of the WebSocket protocol
 * This accepts interfaces of versions to enable/disable
 */
class SessionManager
{
    public function getRequestSessionId(RequestInterface $request)
    {
        $segments = $request->getUrl(true)->getPathSegments();

        if (3 < count($segments) && 'socket.io' == $segments[0]) {
            return (string) $segments[3];
        }
        
        return null;
    }
}
