<?php

namespace Ratchet\SocketIO\Version;

use Ratchet\MessageInterface;
use Ratchet\ConnectionInterface;
use Guzzle\Http\Message\RequestInterface;
use Ratchet\SocketIO\TransportManager;
use Ratchet\AbstractConnectionDecorator;

/**
 * A standard interface for interacting with the various version of the WebSocket protocol
 */
interface VersionInterface extends MessageInterface
{
    /**
     * Given an HTTP header, determine if this version should handle the protocol
     * @param  \Guzzle\Http\Message\RequestInterface $request
     * @return bool
     * @throws \UnderflowException                   If the protocol thinks the headers are still fragmented
     */
    public function isVersion(RequestInterface $request);

    /**
     * Although the version has a name associated with it the integer returned is the proper identification
     * @return int
     */
    public function getVersionNumber();
    
    /**
     * Perform the handshake and return the response
     * 
     * @param \Guzzle\Http\Message\RequestInterface $request
     * @param \Ratchet\SocketIO\TransportManager $transporter
     * 
     * @return \Guzzle\Http\Message\Response
     */
    public function handshake(RequestInterface $request, AbstractConnectionDecorator $connection, array $options, TransportManager $transporter);

    /**
     * @param  \Ratchet\ConnectionInterface $conn
     * @param  \Ratchet\MessageInterface    $coalescedCallback
     * @return \Ratchet\ConnectionInterface
     */
    //public function upgradeConnection(ConnectionInterface $conn, MessageInterface $coalescedCallback);
    public function upgradeConnection(ConnectionInterface $conn);

    /**
     * @return MessageInterface
     */
    //function newMessage();

    /**
     * @return FrameInterface
     */
    //function newFrame();

    /**
     * @param string
     * @param bool
     * @return string
     * @todo Change to use other classes, this will be removed eventually
     */
    //function frame($message, $mask = true);
}
