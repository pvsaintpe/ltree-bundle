<?php

namespace LTree\Annotation\Driver;

use Exception;

/**
 * Class PropertyNotFoundException
 * @package LTree\Annotation\Driver
 */
class PropertyNotFoundException extends Exception
{
    /**
     * @var string
     */
    private $className;

    /**
     * @var string
     */
    private $annotationClassName;

    public CONST INIT_ERROR = 'Class %s does not exist property annotated by %s';

    /**
     * PropertyNotFoundException constructor.
     * @param object $object
     * @param string $annotationClassName
     */
    public function __construct($object, string $annotationClassName)
    {
        $this->className = get_class($object);
        $this->annotationClassName = $annotationClassName;

        parent::__construct(sprintf(static::INIT_ERROR, $this->getClassName(), $this->getAnnotationClassName()));
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * @return string
     */
    public function getAnnotationClassName(): string
    {
        return $this->annotationClassName;
    }
}
