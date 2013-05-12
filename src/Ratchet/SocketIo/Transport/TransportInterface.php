<?php

namespace Ratchet\SocketIo\Transport;

use Ratchet\MessageComponentInterface;

/**
 * Transport interface
 */
interface TransportInterface extends MessageComponentInterface
{
    /**
     * Transport type
     *
     * @return string
     */
    public function getType();

    /**
     * Is type
     *
     * @param string $type
     *
     * @return bool
     */
    public function isType($type);

    /**
     * Initialize
     *
     * @param  null|bool $enabled
     * @return null|bool
     */
    public function isEnabled($enabled = null);
}
