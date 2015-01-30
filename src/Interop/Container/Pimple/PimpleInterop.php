<?php
namespace Interop\Container\Pimple;

use Interop\Container\ContainerInterface;

/**
 * This class extends the Pimple class.
 * It adds compatibility with the container-interop APIs.
 * In particular, it adds the capability for Pimple to accept fallback DI containers.
 * 
 * @author David Négrier <david@mouf-php.com>
 */
class PimpleInterop extends \Pimple implements ContainerInterface {

	/**
	 * @var ContainerInterface
	 */
	protected $fallbackContainer;
	
	/**
	 * @var FallbackContainerAdapter
	 */
	protected $wrappedFallbackContainer;
	
	/**
	 * The number of time this container was called recursively.
	 * @var int
	 */
	protected $nbLoops = 0;
	
	const MODE_STANDARD_COMPLIANT = 1;
	const MODE_ACT_AS_MASTER = 2;
	
	/**
	 * 
	 * @var int
	 */
	protected $mode = self::MODE_ACT_AS_MASTER;
	
	/**
	 * Sets the mode of pimple-interop.
	 * There are 2 possible modes:
	 * 
	 * - PimpleInterop::MODE_STANDARD_COMPLIANT => a mode that respects the container-interop standard.
	 * - PimpleInterop::MODE_ACT_AS_MASTER => in this mode, if Pimple does not contain the requested
	 *   identifier, it will query the fallback container.
	 * 
	 * @param int $mode
	 */
	public function setMode($mode) {
		$this->mode = $mode;
	}
	
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
			$this->fallbackContainer = $container;
			$this->wrappedFallbackContainer = new FallbackContainerAdapter($container);
		}
	}
	
	/**
	 * Checks if a parameter or an object is set.
	 *
	 * @param string $id The unique identifier for the parameter or object
	 *
	 * @return Boolean
	 */
	public function offsetExists($id)
	{
		if (!$this->fallbackContainer || $this->mode == self::MODE_STANDARD_COMPLIANT) {
			return parent::offsetExists($id);
		} elseif ($this->mode == self::MODE_ACT_AS_MASTER) {
			if ($this->nbLoops != 0) {
				return parent::offsetExists($id);
			} else {
				$this->nbLoops++;
				$has = $this->fallbackContainer->has($id);
				$this->nbLoops--;
				return $has;
			}
		} else {
			throw new \Exception("Invalid mode set");
		}
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
		if (!$this->fallbackContainer || $this->mode == self::MODE_STANDARD_COMPLIANT) {
			try {
				if (!array_key_exists($id, $this->values)) {
					throw new PimpleNotFoundException(sprintf('Identifier "%s" is not defined.', $id));
				}

				$isFactory = is_object($this->values[$id]) && method_exists($this->values[$id], '__invoke');

				return $isFactory ? $this->values[$id]($this->wrappedFallbackContainer) : $this->values[$id];
			} catch (\InvalidArgumentException $e) {
				// To respect container-interop, let's wrap the exception.
				throw new PimpleNotFoundException($e->getMessage(), $e->getCode(), $e);
			}
		} elseif ($this->mode == self::MODE_ACT_AS_MASTER) {
			if ($this->nbLoops != 0) {
				if (!array_key_exists($id, $this->values)) {
					throw new PimpleNotFoundException(sprintf('Identifier "%s" is not defined.', $id));
				}
				
				$isFactory = is_object($this->values[$id]) && method_exists($this->values[$id], '__invoke');
				
				return $isFactory ? $this->values[$id]($this->wrappedFallbackContainer) : $this->values[$id];
				
			} else {
				$this->nbLoops++;
				$instance = $this->fallbackContainer->get($id);
				$this->nbLoops--;
				return $instance;
			}
		} else {
			throw new \Exception("Invalid mode set");
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