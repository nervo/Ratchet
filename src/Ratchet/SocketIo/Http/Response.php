<?php

namespace Ratchet\SocketIo\Http;

use Evenement\EventEmitter;
use Guzzle\Http\Message\Response as GuzzleResponse;
//use React\Socket\ConnectionInterface;
use Ratchet\ConnectionInterface;
use React\Stream\WritableStreamInterface;

class Response extends EventEmitter implements WritableStreamInterface
{
    private $closed = false;
    private $writable = true;
    private $conn;
    private $headWritten = false;
    private $chunkedEncoding = true;
    private $keepAlive = true;

    public function __construct(ConnectionInterface $conn)
    {
        $this->conn = $conn;
    }

    public function isWritable()
    {
        return $this->writable;
    }

    public function writeContinue()
    {
        if ($this->headWritten) {
            throw new \Exception('Response head has already been written.');
        }

        $this->conn->write("HTTP/1.1 100 Continue\r\n");
    }

    public function writeHead($status = 200, array $headers = array())
    {
        if ($this->headWritten) {
            throw new \Exception('Response head has already been written.');
        }

        if (isset($headers['Content-Length'])) {
            $this->chunkedEncoding = false;
        }

        $response = new GuzzleResponse($status);
        //$response->setHeader('X-Powered-By', 'React/alpha');
        $response->addHeaders($headers);
        if ($this->chunkedEncoding) {
            $response->setHeader('Transfer-Encoding', 'chunked');
        }
        $data = (string) $response;
        $this->conn->send($data);

        $this->headWritten = true;

        return $this;
    }

    public function write($data)
    {
        if (!$this->headWritten) {
            throw new \Exception('Response head has not yet been written.');
        }

        if ($this->chunkedEncoding) {
            $len = strlen($data);
            $chunk = dechex($len)."\r\n".$data."\r\n";
            $flushed = $this->conn->send($chunk);
        } else {
            $flushed = $this->conn->send($data);
        }

        return $flushed;
    }

    public function end($data = null)
    {
        if (null !== $data) {
            $this->send($data);
        }

        if ($this->chunkedEncoding) {
            $this->conn->send("0\r\n\r\n");
        }

        if (!$this->keepAlive) {
            $this->conn->close();
        }

        return $this;
    }

    public function close()
    {
        if ($this->closed) {
            return;
        }

        $this->closed = true;

        $this->writable = false;

        $this->conn->close();
    }
}
