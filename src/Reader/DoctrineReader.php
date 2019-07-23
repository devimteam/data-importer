<?php

namespace App\Provider\DataImportServiceProvider\Reader;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;

class DoctrineReader extends AbstractReader
{

    /**
     * @var EntityRepository
     */
    protected $repository;

    /**
     * @var array
     */
    private $data;

    /**
     * AbstractDoctrineReader constructor.
     *
     * @param ObjectManager $objectManager
     * @param string $entityClassName
     */
    public function __construct(
        ObjectManager $objectManager,
        string $entityClassName
    ) {
        $this->repository = $objectManager->getRepository($entityClassName);
    }

    public function beforeRead()
    {
        $this->data = $this->repository->createQueryBuilder('c')->getQuery()->getArrayResult();
    }

    /**
     * @return mixed
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function read()
    {
        foreach ($this->data as $item) {
            yield $item;
        }
    }
}
