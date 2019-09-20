<?php

namespace App\DataFixtures;

use App\Entity\BlogPost;
use App\Entity\Comment;
use App\Entity\User;
use App\Security\TokenGenerator;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Faker\Factory;

class AppFixtures extends Fixture
{

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var Factory
     */
    private $faker;

    private const USERS = [

        [
            'username'  => 'fatihsarac',
            'email'     => 'fatih@sarac.com',
            'name'      => 'Fatih Saraç',
            'password'  => 'Sifre123',
            'roles'     => [User::ROLE_SUPERADMIN],
            'enabled'   => true,
        ],

        [
            'username'  => 'mervesarac',
            'email'     => 'merve@sarac.com',
            'name'      => 'Merve Saraç',
            'password'  => 'Sifre123',
            'roles'     => [User::ROLE_ADMIN],
            'enabled'   => true,
        ],

        [
            'username'  => 'ferdisarac',
            'email'     => 'ferdi@sarac.com',
            'name'      => 'Ferdi Saraç',
            'password'  => 'Sifre123',
            'roles'     => [User::ROLE_WRITER],
            'enabled'   => true,
        ],

        [
            'username'  => 'yasarsarac',
            'email'     => 'yasar@sarac.com',
            'name'      => 'Yaşar Saraç',
            'password'  => 'Sifre123',
            'roles'     => [User::ROLE_WRITER],
            'enabled'   => true,
        ],

        [
            'username'  => 'aytensarac',
            'email'     => 'ayten@sarac.com',
            'name'      => 'Ayten Saraç',
            'password'  => 'Sifre123',
            'roles'     => [User::ROLE_WRITER],
            'enabled'   => true,
        ],

        [
            'username'  => 'editor',
            'email'     => 'editor@editor.com',
            'name'      => 'Editör Üye',
            'password'  => 'Sifre123',
            'roles'     => [User::ROLE_EDITOR],
            'enabled'   => false,
        ],

        [
            'username'  => 'yorumcu',
            'email'     => 'yorumcu@yorumcu.com',
            'name'      => 'Yorumcu Üye',
            'password'  => 'Sifre123',
            'roles'     => [User::ROLE_COMMENTATOR],
            'enabled'   => true,
        ],

    ];
    /**
     * @var TokenGenerator
     */
    private $tokenGenerator;


    public function __construct(UserPasswordEncoderInterface $passwordEncoder, TokenGenerator $tokenGenerator)
    {
        $this->passwordEncoder = $passwordEncoder;

        $this->faker = Factory::create();
        $this->tokenGenerator = $tokenGenerator;
    }

    /**
     * @param ObjectManager $manager
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {
        $this->loadUsers($manager);
        $this->loadBlogPosts($manager);
        $this->loadComments($manager);
    }

    public function loadUsers(ObjectManager $manager)
    {
        foreach (self::USERS as $UserInfo){
            $user = new User();
            $user->setUsername($UserInfo['username']);
            $user->setEmail($UserInfo['email']);
            $user->setName($UserInfo['name']);
            $user->setPassword($this->passwordEncoder->encodePassword($user, $UserInfo['password']));
            $user->setRoles($UserInfo['roles']);
            $user->setEnabled($UserInfo['enabled']);

            if(!$UserInfo['enabled']){
                $user->setConfirmationToken(
                    $this->tokenGenerator->getRandomToken()
                );
            }

            $this->addReference('user_'.$UserInfo['username'], $user);

            $manager->persist($user);
        }

        $manager->flush();;
    }

    public function loadBlogPosts(ObjectManager $manager)
    {
        for($i=1; $i<=100; $i++){

            $blogPost = new BlogPost();
            $blogPost->setTitle($this->faker->realText(50));
            $blogPost->setPublished($this->faker->dateTimeThisYear);
            $blogPost->setContent($this->faker->realText());
            $blogPost->setAuthor($this->RandomUserData($blogPost));
            $blogPost->setSlug("blog-".$i);

            $this->setReference('blog_post'.$i, $blogPost);

            $manager->persist($blogPost);
        }

        $manager->flush();
    }

    public function loadComments(ObjectManager $manager)
    {
        for ($i=1; $i<=100; $i++){
            for ($q=1; $q<=rand(1,10); $q++){
                $comment = new Comment();
                $comment->setAuthor($this->RandomUserData($comment));
                $comment->setPublished($this->faker->dateTimeThisYear);
                $comment->setContent($this->faker->realText(80));
                $comment->setBlogPost($this->getReference('blog_post'.$i));

                $manager->persist($comment);
            }
        }

        $manager->flush();;
    }

    /**
     * @param $entity
     * @return object
     */
    public function RandomUserData($entity): object
    {
        $randomUser = self::USERS[rand(0, 6)];

        if($entity instanceof BlogPost && !count(array_intersect($randomUser['roles'], [User::ROLE_SUPERADMIN, User::ROLE_ADMIN, User::ROLE_WRITER]))){
            return $this->RandomUserData($entity);
        }

        if($entity instanceof Comment && !count(array_intersect($randomUser['roles'], [User::ROLE_SUPERADMIN, User::ROLE_ADMIN, User::ROLE_WRITER, User::ROLE_COMMENTATOR]))){
            return $this->RandomUserData($entity);
        }


        return $this->getReference('user_' . $randomUser['username']);
    }


}