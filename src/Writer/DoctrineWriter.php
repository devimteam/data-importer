<?php

namespace Devimteam\Component\DataImporter\Writer;

use Devimteam\Component\DataImporter\Interfaces\TruncatableInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;

/**
 * Class DoctrineWriter.
 */
class DoctrineWriter implements WriterInterface
{
    const DEFAULT_BATCH_SIZE = 20;

    /**
     * @var bool
     */
    protected $truncate = false;

    /**
     * @var EntityManager
     */
    private $objectManager;

    /**
     * @var string
     */
    private $entityClassName;

    /**
     * @var \Doctrine\Common\Persistence\ObjectRepository
     */
    private $repository;

    /**
     * @var \Doctrine\Common\Persistence\Mapping\ClassMetadata
     */
    private $entityMetadata;

    /**
     * @var int
     */
    private $batchSize = self::DEFAULT_BATCH_SIZE;

    /**
     * @var int
     */
    private $counter = 0;

    /**
     * @var string
     */
    private $parentSetterName = 'setParent';

    /**
     * @var string
     */
    private $keyChildren = 'children';

    /**
     * @var string
     */
    private $keyExternalId = 'externalId';

    /**
     * @var string
     */
    private $keyExternalSource = 'externalSource';

    /**
     * @var string
     */
    private $keyExternalSubId = 'externalSubId';

    /**
     * @var bool
     */
    private $isUpdate = false;

    /**
     * DoctrineWriter constructor.
     *
     * @param ObjectManager $objectManager
     * @param string $entityClassName
     */
    public function __construct(
        ObjectManager $objectManager,
        string $entityClassName
    ) {
        $this->objectManager = $objectManager;
        $this->entityClassName = $entityClassName;
        $this->repository = $objectManager->getRepository($entityClassName);
        $this->entityMetadata = $objectManager->getClassMetadata($entityClassName);
    }

    /**
     * @return void
     *
     * @throws \RuntimeException
     */
    public function prepare()
    {
        if ($this->truncate) {
            $this->truncateRepository();
        }
    }

    /**
     * @return void
     *
     * @throws \RuntimeException
     */
    private function truncateRepository()
    {
        if (!$this->repository instanceof TruncatableInterface) {
            throw new \RuntimeException(
                sprintf('Repository "%s" does not implement TruncatableInterface', get_class($this->repository))
            );
        }

        $this->repository->truncate();
    }

    /**
     * @param array $payload
     *
     * @return bool
     */
    private function isBatchRequest(array $payload) : bool
    {
        return array_keys($payload) === range(0, count($payload) - 1);
    }

    /**
     * @param mixed $data
     *
     * @return void
     *
     * @throws \RuntimeException
     */
    public function writeData($data)
    {
        if ($this->isBatchRequest($data)) {
            $this->batchBegin(count($data));
            foreach ($data as $item) {
                $this->writeDataRecursive($item);
            }
        } else {
            $this->batchBegin();
            $this->writeDataRecursive($data);
        }
        $this->batchEnd();
    }

    /**
     * @param int $count
     */
    protected function batchBegin($count = 1)
    {
        $this->counter += $count;
    }

