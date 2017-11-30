<?php

namespace QUI\InviteCode;

use QUI;

/**
 * Class Handler
 *
 * Main Invite Code handler
 */
class Handler
{
    /**
     * InviteCode runtime cache
     *
     * @var InviteCode[]
     */
    protected static $inviteCodes = array();

    public static function getInviteCode($id)
    {
        if (isset(self::$inviteCodes[$id])) {
            return self::$inviteCodes[$id];
        }

        self::$inviteCodes[$id] = new InviteCode($id);

        return self::$inviteCodes[$id];
    }

    /**
     * Create new InviteCode
     *
     * @param array $data
     * @return InviteCode
     */
    public static function createInviteCode($data)
    {
        $Now = new \DateTime();

        $inviteCode = array(
            'createDate' => $Now->format('Y-m-d H:i:s')
        );

        if (!empty($data['userId'])) {
            $User          = QUI::getUsers()->get($data['userId']);
            $data['email'] = $User->getAttribute('email');

            $inviteCode['userId'] = $User->getId();
        }

        if (!empty($data['email'])) {
            $inviteCode['email'] = $data['email'];
        }

//        QUI::getDataBase()->insert()

    }

    /**
     * Check if an invite code already eixsts
     *
     * @param string $code
     * @return bool
     */
    public static function existsCode($code)
    {
        $result = QUI::getDataBase()->fetch(array(
            'select' => 'id',
            'from'   => self::getTable(),
            'where'  => array(
                'code' => $code
            ),
            'limit'  => 1
        ));

        return !empty($result);
    }

    /**
     * Get InviteCode table
     *
     * @return string
     */
    public static function getTable()
    {
        return 'quiqqer_invitecodes';
    }
}
