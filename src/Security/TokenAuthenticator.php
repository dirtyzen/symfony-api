<?php


namespace App\Security;


use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\ExpiredTokenException;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\PreAuthenticationJWTUserToken;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Guard\JWTTokenAuthenticator;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class TokenAuthenticator extends JWTTokenAuthenticator
{

    /**
     * @param PreAuthenticationJWTUserToken $preAuthToken
     * @param UserProviderInterface $userProvider
     * @return User|ExpiredTokenException|\Symfony\Component\Security\Core\User\UserInterface|null
     */
    public function getUser($preAuthToken, UserProviderInterface $userProvider)
    {

        /** @var User $user */
        $user = parent::getUser($preAuthToken, $userProvider);

        if(
            $user->getPasswordChangeDate() &&
            $preAuthToken->getPayload()['iat'] < $user->getPasswordChangeDate()
        ){
            return new ExpiredTokenException();
        }

        return $user;
    }


}