<?php

namespace QUI\InviteCode;

use QUI;
use QUI\Utils\Grid;
use QUI\Utils\Security\Orthos;
use QUI\InviteCode\Exception\InviteCodeException;

/**
 * Class Handler
 *
 * Main Invite Code handler
 */
class Handler
{
    /**
     * Permissions
     */
    const PERMISSION_VIEW      = 'quiqqer.invitecode.view';
    const PERMISSION_CREATE    = 'quiqqer.invitecode.create';
    const PERMISSION_SEND_MAIL = 'quiqqer.invitecode.send_mail';
    const PERMISSION_DELETE    = 'quiqqer.invitecode.delete';

    /**
     * InviteCode runtime cache
     *
     * @var InviteCode[]
     */
    protected static $inviteCodes = array();

    /**
     * Get InviteCode
     *
     * @param int $id
     * @return InviteCode
     */
    public static function getInviteCode($id)
    {
        if (isset(self::$inviteCodes[$id])) {
            return self::$inviteCodes[$id];
        }

        self::$inviteCodes[$id] = new InviteCode($id);

        return self::$inviteCodes[$id];
    }

    /**
     * Get InviteCode by its actual code
     *
     * @param string $code
     * @return InviteCode
     *
     * @throws InviteCodeException
     */
    public static function getInviteCodeByCode($code)
    {
        $result = QUI::getDataBase()->fetch(array(
            'select' => array(
                'id'
            ),
            'from'   => self::getTable(),
            'where'  => array(
                'code' => $code
            ),
            'limit'  => 1
        ));

        if (empty($result)) {
            throw new InviteCodeException(array(
                'quiqqer/invitecode',
                'exception.handler.code_not_found',
                array(
                    'code' => $code
                )
            ), 404);
        }

        return self::getInviteCode($result[0]['id']);
    }

    /**
     * Create new InviteCode
     *
     * @param array $data
     * @return InviteCode
     *
     * @throws \Exception
     */
    public static function createInviteCode($data)
    {
        $Now = new \DateTime();

        $inviteCode = array(
            'title'      => empty($data['title']) ? '' : $data['title'],
            'createDate' => $Now->format('Y-m-d H:i:s'),
            'code'       => CodeGenerator::generate()
        );

        if (!empty($data['validUntil'])) {
            $ValidUntil                   = new \DateTime($data['validUntil']);
            $inviteCode['validUntilDate'] = $ValidUntil->format('Y-m-d H:i:s');
        }

        if (!empty($data['email'])) {
            try {
                QUI::getUsers()->getUserByMail($data['email']);

                throw new InviteCodeException(array(
                    'quiqqer/invitecode',
                    'exception.handler.user_already_exists',
                    array(
                        'email' => $data['email']
                    )
                ));
            } catch (QUI\Users\Exception $Exception) {
                if ($Exception->getCode() !== 404) {
                    throw $Exception;
                }
            }

            $inviteCode['email'] = $data['email'];
        }

        QUI::getDataBase()->insert(
            self::getTable(),
            $inviteCode
        );

        return self::getInviteCode(QUI::getPDO()->lastInsertId());
    }

    /**
     * Search InviteCodes
     *
     * @param array $searchParams
     * @param bool $countOnly (optional) - get result count only [default: false]
     * @return InviteCode[]|int
     */
    public static function search($searchParams, $countOnly = false)
    {
        $inviteCodes = array();
        $Grid        = new Grid($searchParams);
        $gridParams  = $Grid->parseDBParams($searchParams);

        $binds = array();
        $where = array();

        if ($countOnly) {
            $sql = "SELECT COUNT(*)";
        } else {
            $sql = "SELECT id";
        }

        $sql .= " FROM `" . self::getTable() . "`";

        if (!empty($searchParams['search'])) {
            $searchColumns = array(
                'id',
                'code',
                'email'
            );

            $whereOr = array();

            foreach ($searchColumns as $searchColumn) {
                $whereOr[] = '`' . $searchColumn . '` LIKE :search';
            }

            if (!empty($whereOr)) {
                $where[] = '(' . implode(' OR ', $whereOr) . ')';

                $binds['search'] = array(
                    'value' => '%' . $searchParams['search'] . '%',
                    'type'  => \PDO::PARAM_STR
                );
            }
        }

        // build WHERE query string
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        // ORDER
        if (!empty($searchParams['sortOn'])
        ) {
            $sortOn = Orthos::clear($searchParams['sortOn']);
            $order  = "ORDER BY " . $sortOn;

            if (isset($searchParams['sortBy']) &&
                !empty($searchParams['sortBy'])
            ) {
                $order .= " " . Orthos::clear($searchParams['sortBy']);
            } else {
                $order .= " ASC";
            }

            $sql .= " " . $order;
        } else {
            $sql .= " ORDER BY id DESC";
        }

        // LIMIT
        if (!empty($gridParams['limit'])
            && !$countOnly
        ) {
            $sql .= " LIMIT " . $gridParams['limit'];
        } else {
            if (!$countOnly) {
                $sql .= " LIMIT " . (int)20;
            }
        }

        $Stmt = QUI::getPDO()->prepare($sql);

        // bind search values
        foreach ($binds as $var => $bind) {
            $Stmt->bindValue(':' . $var, $bind['value'], $bind['type']);
        }

        try {
            $Stmt->execute();
            $result = $Stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $Exception) {
            QUI\System\Log::addError(
                self::class . ' :: search() -> ' . $Exception->getMessage()
            );

            return array();
        }

        if ($countOnly) {
            return (int)current(current($result));
        }

        foreach ($result as $row) {
            $inviteCodes[] = self::getInviteCode($row['id']);
        }

        return $inviteCodes;
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
     * Get Registration site
     *
     * @return QUI\Projects\Site|false
     */
    public static function getRegistrationSite()
    {
        $Conf    = QUI::getPackage('quiqqer/invitecode')->getConfig();
        $regSite = $Conf->get('settings', 'registrationSite');

        if (empty($regSite)) {
            return false;
        }

        try {
            return QUI\Projects\Site\Utils::getSiteByLink($regSite);
        } catch (\Exception $Exception) {
            return false;
        }
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
