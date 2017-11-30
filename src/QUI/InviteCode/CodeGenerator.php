<?php

namespace QUI\InviteCode;

use QUI;
use QUI\Utils\System\File;

/**
 * Class CodeGenerator
 *
 * Generated unique, random Invite Codes
 */
class CodeGenerator
{
    /**
     * Get CodeGenerator that is currently set
     *
     * @return string - FQ class name
     */
    public static function getCurrentGenerator()
    {

    }

    /**
     * Get list of all available CodeGenerators
     *
     * @return array
     */
    public static function getList()
    {
        $dir        = QUI::getPackage('quiqqer/invitecode')->getDir() . 'src/QUI/InviteCode/CodeGeneratos';
        $generators = array();

        foreach (File::readDir($dir, true) as $file) {
            $generators[] = basename($file, '.php');
        }

        return $generators;
    }
}
