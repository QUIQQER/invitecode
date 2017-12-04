<?php

/**
 * This file contains package_quiqqer_invitecode_ajax_getList
 */

use QUI\InviteCode\Handler;
use QUI\Utils\Security\Orthos;
use QUI\Utils\Grid;
use QUI\Permissions\Permission;

/**
 * Get list of InviteCodes
 *
 * @param array $searchParams
 * @return int|false - New InviteCode ID or false on error
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_invitecode_ajax_getList',
    function ($searchParams) {
        Permission::hasPermission(Handler::PERMISSION_VIEW);

        $searchParams = Orthos::clearArray(json_decode($searchParams, true));
        $inviteCodes  = array();

        try {
            foreach (Handler::search($searchParams) as $InviteCode) {
                $inviteCodes[] = $InviteCode->toArray();
            }
        } catch (\Exception $Exception) {
            QUI\System\Log::writeException($Exception);

            QUI::getMessagesHandler()->addSuccess(
                QUI::getLocale()->get(
                    'quiqqer/invitecode',
                    'message.ajax.general_error'
                )
            );

            return false;
        }

        $Grid = new Grid($searchParams);

        return $Grid->parseResult(
            $inviteCodes,
            Handler::search($searchParams, true)
        );
    },
    array('searchParams'),
    'Permission::checkAdminUser'
);
