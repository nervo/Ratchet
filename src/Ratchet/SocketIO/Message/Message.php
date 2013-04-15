<?php

namespace Ratchet\SocketIO\Message;

use Ratchet\SocketIO\SocketIOServerInterface;
use Ratchet\ConnectionInterface;

/**
 * Message
 */
class Message
{
    // Types
    const TYPE_CONNECT = 1;
    const TYPE_EVENT   = 5;
    
    /**
     * Id
     * 
     * @var int
     */
    protected $id;
    
    /**
     * Endpoint
     * 
     * @var string
     */
    protected $endpoint;
    
    /**
     * Data
     * 
     * @var array|null
     */
    protected $data;

    /**
     * Constructor
     * 
     * @param int        $id
     * @param string     $endpoint
     * @param array|null $data
     */
    public function __construct($id = null, $endpoint = null, array $data = null)
    {
        // Id
        $this->id = (int) $id;
        
        // Endpoint
        $this->endpoint = (string) $endpoint;
        
        // Data
        $this->data = $data;
    }
    
    /**
     * Get type
     * 
     * @return int
     * 
     * @throws \BadMethodCallException
     */
    protected function getType()
    {
        throw new \BadMethodCallException();
    }
    
    /**
     * Get id
     * 
     * @return int
     */
    protected function getId()
    {
        return $this->id;
    }
    
    /**
     * Get endpoint
     * 
     * @return string
     */
    protected function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * Get data
     * 
     * @return array|null
     */
    protected function getData()
    {
        return $this->data;
    }
    
    /**
     * To string
     * 
     * @return string
     */
    public function __toString()
    {
         return implode(
            ':',
            array(
                $this->getType(),
                $this->getId(),
                $this->getEndpoint(),
                ($data = $this->getData()) ? json_encode($data) : null
            )
         );
    }
    
    /**
     * Parse
     * 
     * @param string $message
     * 
     * @return \Ratchet\SocketIO\Message\Message|null
     */
    public static function parse($message)
    {
        $parts = explode(':', (string) $message, 4);
        
        switch ($parts[0]) {
            case self::TYPE_CONNECT:
                return new ConnectMessage(
                    isset($parts[1]) ? $parts[1] : null,
                    isset($parts[2]) ? $parts[2] : null
                );
            case self::TYPE_EVENT:
                return new EventMessage(
                    isset($parts[1]) ? $parts[1] : null,
                    isset($parts[2]) ? $parts[2] : null,
                    isset($parts[3]) ? json_decode($parts[3], true) : null
                );
            default:
                return null;
        }
    }
    
    /**
     * Handle server
     * 
     * @param \Ratchet\SocketIO\SocketIOServerInterface $server
     * 
     * @throws \BadMethodCallException
     */
    public function handleServerConnection(SocketIOServerInterface $server, ConnectionInterface $connection)
    {
        throw new \BadMethodCallException();
    }
}
