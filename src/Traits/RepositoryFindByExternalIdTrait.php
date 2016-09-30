<?php

namespace Devimteam\Component\DataImporter\Traits;

use Doctrine\ORM\QueryBuilder;

/**
 * Class RepositoryFindByExternalIdTrait
 */
trait RepositoryFindByExternalIdTrait
{
    /**
     * @param string $externalId
     *
     * @return mixed|null
     */
    public function findByExternalId(string $externalId)
    {
        return $this->findOneBy(['externalId' => $externalId]);
    }

    /**
     * @param string $externalId
     * @param int $externalSource
     *
     * @return mixed|null
     */
    public function findByExternalIdAndExternalSource(string $externalId, int $externalSource)
    {
        return $this->findOneBy(['externalId' => $externalId, 'externalSource' => $externalSource]);
    }

    /**
     * @param string $externalId
     * @param int $externalSource
     * @param string $externalSubId
     *
     * @return mixed|null
     */
    public function findByExternalIdAndExternalSourceAndExternalSubId(string $externalId, int $externalSource, string $externalSubId)
    {
        return $this->findOneBy(['externalId' => $externalId, 'externalSource' => $externalSource, 'externalSubId' => $externalSubId]);
    }

    /**
     * @param bool $castToInt
     *
     * @return int
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function getLastExternalId($castToInt = true)
    {
        $query = 'MAX(cast (e.externalId as int)) as maxExternalId';

        if (!$castToInt) {
            $query = 'MAX(e.externalId) as maxExternalId';
        }
        /** @var QueryBuilder $qb */
        $qb = $this->createQueryBuilder('e');
        $qb
            ->select($query)
            ->setMaxResults(1);

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param int $externalSource
     * @param bool $castToInt
     *
     * @return int
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function getLastExternalIdBySource(int $externalSource, $castToInt = true)
    {
        $query = 'MAX(cast (e.externalId as int)) as maxExternalId';

        if (!$castToInt) {
            $query = 'MAX(e.externalId) as maxExternalId';
        }
        /** @var QueryBuilder $qb */
        $qb = $this->createQueryBuilder('e');
        $qb
            ->select($query)
            ->where('e.externalSource = :external_source')
            ->setParameter('external_source', $externalSource)
            ->setMaxResults(1);

        return $qb->getQuery()->getSingleScalarResult();
    }
}
