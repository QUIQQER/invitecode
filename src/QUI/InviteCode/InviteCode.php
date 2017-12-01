<?php

namespace QUI\InviteCode;

use QUI;
use QUI\InviteCode\Exception\InviteCodeException;
use QUI\InviteCode\Exception\InviteCodeMailException;

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
    protected $UseDate = null;

    /**
     * Date until the Invite code is valid
     *
     * @var \DateTime
     */
    protected $ValidUntilDate = null;

    /**
     * InviteCode title
     *
     * @var string
     */
    protected $title;

    /**
     * InviteCode constructor.
     *
     * @param int $id - Invite Code ID
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

        $this->id    = (int)$data['id'];
        $this->code  = $data['code'];
        $this->title = $data['title'];

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

        if (!empty($data['validUntilDate'])) {
            $this->ValidUntilDate = new \DateTime($data['validUntilDate']);
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
     * @return \DateTime|null
     */
    public function getUseDate()
    {
        return $this->UseDate;
    }

    /**
     * @return \DateTime|null
     */
    public function getValidUntilDate()
    {
        return $this->ValidUntilDate;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Send this Invite Code via E-Mail
     *
     * @return void
     * @throws InviteCodeMailException
     */
    public function sendViaMail()
    {
        $email = $this->getEmail();

        if (empty($email)) {
            throw new InviteCodeMailException(array(
                'quiqqer/invitecode',
                'exception.invitecode.no_email'
            ));
        }

        $Mailer = new QUI\Mail\Mailer();

        $Mailer->addRecipient($email);

        $Engine = QUI::getTemplateManager()->getEngine();
        $dir    = QUI::getPackage('quiqqer/invitecode')->getDir() . 'templates/';
        $data   = array(
            'code' => $this->getCode()
        );

        $RegistrationSite = Handler::getRegistrationSite();

        if (empty($RegistrationSite)) {
            throw new InviteCodeMailException(array(
                'quiqqer/invitecode',
                'exception.invitecode.no_registration_site'
            ));
        }

        $data['registrationUrl'] = $RegistrationSite->getUrlRewritten();

        $Engine->assign(array(
            'body' => QUI::getLocale()->get(
                'quiqqer/invitecode',
                'mail.invite_code.body',
                $data
            )
        ));

        $Mailer->setSubject(QUI::getLocale()->get(
            'quiqqer/invitecode',
            'mail.invite_code.subject'
        ));
        $Mailer->setBody($Engine->fetch($dir . 'mail.invite_code.html'));
        $Mailer->send();
    }

    /**
     * Permanently delete this InviteCode
     *
     * @return void
     */
    public function delete()
    {
        QUI::getDataBase()->delete(
            Handler::getTable(),
            array(
                'id' => $this->id
            )
        );
    }

    /**
     * Get InviteCode attributes as array
     *
     * @return array
     */
    public function toArray()
    {
        $data = array(
            'id'             => $this->getId(),
            'code'           => $this->getCode(),
            'userId'         => false,
            'username'       => false,
            'email'          => $this->getEmail() ?: false,
            'createDate'     => $this->getCreateDate()->format('Y-m-d H:i:s'),
            'useDate'        => false,
            'validUntilDate' => false,
            'title'          => $this->getTitle() ?: false
        );

        $User = $this->getUser();

        if ($User) {
            $data['userId']   = $User->getId();
            $data['username'] = $User->getName();
        }

        $UseDate = $this->getUseDate();

        if ($UseDate) {
            $data['useDate'] = $UseDate->format('Y-m-d H:i:s');
        }

        $ValidUntilDate = $this->getValidUntilDate();

        if ($ValidUntilDate) {
            $data['validUntilDate'] = $ValidUntilDate->format('Y-m-d H:i:s');
        }

        return $data;
    }
}
