<?php

namespace QUI\InviteCode;

use QUI\InviteCode\Handler;

/**
 * Class Cron
 *
 * Cronjobs for quiqqer/invitecode
 */
class Cron
{
    /**
     * Delete all expired invite codes
     *
     * @param array $params - Cron params
     * @throws \Exception
     */
    public static function deleteExpiredInviteCodes($params)
    {
        $days = null;

        if (!empty($params['days'])) {
            $days = (int)$params['days'];
        }

        Handler::deleteExpiredInviteCodes($days);
    }

    /**
     * Delete all redeemed invite codes
     *
     * @param array $params - Cron params
     * @throws \Exception
     */
    public static function deleteRedeemedInviteCodes($params)
    {
        $days = null;

        if (!empty($params['days'])) {
            $days = (int)$params['days'];
        }

        Handler::deleteRedeemedInviteCodes($days);
    }
}
