<?php

namespace LTree\Annotation\Driver;

use Doctrine\Common\Annotations\Reader;
use ReflectionClass;
use ReflectionProperty;
use ReflectionObject;
use ReflectionException;

/**
 * Class AnnotationDriver
 * @package LTree\Annotation\Driver
 */
class AnnotationDriver implements AnnotationDriverInterface
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * AnnotationDriver constructor.
     * @param Reader $reader
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * @param object $object
     * @return bool
     */
    public function entityIsLTree($object): bool
    {
        return (bool) $this->getReader()->getClassAnnotation(new ReflectionObject($object), self::ENTITY_ANNOTATION);
    }

    /**
     * @param string $className
     * @return bool
     * @throws ReflectionException
     */
    public function classIsLTree(string $className): bool
    {
        return (bool) $this->getReader()->getClassAnnotation(new ReflectionClass($className), self::ENTITY_ANNOTATION);
    }

    /**
     * @param object $object
     * @return ReflectionProperty
     * @throws PropertyNotFoundException
     */
    public function getChildsProperty($object): ReflectionProperty
    {
        return $this->findAnnotation($object, self::CHILDS_ANNOTATION);
    }

    /**
     * @param object $object
     * @return ReflectionProperty
     * @throws PropertyNotFoundException
     */
    public function getParentProperty($object): ReflectionProperty
    {
        return $this->findAnnotation($object, self::PARENT_ANNOTATION);
    }

    /**
     * @param object $object
     * @return ReflectionProperty
     * @throws PropertyNotFoundException
     */
    public function getPathProperty($object): ReflectionProperty
    {
        return $this->findAnnotation($object, self::PATH_ANNOTATION);
    }

    /**
     * @param $object
     * @return ReflectionProperty
     * @throws PropertyNotFoundException
     */
    public function getIdProperty($object): ReflectionProperty
    {
        return $this->findAnnotation($object, self::ID_ANNOTATION);
    }

    /**
     * @return Reader
     */
    public function getReader(): Reader
    {
        return $this->reader;
    }

    /**
     * @param object $object
     * @param string $annotationName
     * @return ReflectionProperty
     * @throws PropertyNotFoundException
     */
    protected function findAnnotation($object, string $annotationName): ReflectionProperty
    {
        $reflObject = new ReflectionObject($object);

        foreach ($reflObject->getProperties() as $property) {
            $result = $this->getReader()->getPropertyAnnotation($property, $annotationName);
            if ($result) {
                return $property;
            }
        }

        throw new PropertyNotFoundException($object, $annotationName);
    }
}
