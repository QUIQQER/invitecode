<?php

namespace QUI\InviteCode;

/**
 * Interface CodeGeneratorInterface
 *
 * General interface for all code generators
 */
interface CodeGeneratorInterface
{
    /**
     * Generate a random, unique Invite code
     *
     * @param string $prefix (optional)
     * @return string
     */
    public static function generate($prefix = '');
}
