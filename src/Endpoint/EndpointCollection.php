<?php

declare(strict_types=1);

namespace Alvtek\OpenIdConnect\Endpoint;

use Alvtek\OpenIdConnect\Endpoint;
use Alvtek\OpenIdConnect\Endpoint\Exception\UndefinedEndpointException;
use Alvtek\OpenIdConnect\Exception\InvalidArgumentException;
use Alvtek\OpenIdConnect\Provider\Exception\UnrecognisedEndpointException;
use Countable;
use Iterator;

class EndpointCollection implements Countable, Iterator
{
    /** @var Endpoint[] */
    private $endpoints;
    
    private $position;

    /**
     * @param array $endpoints
     */
    public function __construct(array $endpoints)
    {
        $this->position = 0;
        $this->endpoints = [];
        
        foreach ($endpoints as $endpoint) {
            if (!$endpoint instanceof Endpoint) {
                throw new InvalidArgumentException(sprintf("Expecting an array of type %s", Endpoint::class));
            }
            
            if ($this->hasEndpoint($endpoint)) {
                continue;
            }
            
            $this->endpoints[] = $endpoint;
        }
    }
    
    /**
     * @return boolean
     */
    public function isEmpty()
    {
        return empty($this->endpoints);
    }
    
    /**
     * @param string $type
     * @return boolean
     */
    public function hasEndpointType($type)
    {
        foreach ($this->endpoints as $endpoint) {
            if ($endpoint->type() === $type) {
                return true;
            }
        }

        return false;
    }
    
    /**
     * @param array $types
     * @return boolean
     */
    public function hasEndpointTypes(array $types)
    {
        foreach ($types as $type) {
            if (!is_string($type)) {
                throw new InvalidArgumentException("types argument must be an array of strings");
            }
            
            if (!$this->hasEndpointType($type)) {
                return false;
            }
        }
        
        return true;
    }
    
    public function hasEndpoint(Endpoint $endpoint)
    {
        foreach ($this->endpoints as $existingEndpoint) {
            if ($existingEndpoint->equals($endpoint)) {
                return true;
            }
        }
        
        return false;
    }
    
    public function addEndpoint(Endpoint $endpoint)
    {
        $endpoints = $this->endpoints;
        $endpoints[] = $endpoint;
        
        return new self($endpoints);
    }

    /**
     * @param string $type
     * @return Endpoint
     * @throws UnrecognisedEndpointException
     * @throws UndefinedEndpointException
     */
    public function get(string $type)
    {
        foreach ($this->endpoints as $endpoint) {
            if ($endpoint->type() === $type) {
                return $endpoint;
            }
        }
        
        throw new UndefinedEndpointException(sprintf("The endpoint '%s' does "
                . "not exist", $type));
    }
    
    /**
     * 
     * @return integer
     */
    public function count()
    {
        return count($this->endpoints);
    }

    public function rewind()
    {
        $this->position = 0;
    }

    /** @return Endpoint */
    public function current()
    {
        return $this->endpoints[$this->position];
    }

    /**
     * @return integer
     */
    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        ++$this->position;
    }

    /**
     * @return boolean
     */
    public function valid()
    {
        return isset($this->endpoints[$this->position]);
    }
}
