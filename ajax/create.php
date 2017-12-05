<?php

/**
 * This file contains package_quiqqer_invitecode_ajax_create
 */

use QUI\InviteCode\Exception\InviteCodeException;
use QUI\InviteCode\Exception\InviteCodeMailException;
use QUI\InviteCode\Handler;
use QUI\Utils\Security\Orthos;

/**
 * Create new InviteCode(s)
 *
 * @param array $attributes
 * @return bool - success
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_invitecode_ajax_create',
    function ($attributes) {
        $attributes = Orthos::clearArray(json_decode($attributes, true));

        try {
            $amount      = 1;
            $inviteCodes = array();

            if (!empty($attributes['amount'])) {
                $amount = (int)$attributes['amount'];
                unset($attributes['amount']);
            }

            for ($i = 0; $i < $amount; $i++) {
                $inviteCodes[] = Handler::createInviteCode($attributes);
            }
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

        } catch (QUI\Permissions\Exception $Exception) {
            throw $Exception;
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
                foreach ($inviteCodes as $InviteCode) {
                    $InviteCode->sendViaMail();
                }
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

        return true;
    },
    array('attributes'),
    'Permission::checkAdminUser'
);
