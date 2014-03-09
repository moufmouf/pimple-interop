<?php
namespace Interop\Container\Pimple;

use Interop\Container\ContainerInterface;
use Interop\Container\ParentAwareContainerInterface;

/**
 * This class wraps a container into an object that can be used as an array
 * (therefore as Pimple).
 * It is used with the fallback container.
 * This way, the fallback container can be accessed using the $container['instance'] notation. 
 *  
 * @author David Négrier <david@mouf-php.com>
 */
class FallbackContainerAdapter implements \ArrayAccess, ContainerInterface {

	/**
	 * @var ContainerInterface
	 */
	protected $container;
	
	public function __construct(ContainerInterface $container) {
		$this->container = $container;
	}
	
	/**
	 * Call the underlying `has` method.
	 *
	 * @param string $id The unique identifier for the parameter or object
	 * @return Boolean
	 */
	public function offsetExists($id)
	{
		return $this->container->has($id);
	}
	
	/**
	 * Call the underlying `get` method.
	 *
	 * @param string $id The unique identifier for the parameter or object
	 * @return mixed The value of the parameter or an object
	 */
	public function offsetGet($id)
	{
		return $this->container->get($id);
	}
	
	/**
	 * Forward any other calls to the container.
	 * 
	 * @param string $method
	 * @param array $arguments
	 * @return mixed
	 */
	public function __call($method, $arguments) {
		return call_user_func_array(array($this->container, $method), $param_arr);
	}
	
	/* (non-PHPdoc)
	 * @see \Interop\Container\ContainerInterface::get()
	 */
	public function get($identifier) {
		return $this->container->get($identifier);
	}

	/* (non-PHPdoc)
	 * @see \Interop\Container\ContainerInterface::has()
	 */
	public function has($identifier) {
		return $this->container->has($identifier);
	}

	/**
	 * Not available in fallback container
	 *
	 * @param string $id    The unique identifier for the parameter or object
	 * @param mixed  $value The value of the parameter or a closure to defined an object
	 */
	public function offsetSet($id, $value)
	{
		// TODO: we could fallback to the original Pimple here.
		// same thing in __call.
		throw new \Exception("Setting an instance in a root container is not allowed. You can only do that is pure Pimple containers.");
	}
	
	/**
	 * Not available in fallback container
	 *
	 * @param string $id The unique identifier for the parameter or object
	 */
	public function offsetUnset($id)
	{
		throw new \Exception("Unsetting an instance in a root container is not allowed. You can only do that is pure Pimple containers.");
	}
}