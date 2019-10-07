<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\Controller\PasswordResetAction;

/**
 * @ApiResource(
 *     itemOperations={
 *          "get" = {
 *              "access_control" = "is_granted('IS_AUTHENTICATED_FULLY')",
 *              "normalization_context" = {
 *                  "groups" = {"get"}
 *              }
 *          },
 *          "put" = {
 *              "access_control" = "is_granted('IS_AUTHENTICATED_FULLY') and object == user",
 *              "denormalization_context" = {
 *                  "groups" = {"put"}
 *              },
 *              "normalization_context" = {
 *                  "groups" = {"get"}
 *              }
 *          },
 *          "put-reset-password" = {
 *              "access_control" = "is_granted('IS_AUTHENTICATED_FULLY') and object == user",
 *              "method" = "PUT",
 *              "path" = "/users/{id}/reset-password",
 *              "controller" = PasswordResetAction::class,
 *              "denormalization_context" = {
 *                  "groups" = {"put-reset-password"}
 *              },
 *              "validation_groups" = {"put-reset-password"}
 *          }
 *     },
 *     collectionOperations={
 *          "post" = {
 *              "denormalization_context" = {
 *                  "groups" = {"post"}
 *              },
 *              "normalization_context" = {
 *                  "groups" = {"get"}
 *              },
 *              "validation_groups" = {"post"}
 *          }
 *      }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity("username", message="Kullanıcı adı başkası tarafından kullanılıyor.")
 * @UniqueEntity("email", message="Email adresi zaten kayıtlı.")
 */
class User implements UserInterface
{

    const ROLE_COMMENTATOR = 'ROLE_COMMENTATOR';
    const ROLE_WRITER = 'ROLE_WRITER';
    const ROLE_EDITOR = 'ROLE_EDITOR';
    const ROLE_ADMIN = 'ROLE_ADMIN';
    const ROLE_SUPERADMIN = 'ROLE_SUPERADMIN';

    const DEFAULT_ROLES = [self::ROLE_COMMENTATOR];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"get", "get-blog-post-with-author"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(groups={"post"})
     * @Assert\Length(min="6", max="255", minMessage="Kullanıcı adı en az 6 karakterli olmalı.", maxMessage="Kullanıcı en fazla 255 karakter olabilir.", groups={"post"})
     * @Groups({"get", "post", "get-comment-with-author", "get-blog-post-with-author"})
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(groups={"post"})
     * @Assert\Regex(
     *     pattern="/(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9]){7,}/",
     *     message="Şifrede bir büyük harf, bir küçük harf, bir sayı bulunmalı ve en az 7 karakterli olmalı.",
     *     groups={"post"}
     * )
     * @Groups({"post"})
     */
    private $password;

    /**
     * @Assert\NotBlank(groups={"post"}, message="Şifre tekrarını yazmadınız.")
     * @Assert\Expression(
     *     "this.getPassword() === this.getRetypedPassword()",
     *     message="Şifreler birbirini tutmuyor.",
     *     groups={"post"}
     * )
     * @Groups({"post"})
     */
    private $retypedPassword;

    /**
     * @Assert\NotBlank(message="Yeni şifreyi yazmadınız.", groups={"put-reset-password"})
     * @Assert\Regex(
     *     pattern="/(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9]){7,}/",
     *     message="Şifrede bir büyük harf, bir küçük harf, bir sayı bulunmalı ve en az 7 karakterli olmalı.",
     *     groups={"put-reset-password"}
     * )
     * @Groups({"put-reset-password"})
     */
    private $newPassword;

    /**
     * @Assert\NotBlank(message="Yeni şifrenin tekrarını yazmadınız.", groups={"put-reset-password"})
     * @Assert\Expression(
     *     "this.getNewPassword() === this.getNewRetypedPassword()",
     *     message="Yeni şifreler birbirini tutmuyor.",
     *     groups={"put-reset-password"}
     * )
     * @Groups({"put-reset-password"})
     */
    private $newRetypedPassword;

    /**
     * @Assert\NotBlank(message="Mevcut şifrenizi yazmadınız.", groups={"put-reset-password"})
     * @UserPassword(message="Mevcut şifrenizi yanlış yazdınız.", groups={"put-reset-password"})
     * @Groups({"put-reset-password"})
     */
    private $oldPassword;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="İsim alanını boş bıraktınız.", groups={"post"})
     * @Assert\Length(min="3", max="255", minMessage="En az 3 karakterli bir isim giriniz.", groups={"post", "put"})
     * @Groups({"get", "post", "put", "get-comment-with-author", "get-blog-post-with-author"})
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Email alanını boş bıraktınız.", groups={"post"})
     * @Assert\Email(message="Geçersiz bir email yazdınız.", groups={"post", "put"})
     * @Groups({"post", "put", "get-admin", "get-owner"})
     */
    private $email;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\BlogPost", mappedBy="author")
     * @Groups({"get"})
     */
    private $posts;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="author")
     * @Groups({"get"})
     */
    private $comments;

    /**
     * @ORM\Column(type="simple_array", length=200)
     * @Groups({"get-admin", "get-owner"})
     */
    private $roles;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $passwordChangeDate;

    /**
     * @ORM\Column(type="boolean")
     */
    private $enabled;

    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private $confirmationToken;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->roles = self::DEFAULT_ROLES;
        $this->enabled = false;
        $this->confirmationToken = null;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getRetypedPassword(): ?string
    {
        return $this->retypedPassword;
    }

    public function setRetypedPassword(string $retypedPassword): self
    {
        $this->retypedPassword = $retypedPassword;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    /**
     * @return Collection
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getSalt()
    {
        return null;
    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }


    public function getNewPassword(): ?string
    {
        return $this->newPassword;
    }

    public function setNewPassword($newPassword): self
    {
        $this->newPassword = $newPassword;

        return $this;
    }

    public function getNewRetypedPassword(): ?string
    {
        return $this->newRetypedPassword;
    }

    public function setNewRetypedPassword($newRetypedPassword): self
    {
        $this->newRetypedPassword = $newRetypedPassword;

        return $this;
    }

    public function getOldPassword(): ?string
    {
        return $this->oldPassword;
    }

    public function setOldPassword($oldPassword): self
    {
        $this->oldPassword = $oldPassword;

        return $this;
    }

    public function getPasswordChangeDate()
    {
        return $this->passwordChangeDate;
    }

    public function setPasswordChangeDate($passwordChangeDate): self
    {
        $this->passwordChangeDate = $passwordChangeDate;

        return $this;
    }

    public function getEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled($enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getConfirmationToken(): ?string
    {
        return $this->confirmationToken;
    }

    public function setConfirmationToken($confirmationToken): self
    {
        $this->confirmationToken = $confirmationToken;

        return $this;
    }

    public function __toString(): ?string
    {
        return $this->name;
    }


}
