<?php

namespace QUI\InviteCode\CodeGenerators;

use QUI\InviteCode\CodeGeneratorInterface;
use Ramsey\Uuid\Uuid as UuidCreator;

class Uuid implements CodeGeneratorInterface
{
    /**
     * Generate a random, unique Invite code
     *
     * @param string $prefix (optional)
     * @return string
     */
    public static function generate($prefix = '')
    {
        return $prefix . UuidCreator::uuid1()->toString();
    }
}
