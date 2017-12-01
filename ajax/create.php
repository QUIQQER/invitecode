<?php

/**
 * This file contains package_quiqqer_invitecode_ajax_settings_getCodeGenerators
 */

use QUI\InviteCode\Exception\InviteCodeException;
use QUI\InviteCode\Exception\InviteCodeMailException;
use QUI\InviteCode\Handler;
use QUI\Utils\Security\Orthos;

/**
 * Create new InviteCode
 *
 * @param array $attributes
 * @return int|false - New InviteCode ID or false on error
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_invitecode_ajax_create',
    function ($attributes) {
        $attributes = Orthos::clearArray(json_decode($attributes, true));

        try {
            $InviteCode = Handler::createInviteCode($attributes);
        } catch (InviteCodeException $Exception) {
            QUI::getMessagesHandler()->addError(
                QUI::getLocale()->get(
                    'quiqqer/invitecode',
                    'message.ajax.create.error',
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
                'message.ajax.create.success'
            )
        );

        if (!empty($attributes['sendmail'])) {
            try {
                $InviteCode->sendViaMail();
            } catch (InviteCodeMailException $Exception) {
                QUI::getMessagesHandler()->addError(
                    QUI::getLocale()->get(
                        'quiqqer/invitecode',
                        'message.ajax.create.send_mail.error',
                        array(
                            'error' => $Exception->getMessage()
                        )
                    )
                );
            } catch (\Exception $Exception) {
                QUI\System\Log::writeException($Exception);

                QUI::getMessagesHandler()->addError(
                    QUI::getLocale()->get(
                        'quiqqer/invitecode',
                        'message.ajax.create.send_mail.general_error'
                    )
                );
            }

            QUI::getMessagesHandler()->addSuccess(
                QUI::getLocale()->get(
                    'quiqqer/invitecode',
                    'message.ajax.create.send_mail.success'
                )
            );
        }

        return $InviteCode->getId();
    },
    array('attributes'),
    'Permission::checkAdminUser'
);
