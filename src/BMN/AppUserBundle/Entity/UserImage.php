<?php

namespace BMN\AppUserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * BMN\AppUserBundle\Entity\UserImage
 *
 * @ORM\Table(name="userimage")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class UserImage
{
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\OneToOne(targetEntity="BMN\AppUserBundle\Entity\AppUser")
     */
    private $appUser;

    /**
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank
     */
    private $name;

    /**
     * Image path
     *
     * @var string
     *
     * @ORM\Column(name="path", type="text", length=255, nullable=false)
     */
    private $path;

    /**
     * Image file
     *
     * @var File
     *
     * @Assert\File(
     *     maxSize = "1M",
     *     mimeTypes = {"image/jpeg", "image/gif", "image/png", "image/tiff"},
     *     maxSizeMessage = "The maximum allowed file size is 1MB.",
     *     mimeTypesMessage = "Only the filetypes image are allowed."
     * )
     */
    private $file;
    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets file.
     *
     * @param UploadedFile $file
     */
    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;
    }



    /**
     * Get file.
     *
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }



    public function getAbsolutePath()
    {
        return null === $this->path
            ? null
            : $this->getUploadRootDir() . '/' . $this->path;
    }

    public function getWebPath()
    {
        return null === $this->path
            ? null
            : $this->getUploadDir() . '/' . $this->path;
    }

    protected function getUploadRootDir()
    {
// the absolute directory path where uploaded
// documents should be saved
        return __DIR__ . '/../../../../web/' . $this->getUploadDir();
    }

    protected function getUploadDir()
    {
// get rid of the __DIR__ so it doesn't screw up
// when displaying uploaded doc/image in the view.
        return 'local/img/users';
    }

    /**
     * Called before saving the entity
     *
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload()
    {
        if (null !== $this->file) {
            // do whatever you want to generate a unique name

            $filename = $this->name;
            $this->path = $filename . '.' . $this->file->guessExtension();
        }
    }

    /**
     * Called before entity removal
     *
     * @ORM\PreRemove()
     */
    public function removeUpload()
    {
        if ($file = $this->getAbsolutePath()) {

            unlink($file);
        }
    }

    /**
     * Called after entity persistence
     *
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload()
    {
        // The file property can be empty if the field is not required
        if (null === $this->file) {
            return;
        }

        // Use the original file name here but you should
        // sanitize it at least to avoid any security issues

        // move takes the target directory and then the
        // target filename to move to

        $this->file->move(

            $this->getUploadRootDir(),
            $this->path
        );

        // Set the path property to the filename where you've saved the file
        //$this->path = $this->file->getClientOriginalName();

        // Clean up the file property as you won't need it anymore

        $this->file = null;
    }

    public function __toString()
    {
        return $this->getName();
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param int $appUser
     */
    public function setAppUser($appUser)
    {
        $this->appUser = $appUser;
    }

    /**
     * @return int
     */
    public function getAppUser()
    {
        return $this->appUser;
    }

}