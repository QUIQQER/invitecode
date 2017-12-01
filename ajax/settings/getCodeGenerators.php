<?php

/**
 * This file contains package_quiqqer_invitecode_ajax_settings_getCodeGenerators
 */

use QUI\InviteCode\CodeGenerator;

/**
 * Get list of all CodeGenerators
 *
 * @return array
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_invitecode_ajax_settings_getCodeGenerators',
    function () {
        return CodeGenerator::getList();
    },
    array(),
    'Permission::checkAdminUser'
);
