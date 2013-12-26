<?php
namespace Interop\Container\Pimple;

use Interop\Container\ReadableContainerInterface;
use Interop\Container\ParentAwareContainerInterface;

/**
 * This class extends the Pimple class.
 * It adds compatibility with the container-interop APIs.
 * In particular, it adds the capability for Pimple to accept fallback DI containers.
 * 
 * @author David Négrier <david@mouf-php.com>
 */
class PimpleInterop extends \Pimple implements ReadableContainerInterface, ParentAwareContainerInterface {

	/**
	 * @var ContainerInterface
	 */
	protected $fallbackContainer;
		
	/**
	 * Checks if a parameter or an object is set.
	 *
	 * @param string $id The unique identifier for the parameter or object
	 *
	 * @return Boolean
	 */
	public function offsetExists($id)
	{
		$has = parent::offsetExists($id);
		if ($has) {
			return true;
		}
		
		if ($this->fallbackContainer && $this->fallbackContainer->has($id)) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Gets a parameter or an object, first from Pimple, then from the fallback container if it is set.
	 *
	 * @param string $id The unique identifier for the parameter or object
	 *
	 * @return mixed The value of the parameter or an object
	 *
	 * @throws PimpleNotFoundException if the identifier is not defined
	 */
	public function offsetGet($id)
	{
		if (parent::offsetExists($id)) {
			return parent::offsetGet($id);
		}
		
		// Let's search in the fallback container:
		if ($this->fallbackContainer && $this->fallbackContainer->has($id)) {
			return $this->fallbackContainer->get($id);
		}
		
		throw new PimpleNotFoundException(sprintf('Identifier "%s" is not defined.', $id));
	}
	
	/* (non-PHPdoc)
	 * @see \Interop\Container\ReadableContainerInterface::get()
	 */
	public function get($identifier) {
		return $this->offsetGet($identifier);
	}

	/* (non-PHPdoc)
	 * @see \Interop\Container\ReadableContainerInterface::has()
	 */
	public function has($identifier) {
		return $this->offsetExists($identifier);
	}

	/* (non-PHPdoc)
	 * @see \Interop\Container\ParentAwareContainerInterface::setParentContainer()
	 */
	public function setParentContainer(ReadableContainerInterface $container) {
		$this->fallbackContainer = $container;
	}
}