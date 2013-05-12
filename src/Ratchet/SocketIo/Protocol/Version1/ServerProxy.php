<?php

namespace Ratchet\SocketIo\Protocol\Version1;

use Ratchet\SocketIo;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use React\EventLoop;
use Psr\Log;

class ServerProxy implements MessageComponentInterface
{
    /**
     * Server
     *
     * @var SocketIo\SocketIoServerInterface
     */
    protected $server;

    /**
     * Loop
     *
     * @var \React\EventLoop\LoopInterface
     */
    protected $loop;

    /**
     * Options
     *
     * @var \Ratchet\SocketIo\Protocol\Version1\Options
     */
    protected $options;

    /**
     * Logger
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Connections
     *
     * @var \SplObjectStorage
     */
    protected $connections;

    /**
     * Message handlers
     *
     * @var array
     */
    protected $messageHandlers = array();

    /**
     *  Constructor
     *
     * @param SocketIo\SocketIoServerInterface $server
     * @param EventLoop\LoopInterface          $loop
     * @param Options                          $options
     * @param Log\LoggerInterface              $logger
     */
    public function __construct(
        SocketIo\SocketIoServerInterface $server,
        EventLoop\LoopInterface $loop,
        Options $options,
        Log\LoggerInterface $logger = null
    ) {
        // Server
        $this->server = $server;

        // Loop
        $this->loop = $loop;

        // Logger
        $this->logger = $logger;

        // Options
        $this->options = $options;

        // Connections
        $this->connections = new \SplObjectStorage();

        // Log
        if ($this->logger) {
            $this->logger->info('Initialize protocol version 1 server proxy');
        }

        // Message handlers
        $this->addMessageHandler(
            new Message\ConnectMessageHandler(
                $server
            )
        )->addMessageHandler(
            new Message\MessageMessageHandler(
                $server
            )
        )->addMessageHandler(
            new Message\JsonMessageMessageHandler(
                $server
            )
        )->addMessageHandler(
            new Message\EventMessageHandler(
                $server
            )
        )->addMessageHandler(
            new Message\DisconnectMessageHandler(
                $server
            )
        );

        // Heartbeat message handler
        if ($this->options->areHeartbeatsEnabled()) {
            $this->addMessageHandler(
                new Message\HeartbeatMessageHandler(
                    $this->loop,
                    $this->connections,
                    $this->options
                )
            );
        }
    }

    /**
     * Add message handler
     *
     * @param \Ratchet\SocketIo\Protocol\Version1\Message\MessageHandler $handler
     *
     * @return \Ratchet\SocketIo\Protocol\Version1\ServerProxy
     */
    protected function addMessageHandler(Message\MessageHandler $handler)
    {
        $this->messageHandlers[$handler->getType()] = $handler;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function onOpen(ConnectionInterface $connection)
    {
        // Log
        if ($this->logger) {
            $this->logger->debug('Protocol version 1 server proxy onOpen');
        }

        $this->connections->attach($connection);

        foreach ($this->messageHandlers as $messageHandler) {
            $messageHandler->onOpen(
                $connection
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function onMessage(ConnectionInterface $connection, $message)
    {
        // Log
        if ($this->logger) {
            $this->logger->debug('Protocol version 1 server proxy onMessage', array($message));
        }

        if ($connection->socketIoConnection->isEstablished()) {

            $socketIoMessage = (new Message\Message())
                ->unserialize((string) $message);

            foreach ($this->messageHandlers as $messageHandler) {
                if ($messageHandler->isMessageType($socketIoMessage)) {
                    $messageHandler->onMessage(
                        $connection,
                        $socketIoMessage
                    );
                    break;
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function onClose(ConnectionInterface $connection)
    {
        // Log
        if ($this->logger) {
            $this->logger->debug('Protocol version 1 proxy server onClose');
        }

        $this->connections->detach($connection);

        foreach ($this->messageHandlers as $messageHandler) {
            $messageHandler->onClose(
                $connection
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function onError(ConnectionInterface $connection, \Exception $e)
    {
        // Log
        if ($this->logger) {
            $this->logger->debug('Protocol version 1 server proxy onError', array(get_class($e), $e->getMessage()));
        }

        if ($connection->socketIoConnection->isEstablished()) {
            $this->server->onError($connection->socketIoConnection, $e);
        } else {
            $connection->close();
        }
    }
}
