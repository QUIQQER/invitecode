<?php

/**
 * This file contains package_quiqqer_invitecode_ajax_settings_getCodeGenerators
 */

use QUI\InviteCode\Exception\InviteCodeException;
use QUI\InviteCode\Handler;
use QUI\Utils\Security\Orthos;

/**
 * Send InviteCodes via mail
 *
 * @param array $ids - InviteCode IDs
 * @param bool $resend - Resend if already sent
 * @return bool - success
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_invitecode_ajax_sendMail',
    function ($ids, $resend) {
        $ids    = Orthos::clearArray(json_decode($ids, true));
        $resend = boolval($resend);

        try {
            foreach ($ids as $id) {
                $InviteCode = Handler::getInviteCode((int)$id);
                $email      = $InviteCode->getEmail();

                // do not send codes without e-mail address
                if (empty($email)) {
                    continue;
                }

                $InviteCode->sendViaMail($resend);
            }
        } catch (QUI\Permissions\Exception $Exception) {
            throw $Exception;
        } catch (InviteCodeException $Exception) {
            QUI::getMessagesHandler()->addError(
                QUI::getLocale()->get(
                    'quiqqer/invitecode',
                    'message.ajax.sendMail.error',
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
                'message.ajax.sendMail.success'
            )
        );

        return true;
    },
    array('ids', 'resend'),
    'Permission::checkAdminUser'
);
