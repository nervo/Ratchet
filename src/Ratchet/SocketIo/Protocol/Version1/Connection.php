<?php

namespace Ratchet\SocketIo\Protocol\Version1;

use Ratchet\SocketIo;
use Ratchet\AbstractConnectionDecorator;

/**
 * Protocol version 1 connection
 */
class Connection extends AbstractConnectionDecorator implements SocketIo\SocketIoConnectionInterface
{
    /**
     * Protocol
     *
     * @var \Ratchet\SocketIo\Protocol\ProtocolInterface
     */
    protected $protocol;

    /**
     * Transport
     *
     * @var \Ratchet\SocketIo\Transport\TransportInterface
     */
    protected $transport;

    /**
     * Session id
     *
     * @var string
     */
    protected $sessionId;

    /**
     * Established
     *
     * @var bool
     */
    protected $established = false;

    /**
     * Constructor
     *
     * @param \Ratchet\SocketIo\Protocol\ProtocolInterface   $protocol
     * @param \Ratchet\SocketIo\Transport\TransportInterface $transport
     * @param string                                         $sessionId
     */
    public function __construct(
        SocketIo\Protocol\ProtocolInterface $protocol,
        SocketIo\Transport\TransportInterface $transport,
        $sessionId
    ) {
        // Protocol
        $this->protocol = $protocol;

        // Transport
        $this->transport = $transport;

        // Session id
        $this->sessionId = (string) $sessionId;
    }

    /**
     * {@inheritdoc}
     */
    public function getProtocol()
    {
        return $this->protocol;
    }

    /**
     * {@inheritdoc}
     */
    public function getTransport()
    {
        return $this->transport;
    }

    /**
     * {@inheritdoc}
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * {@inheritdoc}
     */
    public function isEstablished($established = null)
    {
        if (is_null($established)) {
            return $this->established;
        }

        $this->established = (bool) $established;
    }

    /**
     * {@inheritdoc}
     */
    public function send($message)
    {
        return $this->getConnection()->send((string) $message);
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        $this->getConnection()->close();
    }
}
