<?php


namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *
 *     collectionOperations={
 *          "post" = {
 *              "path" = "/users/confirm"
 *          }
 *     },
 *     itemOperations={}
 * )
 */
class UserConfirmation
{

    /**
     * @Assert\NotBlank(message="Token bilgisi boş.")
     * @Assert\Length(min=30, max=40)
     */
    public $confirmationToken;

}