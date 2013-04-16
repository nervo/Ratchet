<?php

namespace Ratchet\SocketIO\Message;

use Ratchet\SocketIO\SocketIOServerInterface;
use Ratchet\ConnectionInterface;

/**
 * Event message
 */
class EventMessage extends Message
{
    /**
     * Constructor
     * 
     * @param int        $id
     * @param string     $endpoint
     * @param array|null $data
     */
    public function __construct($name, array $args = null, $endpoint = null, $id = null)
    {
        // Data
        $this->data = array(
            'name' => (string) $name
        );
        
        if (!is_null($args)) {
            $this->data['args'] = $args;
        }
        
        // Endpoint
        $this->endpoint = is_null($endpoint) ? null : (string) $endpoint;
        
        // Id
        $this->id = is_null($id) ? null : (int) $id;
    }
    
    /**
     * From array
     * 
     * @param array $array
     */
    public static function fromArray(array $array)
    {
        if (!isset($array[3])) {
            return null;
        }
        
        $data = json_decode($array[3], true);
        
        if (!isset($data['name'])) {
            return null;
        }
        
        return new self(
            $data['name'],
            isset($data['args']) && is_array($data['args']) ? $data['args'] : null,
            isset($array[2]) ? $array[2] : null,
            isset($array[1]) ? $array[1] : null
        );
    }
    
    /**
     * {@inheritdoc}
     */
    protected function getType()
    {
        return self::TYPE_EVENT;
    }
    
    /**
     * {@inheritdoc}
     */
    public function handleServerConnection(SocketIOServerInterface $server, ConnectionInterface $connection)
    {
        $data = $this->getData();
        
        if (isset($data['name'])) {
            $server->onEvent(
                $connection,
                $data['name'],
                (isset($data['args']) && is_array($data['args'])) ? $data['args'] : null
            );
        }
    }
}
