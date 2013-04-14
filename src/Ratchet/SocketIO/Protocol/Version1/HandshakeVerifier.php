<?php

namespace Ratchet\SocketIO\Protocol\Version1;

use Guzzle\Http\Message\RequestInterface;

/**
 * These are checks to ensure the client requested handshake are valid
 * Verification rules come from section 4.2.1 of the RFC6455 document
 * @todo Currently just returning invalid - should consider returning appropriate HTTP status code error #s
 */
class HandshakeVerifier
{
    /**
     * Given an array of the headers this method will run through all verification methods
     * @param  \Guzzle\Http\Message\RequestInterface $request
     * @return bool                                  TRUE if all headers are valid, FALSE if 1 or more were invalid
     */
    public function verifyRequest(RequestInterface $request)
    {
        $passes = 0;

        $passes += (int) $this->verifyRequestMethod($request->getMethod());
        $passes += (int) $this->verifyRequestProtocolVersion($request->getProtocolVersion());
        $passes += (int) $this->verifyRequestPath($request->getPath());
        $passes += (int) $this->verifyRequestHost($request->getHeader('Host', true));

        return (4 === $passes);
    }

    /**
     * Test the HTTP method.  MUST be "GET"
     * @param string
     * @return bool
     */
    protected function verifyRequestMethod($method)
    {
        return ('get' === strtolower($method));
    }

    /**
     * Test the HTTP version passed.  MUST be 1.1 or greater
     * @param string|int
     * @return bool
     */
    protected function verifyRequestProtocolVersion($version)
    {
        return (1.1 <= (double) $version);
    }

    /**
     * @param string
     * @return bool
     */
    protected function verifyRequestPath($path)
    {
        if ($path[0] != '/') {
            return false;
        }

        if (false !== strstr($path, '#')) {
            return false;
        }

        if (!extension_loaded('mbstring')) {
            return true;
        }

        return mb_check_encoding($path, 'US-ASCII');
    }

    /**
     * @param string|null
     * @return bool
     * @todo Find out if I can find the master socket, ensure the port is attached to header if not 80 or 443 - not sure if this is possible, as I tried to hide it
     * @todo Once I fix HTTP::getHeaders just verify this isn't NULL or empty...or maybe need to verify it's a valid domain??? Or should it equal $_SERVER['HOST'] ?
     */
    protected function verifyRequestHost($host)
    {
        return (null !== $host);
    }
}
