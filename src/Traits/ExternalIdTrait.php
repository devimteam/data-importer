<?php

namespace Devim\Component\DataImporter\Traits;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class ExternalIdTrait
 */
trait ExternalIdTrait
{
    /**
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $externalId;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $externalSubId;

    /**
     * @var int|null
     *
     * @ORM\Column(type="bigint", nullable=true)
     */
    protected $externalSource;

    /**
     * @return string|null
     */
    public function getExternalId(): ?string
    {
        return $this->externalId;
    }

    /**
     * @param string|null $externalId
     *
     * @return $this
     */
    public function setExternalId(?string $externalId)
    {
        $this->externalId = $externalId;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getExternalSubId() : ?string
    {
        return $this->externalSubId;
    }

    /**
     * @param string|null $externalSubId
     *
     * @return $this
     */
    public function setExternalSubId(?string $externalSubId)
    {
        $this->externalSubId = $externalSubId;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getExternalSource() : ?int
    {
        return $this->externalSource;
    }

    /**
     * @param int|null $externalSource
     *
     * @return ExternalIdTrait
     */
    public function setExternalSource(?int $externalSource)
    {
        $this->externalSource = $externalSource;

        return $this;
    }
}
