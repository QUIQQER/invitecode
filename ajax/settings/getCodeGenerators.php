<?php

/**
 * This file contains package_quiqqer_invitecode_ajax_settings_getCodeGenerators
 */

/**
 * Get list of all CodeGenerators
 *
 * @return array
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_invitecode_ajax_settings_getCodeGenerators',
    function () {
        return \QUI\InviteCode\CodeGenerator::getList();
    },
    array(),
    'Permission::checkAdminUser'
);
