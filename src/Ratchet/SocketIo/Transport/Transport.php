<?php

namespace Ratchet\SocketIo\Transport;

/**
 * Transport
 */
abstract class Transport implements TransportInterface
{
    /**
     * Enabled
     *
     * @var bool
     */
    protected $enabled = false;

    /**
     * {@inheritdoc}
     */
    public function isType($type)
    {
        return (string) $type === $this->getType();
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled($enabled = null)
    {
        if (is_null($enabled)) {
            return $this->enabled;
        }

        $this->enabled = (bool) $enabled;
    }
}
