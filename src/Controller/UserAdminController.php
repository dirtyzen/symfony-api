<?php


namespace App\Controller;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Controller\EasyAdminController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserAdminController extends EasyAdminController
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @param User $entity
     */
    protected function persistEntity($entity)
    {
        $this->PasswordEncode($entity);
        parent::persistEntity($entity);
    }

    /**
     * @param User $entity
     */
    protected function updateEntity($entity)
    {
        $this->PasswordEncode($entity);
        parent::updateEntity($entity);
    }

    /**
     * @param User $entity
     */
    private function PasswordEncode($entity): void
    {
        $entity->setPassword(
            $this->passwordEncoder->encodePassword($entity, $entity->getPassword())
        );
    }


}