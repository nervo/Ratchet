<?php

namespace Ratchet\SocketIO\Protocol;

use Ratchet\MessageComponentInterface;
use Guzzle\Http\Message\RequestInterface;

/**
 * Protocol interface
 */
interface ProtocolInterface extends MessageComponentInterface
{
    /**
     * Get version
     *
     * @return int
     */
    public function getVersion();

    /**
     * Is version
     *
     * @param int $version
     *
     * @return bool
     */
    public function isVersion($version);

    /**
     * Initialize
     *
     * @param  null|bool $enabled
     * @return null|bool
     */
    public function isEnabled($enabled = null);

    /**
     * Is http request protocol
     *
     * @param \Guzzle\Http\Message\RequestInterface $httpRequest
     */
    public function isHttpRequestProtocol(RequestInterface $httpRequest);
}
