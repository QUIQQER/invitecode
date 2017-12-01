<?php

/**
 * This file contains package_quiqqer_invitecode_ajax_settings_getCodeGenerators
 */

use QUI\InviteCode\Exception\InviteCodeException;
use QUI\InviteCode\Handler;
use QUI\Utils\Security\Orthos;

/**
 * Delete InviteCodes
 *
 * @param array $ids - InviteCode IDs
 * @return bool - success
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_invitecode_ajax_delete',
    function ($ids) {
        $ids = Orthos::clearArray(json_decode($ids, true));

        try {
            foreach ($ids as $id) {
                $InviteCode = Handler::getInviteCode((int)$id);
                $InviteCode->delete();
            }
        } catch (InviteCodeException $Exception) {
            QUI::getMessagesHandler()->addError(
                QUI::getLocale()->get(
                    'quiqqer/invitecode',
                    'message.ajax.delete.error',
                    array(
                        'error' => $Exception->getMessage()
                    )
                )
            );

            return false;
        } catch (\Exception $Exception) {
            QUI\System\Log::writeException($Exception);

            QUI::getMessagesHandler()->addError(
                QUI::getLocale()->get(
                    'quiqqer/invitecode',
                    'message.ajax.general_error'
                )
            );

            return false;
        }

        QUI::getMessagesHandler()->addSuccess(
            QUI::getLocale()->get(
                'quiqqer/invitecode',
                'message.ajax.delete.success'
            )
        );

        return true;
    },
    array('ids'),
    'Permission::checkAdminUser'
);
