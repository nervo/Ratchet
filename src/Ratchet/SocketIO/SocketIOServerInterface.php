<?php

namespace Ratchet\SocketIO;

use Ratchet\ComponentInterface;
use Ratchet\ConnectionInterface;

interface SocketIOServerInterface extends ComponentInterface
{
    public function onCall(ConnectionInterface $connection);

    public function onSubscribe(ConnectionInterface $connection);

    public function onUnSubscribe(ConnectionInterface $connection);

    public function onPublish(ConnectionInterface $connection);
}
