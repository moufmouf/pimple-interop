<?php
namespace Interop\Container\Pimple;

use Interop\Container\ContainerInterface;

/**
 * This class wraps a container into an object that can be used as an array
 * (therefore as Pimple).
 * It is used with the delegate container container.
 * This way, the delegate container container can be accessed using the $container['instance'] notation.
 *  
 * @author David Négrier <david@mouf-php.com>
 */
class DelegateLookupContainerAdapter implements \ArrayAccess, ContainerInterface {

	/**
	 * @var ContainerInterface
	 */
	protected $container;

	/**
	 * @var \Pimple
	 */
	protected $pimpleFallback;

	public function __construct(ContainerInterface $container, \Pimple $pimpleFallback) {
		$this->container = $container;
		$this->pimpleFallback = $pimpleFallback;
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
	 * Forwarded to Pimple original container (although it should be avoided)
	 *
	 * @param string $id    The unique identifier for the parameter or object
	 * @param mixed  $value The value of the parameter or a closure to defined an object
	 */
	public function offsetSet($id, $value)
	{
		$this->pimpleFallback->offsetSet($id, $value);
	}
	
	/**
	 * Forwarded to Pimple original container (although it should be avoided)
	 *
	 * @param string $id The unique identifier for the parameter or object
	 */
	public function offsetUnset($id)
	{
		$this->pimpleFallback->offsetUnset($id);
	}
}