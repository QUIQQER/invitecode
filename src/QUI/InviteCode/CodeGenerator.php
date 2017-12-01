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
     * Generate a new, random Invite Code
     *
     * @return string
     */
    public static function generate()
    {
        $generator = '\\QUI\\InviteCode\\CodeGenerators\\' . self::getCurrentGenerator();

        $Config = QUI::getPackage('quiqqer/invitecode')->getConfig();
        $prefix = $Config->get('settings', 'prefix');

        if (empty($prefix)) {
            $prefix = '';
        }

        return call_user_func($generator . '::generate', $prefix);
    }

    /**
     * Get CodeGenerator that is currently set
     *
     * @return string - FQ class name
     */
    protected static function getCurrentGenerator()
    {
        $Config           = QUI::getPackage('quiqqer/invitecode')->getConfig();
        $currentGenerator = $Config->get('settings', 'codeGenerator');

        if (empty($currentGenerator)) {
            return 'SimpleString';
        }

        return $currentGenerator;
    }

    /**
     * Get list of all available CodeGenerators
     *
     * @return array
     */
    public static function getList()
    {
        $dir        = QUI::getPackage('quiqqer/invitecode')->getDir() . 'src/QUI/InviteCode/CodeGenerators';
        $generators = array();

        foreach (File::readDir($dir, true) as $file) {
            $generators[] = basename($file, '.php');
        }

        return $generators;
    }
}
