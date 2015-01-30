<?php
namespace Interop\Container\Pimple;

use Interop\Container\ContainerInterface;

/**
 * This class extends the Pimple class.
 * It adds compatibility with the container-interop APIs.
 * In particular, it adds the capability for Pimple to accept delegate lookup containers.
 * 
 * @author David Négrier <david@mouf-php.com>
 */
class PimpleInterop extends \Pimple implements ContainerInterface {

	/**
	 * @var ContainerInterface
	 */
	protected $delegateLookupContainer;
	
	/**
	 * @var DelegateLookupContainerAdapter
	 */
	protected $wrappedDelegateLookupContainer;
	
	/**
	 * Instantiate the container.
	 *
	 * Objects and parameters can be passed as argument to the constructor.
	 * 
	 * @param ContainerInterface $container The root container of the application (if any)
	 * @param array $values The parameters or objects.
	 */
	public function __construct(ContainerInterface $container = null, array $values = array())
	{
		parent::__construct($values);
		if ($container) {
			$this->delegateLookupContainer = $container;
			$this->wrappedDelegateLookupContainer = new DelegateLookupContainerAdapter($container, $this);
		} else {
			$this->wrappedDelegateLookupContainer = $this;
		}
	}

	/**
	 * Gets a parameter or an object, first from Pimple, then from the delegate lookup container if it is set.
	 *
	 * @param string $id The unique identifier for the parameter or object
	 *
	 * @return mixed The value of the parameter or an object
	 *
	 * @throws PimpleNotFoundException if the identifier is not defined
	 */
	public function offsetGet($id)
	{
		if (!array_key_exists($id, $this->values)) {
			throw new PimpleNotFoundException(sprintf('Identifier "%s" is not defined.', $id));
		}
		try {
			$isFactory = is_object($this->values[$id]) && method_exists($this->values[$id], '__invoke');

			return $isFactory ? $this->values[$id]($this->wrappedDelegateLookupContainer) : $this->values[$id];
		} catch (\InvalidArgumentException $e) {
			// To respect container-interop, let's wrap the exception.
			throw new PimpleNotFoundException($e->getMessage(), $e->getCode(), $e);
		}

	}
	
	/* (non-PHPdoc)
	 * @see \Interop\Container\ContainerInterface::get()
	 */
	public function get($identifier) {
		return $this->offsetGet($identifier);
	}

	/* (non-PHPdoc)
	 * @see \Interop\Container\ContainerInterface::has()
	 */
	public function has($identifier) {
		return $this->offsetExists($identifier);
	}
}
