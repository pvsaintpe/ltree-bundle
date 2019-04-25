<?php

namespace LTree\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use LTree\Annotation\LTreeChilds;
use LTree\Annotation\LTreeEntity;
use LTree\Annotation\LTreeParent;
use LTree\Annotation\LTreePath;
use LTree\Repository\LTreeEntityInterface;

/**
 * Class TestEntity
 * @package LTree\Entity
 *
 * @Entity(repositoryClass="LTree\Entity\TestRepository")
 * @LTreeEntity()
 */
class TestEntity implements LTreeEntityInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @LTreePath()
     * @ORM\Column(type="ltree")
     */
    private $path = null;

    /**
     * @LTreeParent()
     * @ORM\ManyToOne(targetEntity="TestEntity", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     */
    private $parent;

    /**
     * @LTreeChilds()
     * @ORM\OneToMany(targetEntity="TestEntity", mappedBy="parent", cascade={"all"}, orphanRemoval=true)
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $children;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    /**
     * @inheritDoc
     */
    public function setParent($parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @inheritDoc
     */
    public function getPath(): array
    {
        return $this->path ?: [];
    }

    /**
     * @inheritDoc
     */
    public function setPath(?array $path): self
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getLevel(): int
    {
        return count($this->getPath());
    }

    /**
     * @inheritDoc
     */
    public function addChildren($child)
    {
        if (!$this->children->contains($child)) {
            $this->children[] = $child;
            $child->setParent($this);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function removeChildren($child)
    {
        if ($this->children->contains($child)) {
            $this->children->removeElement($child);
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getChildren()
    {
        return $this->children;
    }
}
