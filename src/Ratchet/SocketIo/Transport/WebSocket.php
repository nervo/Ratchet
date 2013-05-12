<?php

namespace Ratchet\SocketIo\Transport;

use Ratchet\SocketIo;
use Ratchet\ConnectionInterface;
use Ratchet\WebSocket\WsServer;
use Ratchet\MessageComponentInterface;
use Psr\Log;

/**
 * WebSocket transport
 */
class WebSocket extends Transport
{
    /**
     * Server proxy
     *
     * @var \Ratchet\MessageComponentInterface
     */
    protected $serverProxy;

    /**
     * Logger
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * WebSocket server
     *
     * @var \Ratchet\WebSocket\WsServer
     */
    protected $wsServer;

    /**
     * Constructor
     *
     * @param \Ratchet\MessageComponentInterface $serverProxy
     * @param Log\LoggerInterface                $logger
     */
    public function __construct(
        MessageComponentInterface $serverProxy,
        Log\LoggerInterface $logger = null
    ) {
        // Server
        $this->serverProxy = $serverProxy;

        // Logger
        $this->logger = $logger;

        // WebSocket server
        $this->wsServer = new WsServer(
            $this->serverProxy
        );

        // Log
        if ($this->logger) {
            $this->logger->info('Initialize transport type ' . $this->getType());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'websocket';
    }

    /**
     * {@inheritdoc}
     */
    public function onOpen(ConnectionInterface $connection)
    {
        // Log
        if ($this->logger) {
            $this->logger->debug('Transport type ' . $this->getType() . ' onOpen');
        }

        $this->wsServer->onOpen($connection);
    }

    /**
     * {@inheritdoc}
     */
    public function onMessage(ConnectionInterface $connection, $message)
    {
        // Log
        if ($this->logger) {
            $this->logger->debug('Transport type ' . $this->getType() . ' onMessage', array(utf8_encode($message)));
        }

        if (!isset($connection->WebSocket)) {
            $this->onOpen($connection);
        }

        $this->wsServer->onMessage($connection, $message);

        if (!$connection->socketIoConnection->isEstablished() && $connection->WebSocket->established) {
            $connection->socketIoConnection->isEstablished(true);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function onClose(ConnectionInterface $connection)
    {
        // Log
        if ($this->logger) {
            $this->logger->debug('Transport type ' . $this->getType() . ' onClose');
        }

        $this->wsServer->onClose($connection);
    }

    /**
     * {@inheritdoc}
     */
    public function onError(ConnectionInterface $connection, \Exception $e)
    {
        // Log
        if ($this->logger) {
            $this->logger->debug(
                'Transport type ' . $this->getType() . ' onError',
                array(get_class($e), $e->getMessage())
            );
        }

        $this->wsServer->onError($connection, $e);
    }
}
