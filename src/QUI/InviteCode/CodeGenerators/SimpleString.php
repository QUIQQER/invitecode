<?php

namespace QUI\InviteCode\CodeGenerators;

use QUI\InviteCode\CodeGeneratorInterface;
use QUI\InviteCode\Handler;

class SimpleString implements CodeGeneratorInterface
{
    const CODE_LENGTH = 8;

    /**
     * Generate a random, unique Invite code
     *
     * @param string $prefix (optional)
     * @return string
     */
    public static function generate($prefix = '')
    {
        $characters = array_merge(
            range('A', 'Z'),
            range(0, 9)
        );

        $count = count($characters) - 1;

        do {
            $code = $prefix;

            for ($i = 0; $i < self::CODE_LENGTH; $i++) {
                $code .= $characters[random_int(0, $count)];
            }
        } while (Handler::existsCode($code));

        return $code;
    }
}
