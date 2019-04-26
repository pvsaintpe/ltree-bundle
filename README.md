# ltree-bundle

Installation:
-------------
```
composer require pvsaintpe/ltree-bundle
```

Using
-----

1. Create Entity class:
```php
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Entity;
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
}
```

2. Create Repository class:
```php
use Doctrine\ORM\EntityManagerInterface;
use LTree\Repository\LTreeEntityRepository;

/**
 * Class TestRepository
 *
 * @method TestEntity|null find($id, $lockMode = null, $lockVersion = null)
 * @method TestEntity|null findOneBy(array $criteria, array $orderBy = null)
 * @method TestEntity[]    findAll()
 * @method TestEntity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @package LTree\Entity
 */
class TestRepository extends LTreeEntityRepository
{
    /**
     * TestRepository constructor.
     * @param EntityManagerInterface $registry
     */
    public function __construct(EntityManagerInterface $registry)
    {
        parent::__construct($registry, $registry->getClassMetadata(TestEntity::class));
    }
}
```