    /**
     * @param mixed $data
     * @param mixed|null $parent
     *
     * @throws \RuntimeException
     */
    private function writeDataRecursive($data, $parent = null)
    {
        $entity = null;

        if (!$this->truncate && $this->isUpdate) {

            $findExternalIdMethod = 'findBy' . ucfirst($this->keyExternalId);

            if (isset($data[$this->keyExternalId]) && !isset($data[$this->keyExternalSource], $data[$this->keyExternalSubId]) &&
                !method_exists($this->repository, $findExternalIdMethod)
            ) {
                throw new \RuntimeException(
                    sprintf(
                        'In "%s" repository not found "%s" method for find by "%s" field',
                        get_class($this->repository), $findExternalIdMethod, $this->keyExternalId
                    )
                );
            }

            if (isset($data[$this->keyExternalSource], $data[$this->keyExternalId]) && !isset($data[$this->keyExternalSubId])) {

                $findExternalIdMethod .= 'And' . ucfirst($this->keyExternalSource);

                if (!method_exists($this->repository, $findExternalIdMethod)) {
                    throw new \RuntimeException(
                        sprintf(
                            'In "%s" repository not found "%s" method for find by "%s" and "%s" fields',
                            get_class($this->repository), $findExternalIdMethod, $this->keyExternalId,
                            $this->keyExternalSource
                        )
                    );
                }
            }

            if (isset($data[$this->keyExternalSource], $data[$this->keyExternalId], $data[$this->keyExternalSubId])) {

                $findExternalIdMethod .= 'And' . ucfirst($this->keyExternalSource) . 'And' . ucfirst($this->keyExternalSubId);

                if (!method_exists($this->repository, $findExternalIdMethod)) {
                    throw new \RuntimeException(
                        sprintf(
                            'In "%s" repository not found "%s" method for find by "%s", "%s" and "%s" fields',
                            get_class($this->repository),
                            $findExternalIdMethod,
                            $this->keyExternalId,
                            $this->keyExternalSource,
                            $this->keyExternalSubId
                        )
                    );
                }
            }

            $entity = (new \ReflectionMethod($this->repository, $findExternalIdMethod))->invokeArgs($this->repository,
                [$data[$this->keyExternalId], $data[$this->keyExternalSource] ?? null, $data[$this->keyExternalSubId] ?? null]);

        }

        if (null === $entity) {
            $entity = $this->createEntityInstance();
        }

        $this->fillEntityData($entity, $data);
        $this->persistEntity($entity);

        if (isset($data[$this->keyChildren]) && is_array($data[$this->keyChildren])) {
            if (method_exists($entity, $this->parentSetterName)) {
                $entity->{$this->parentSetterName}($parent);
            }

            foreach ($data[$this->keyChildren] as $child) {
                $this->writeDataRecursive($child, $entity);
            }
        }
    }

    /**
     * @return mixed
     */
    protected function createEntityInstance()
    {
        $className = $this->entityMetadata->getName();

        return new $className();
    }

    /**
     * @param $entity
     * @param $data
     */
    protected function fillEntityData($entity, $data)
    {
        $fieldNames = array_merge($this->entityMetadata->getFieldNames(), $this->entityMetadata->getAssociationNames());

        foreach ($fieldNames as $fieldName) {
            $value = null;
            if (!is_object($data) && isset($data[$fieldName])) {
                $value = $data[$fieldName];
            } elseif (method_exists($data, 'get' . ucfirst($fieldName))) {
                $value = $data->{'get' . ucfirst($fieldName)}();
            }

            if (null === $value) {
                continue;
            }

            $setter = 'set' . ucfirst($fieldName);
            if (method_exists($entity, $setter)) {
                $entity->$setter($value);
            }
        }
    }

    /**
     * @param mixed $entity
     */
    protected function persistEntity($entity)
    {
        $this->objectManager->persist($entity);
    }

    /**
     */
    protected function batchEnd()
    {
        if ($this->counter >= $this->batchSize) {
            $this->objectManager->flush();
            $this->objectManager->clear();
        }
    }

    /**
     */
    public function finish()
    {
        $this->objectManager->flush();
        $this->objectManager->clear();
        $this->counter = 0;
    }

    /**
     * @param mixed $parentSetterName
     */
    public function setParentSetterName($parentSetterName)
    {
        $this->parentSetterName = $parentSetterName;
    }

    /**
     * @param string $keyChildren
     */
    public function setKeyChildren($keyChildren)
    {
        $this->keyChildren = $keyChildren;
    }

    /**
     * @param string $keyExternalId
     */
    public function setKeyExternalId($keyExternalId)
    {
        $this->keyExternalId = $keyExternalId;
    }

    /**
     * @return bool
     */
    public function getTruncate() : bool
    {
        return $this->truncate;
    }

    /**
     * @param bool $truncate
     *
     * @return WriterInterface
     */
    public function setTruncate(bool $truncate) : WriterInterface
    {
        $this->truncate = $truncate;

        return $this;
    }

    /**
     * @param int $batchSize
     *
     * @return WriterInterface
     */
    public function setBatchSize(int $batchSize) : WriterInterface
    {
        $this->batchSize = $batchSize;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isUpdate() : bool
    {
        return $this->isUpdate;
    }

    /**
     * @param boolean $isUpdate
     */
    public function setIsUpdate(bool $isUpdate)
    {
        $this->isUpdate = $isUpdate;
    }
}
