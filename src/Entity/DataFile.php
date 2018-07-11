<?php

namespace App\Entity;

use App\Entity\Model\UserRelatedInterface;
use App\Entity\Traits\IdentityTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DataFileRepository")
 */
class DataFile implements UserRelatedInterface
{
    const ORIGIN_SHOPIFY = 'shopify';
    const ORIGIN_AMAZON = 'amazon';

    use IdentityTrait, TimestampableEntity, BlameableEntity;

    /**
     * @var User|null
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(referencedColumnName="id", nullable=false)
     */
    private $user;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer")
     */
    private $fileSize = 0;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @Assert\NotBlank()
     * @Assert\Length(min=1, max=255)
     */
    private $fileId;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $fileMimeType;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=50, nullable=false)
     */
    private $origin;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=250, nullable=false)
     */
    private $originalName;

    /**
     * @var UploadedFile|null
     *
     * @Assert\NotBlank(groups={"create"})
     * @Assert\File(maxSize="10M", groups={"create"})
     */
    private $uploadedFile;

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function getFileSize(): int
    {
        return $this->fileSize;
    }

    public function setFileSize(?int $fileSize)
    {
        $this->fileSize = $fileSize;
    }

    public function getUploadedFile(): ?UploadedFile
    {
        return $this->uploadedFile;
    }

    public function setUploadedFile(?UploadedFile $uploadedFile)
    {
        $this->uploadedFile = $uploadedFile;
    }

    public function getFileId(): ?string
    {
        return $this->fileId;
    }

    public function setFileId(string $fileId = null)
    {
        $this->fileId = $fileId;
    }

    public function getFileMimeType(): ?string
    {
        return $this->fileMimeType;
    }

    public function setFileMimeType(string $fileMimeType = null)
    {
        $this->fileMimeType = $fileMimeType;
    }

    public function getOrigin(): ?string
    {
        return $this->origin;
    }

    public function setOrigin(string $origin): void
    {
        $this->origin = $origin;
    }

    public function getOriginalName(): ?string
    {
        return $this->originalName;
    }

    public function setOriginalName(string $originalName): void
    {
        $this->originalName = $originalName;
    }
}
