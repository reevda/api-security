<?php

namespace App\Serializer\Normalizer;

use App\Entity\User;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;

class UserNormalizer implements ContextAwareNormalizerInterface, CacheableSupportsMethodInterface, NormalizerAwareInterface
{
    private const ALREADY_CALLED = 'USER_NORMALIZER_ALLREADY_CALLED';
    private $security;

    use NormalizerAwareTrait;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /** @param User Object */
    public function normalize($object, $format = null, array $context = array()): array
    {
        $isOwner =$this->userIsOwner($object);
        if ($isOwner) {
            $context['groups'][] = 'owner:read';
        }

        $context[self::ALREADY_CALLED] = true;

        $data = $this->normalizer->normalize($object, $format, $context);

        //  Here: add, edit, or delete some data
        $data['isMe'] = $isOwner;
        return $data;
    }

    private function userIsOwner(User $user): bool
    {
        /** @var User $currentUser */
        $currentUser = $this->security->getUser();
        if (!$currentUser) {
            return false;
        }
        return $currentUser->getEmail() === $user->getEmail();
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        // avoid recursion: only call once per object
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof User;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return false;
    }
}
