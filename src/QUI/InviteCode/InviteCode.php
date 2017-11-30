<?php

namespace QUI\InviteCode;

use QUI;
use QUI\InviteCode\Exception\InviteCodeException;

/**
 * Class InviteCode
 */
class InviteCode
{
    /**
     * InviteCode ID
     *
     * @var int
     */
    protected $id;

    /**
     * Actual code
     *
     * @var string
     */
    protected $code;

    /**
     * User that is assigned to this Code
     *
     * @var QUI\Users\User
     */
    protected $User = null;

    /**
     * Email address that is asasigned to this Code
     *
     * @var string
     */
    protected $email = null;

    /**
     * Creation Date
     *
     * @var \DateTime
     */
    protected $CreateDate;

    /**
     * Use Date
     *
     * @var \DateTime
     */
    protected $UseDate;

    /**
     * InviteCode constructor.
     *
     * @param int $id - Invite Code ID
     * @return void
     *
     * @throws InviteCodeException
     */
    public function __construct($id)
    {
        $result = QUI::getDataBase()->fetch(array(
            'from'  => Handler::getTable(),
            'where' => array(
                'id' => $id
            )
        ));

        if (empty($result)) {
            throw new InviteCodeException(array(
                'quiqqer/invitecode',
                'exception.invitecode.not_found',
                array(
                    'id' => $id
                )
            ), 404);
        }

        $data = current($result);

        $this->id   = (int)$data['id'];
        $this->code = $data['code'];

        if (!empty($data['userId'])) {
            try {
                $this->User = QUI::getUsers()->get($data['userId']);
            } catch (\Exception $Exception) {
                QUI\System\Log::addWarning(
                    'Could not find User #' . $data['userId'] . ' for Invite Code #' . $this->id . '.'
                );

                QUI\System\Log::writeException($Exception);
            }
        }

        if (!empty($data['email'])) {
            $this->email = $data['email'];
        }

        $this->CreateDate = new \DateTime($data['createDate']);

        if (!empty($data['useDate'])) {
            $this->UseDate = new \DateTime($data['useDate']);
        }
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return QUI\Users\User|null
     */
    public function getUser()
    {
        return $this->User;
    }

    /**
     * @param QUI\Users\User $User
     */
    public function setUser(QUI\Users\User $User)
    {
        $this->User = $User;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email)
    {
        $this->email = $email;
    }

    /**
     * @return \DateTime
     */
    public function getCreateDate()
    {
        return $this->CreateDate;
    }

    /**
     * @return \DateTime
     */
    public function getUseDate()
    {
        return $this->UseDate;
    }
}
