<?php

namespace LTree\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use LTree\Annotation\Driver\AnnotationDriverInterface;
use LTree\Repository\LTreeEntityRepositoryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use LTree\Annotation\Driver\PropertyNotFoundException;
use ErrorException;
use LogicException;
use ReflectionException;

/**
 * Class LTreeSubscriber
 * @package LTree\Listener
 */
class LTreeSubscriber implements EventSubscriber
{
    /**
     * @var AnnotationDriverInterface
     */
    protected $annotationDriver;

    /**
     * @var PropertyAccessorInterface
     */
    protected $propertyAccessor;

    /**
     * LTreeSubscriber constructor.
     * @param AnnotationDriverInterface $annotationDriver
     * @param PropertyAccessorInterface $propertyAccessor
     */
    public function __construct(AnnotationDriverInterface $annotationDriver, PropertyAccessorInterface $propertyAccessor)
    {
        $this->annotationDriver = $annotationDriver;
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * @param object $entity
     * @param ClassMetadata $classMetadata
     * @throws ErrorException
     * @throws PropertyNotFoundException
     */
    protected function buildPath($entity, ClassMetadata $classMetadata): void
    {
        $pathName = $this->annotationDriver->getPathProperty($entity)->getName();
        $parentName = $this->annotationDriver->getParentProperty($entity)->getName();

        $parent = $this->propertyAccessor->getValue($entity, $parentName);
        $identifiers = $classMetadata->getIdentifierValues($entity);
        $idValue = reset($identifiers);

        if (!$idValue) {
            throw new LogicException('Can\'t build path property without id');
        }
        $pathValue = array();
        if ($parent) {
            $pathValue = $this->propertyAccessor->getValue($parent, $pathName);
            if (!$pathValue || empty($pathValue)) {
                $this->buildPath($parent, $classMetadata);
                $pathValue = $this->propertyAccessor->getValue($parent, $pathName);
            }
            if (!$pathValue || empty($pathValue)) {
                throw new ErrorException('Unable to build parent path property');
            }
        }
        if (!is_array($pathValue)) {
            $this->buildPath($parent, $classMetadata);
            $pathValue = $this->propertyAccessor->getValue($parent, $pathName);
        }
        $pathValue[] = $idValue;
        $this->propertyAccessor->setValue($entity, $pathName, $pathValue);
    }

    /**
     * @param PreUpdateEventArgs $args
     * @throws ErrorException
     * @throws PropertyNotFoundException
     * @throws ReflectionException
     */
    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getEntity();
        if (!$this->annotationDriver->entityIsLTree($entity)) {
            return;
        }

        $parentPath = $this->annotationDriver->getParentProperty($entity)->getName();
        if (!$args->hasChangedField($parentPath)) {
            return;
        }

        $repo = $args->getEntityManager()->getRepository(get_class($entity));
        if (!$repo instanceof LTreeEntityRepositoryInterface) {
            throw new LogicException(sprintf('%s must implement LTreeEntityRepositoryInterface', get_class($repo)));
        }
        $repo->moveNode($entity, $args->getNewValue($parentPath));
        $this->buildPath($entity, $args->getEntityManager()->getClassMetadata(get_class($entity)));
    }

    /**
     * @param OnFlushEventArgs $eventArgs
     * @throws ErrorException
     * @throws PropertyNotFoundException
     * @throws ReflectionException
     */
    public function onFlush(OnFlushEventArgs $eventArgs): void
    {
        $em = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if (!$this->annotationDriver->entityIsLTree($entity)) {
                continue;
            }
            $classMetadata = $em->getClassMetadata(get_class($entity));
            $this->buildPath($entity, $classMetadata);
            $uow->recomputeSingleEntityChangeSet($classMetadata, $entity);
        }
    }

    /**
     * @return array|string[]
     */
    public function getSubscribedEvents(): array
    {
        return array(
            'preUpdate',
            'onFlush'
        );
    }
}
