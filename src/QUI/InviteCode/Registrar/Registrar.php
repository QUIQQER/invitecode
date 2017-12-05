<?php

/**
 * This file contains QUI\InviteCode\Registrar
 */

namespace QUI\InviteCode\Registrar;

use QUI;
use QUI\FrontendUsers;
use QUI\FrontendUsers\InvalidFormField;
use QUI\Utils\Security\Orthos;
use QUI\InviteCode\Handler as InviteCodeHandler;
use QUI\InviteCode\Exception\InviteCodeRegistrarException;

/**
 * Class Registrar
 *
 * Registration via Invite code and e-mail address
 *
 * @package QUI\FrontendUsers\Registrars
 */
class Registrar extends FrontendUsers\AbstractRegistrar
{
    /**
     * @param QUI\Interfaces\Users\User $User
     * @return void
     */
    public function onRegistered(QUI\Interfaces\Users\User $User)
    {
        $SystemUser = QUI::getUsers()->getSystemUser();

        /** @var QUI\Users\User $User */
        // set e-mail address
        $User->setAttribute('email', $this->getAttribute('email'));
        $User->save($SystemUser);

        // use Invite Code
        $InviteCode = InviteCodeHandler::getInviteCodeByCode($this->getAttribute('invitecode'));
        $InviteCode->use($User);
    }

    /**
     * @throws FrontendUsers\Exception
     */
    public function validate()
    {
        $lg       = 'quiqqer/invitecode';
        $lgPrefix = 'exception.registrar.';
        $code     = $this->getAttribute('invitecode');

        if (empty($code)) {
            throw new InviteCodeRegistrarException(array(
                $lg,
                $lgPrefix . 'invalid_code'
            ));
        }

        try {
            $InviteCode = InviteCodeHandler::getInviteCodeByCode($code);
        } catch (\Exception $Exception) {
            throw new InviteCodeRegistrarException(array(
                $lg,
                $lgPrefix . 'invalid_code'
            ));
        }

        if ($InviteCode->isUsed()) {
            throw new InviteCodeRegistrarException(array(
                $lg,
                $lgPrefix . 'invalid_code'
            ));
        }

        $email = $this->getAttribute('email');

        if (empty($email)) {
            throw new InviteCodeRegistrarException(array(
                $lg,
                $lgPrefix . 'empty_email'
            ));
        }

        try {
            QUI::getUsers()->getUserByMail($email);

            throw new InviteCodeRegistrarException(array(
                $lg,
                $lgPrefix . 'email_invalid'
            ));
        } catch (\Exception $Exception) {
            // if user not found -> OK
        }

        $inviteCodeEmail = $InviteCode->getEmail();

        if (!empty($inviteCodeEmail)) {
            if (mb_strtolower($inviteCodeEmail) !== mb_strtolower($email)) {
                throw new InviteCodeRegistrarException(array(
                    $lg,
                    $lgPrefix . 'email_invalid'
                ));
            }
        } else {
            if (!Orthos::checkMailSyntax($email)) {
                throw new InviteCodeRegistrarException(array(
                    $lg,
                    $lgPrefix . 'email_invalid'
                ));
            }
        }
    }

    /**
     * Get all invalid registration form fields
     *
     * @return InvalidFormField[]
     */
    public function getInvalidFields()
    {
        $L             = QUI::getLocale();
        $lg            = 'quiqqer/invitecode';
        $lgPrefix      = 'exception.registrar.';
        $code          = $this->getAttribute('invitecode');
        $invalidFields = array();

        if (empty($code)) {
            $invalidFields['invitecode'] = new InvalidFormField(
                'invitecode',
                $L->get($lg, $lgPrefix . 'invalid_code')
            );
        }

        try {
            $InviteCode = InviteCodeHandler::getInviteCodeByCode($code);
        } catch (\Exception $Exception) {
            $invalidFields['invitecode'] = new InvalidFormField(
                'invitecode',
                $L->get($lg, $lgPrefix . 'invalid_code')
            );

            return $invalidFields;
        }

        if ($InviteCode->isUsed()) {
            $invalidFields['invitecode'] = new InvalidFormField(
                'invitecode',
                $L->get($lg, $lgPrefix . 'invalid_code')
            );
        }

        $email = $this->getAttribute('email');

        if (empty($email)) {
            $invalidFields['email'] = new InvalidFormField(
                'email',
                $L->get($lg, $lgPrefix . 'empty_email')
            );
        }

        try {
            QUI::getUsers()->getUserByMail($email);
        } catch (\Exception $Exception) {
            $invalidFields['email'] = new InvalidFormField(
                'email',
                $L->get($lg, $lgPrefix . 'email_invalid')
            );

            return $invalidFields;
        }

        $inviteCodeEmail = $InviteCode->getEmail();

        if (!empty($inviteCodeEmail)) {
            if (mb_strtolower($inviteCodeEmail) !== mb_strtolower($email)) {
                $invalidFields['email'] = new InvalidFormField(
                    'email',
                    $L->get($lg, $lgPrefix . 'email_invalid')
                );
            }
        } else {
            if (!Orthos::checkMailSyntax($email)) {
                $invalidFields['email'] = new InvalidFormField(
                    'email',
                    $L->get($lg, $lgPrefix . 'email_invalid')
                );
            }
        }

        return $invalidFields;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        $data = $this->getAttributes();

        if (!empty($data['email'])) {
            return $data['email'];
        }

        return '';
    }

    /**
     * @return Control
     */
    public function getControl()
    {
        $invalidFields = array();

        if (!empty($_POST['registration'])) {
            $invalidFields = $this->getInvalidFields();
        }

        return new Control(array(
            'invalidFields' => $invalidFields
        ));
    }

    /**
     * Get title
     *
     * @param QUI\Locale $Locale (optional) - If omitted use QUI::getLocale()
     * @return string
     */
    public function getTitle($Locale = null)
    {
        if (is_null($Locale)) {
            $Locale = QUI::getLocale();
        }

        return $Locale->get('quiqqer/invitecode', 'registrar.title');
    }

    /**
     * Get description
     *
     * @param QUI\Locale $Locale (optional) - If omitted use QUI::getLocale()
     * @return string
     */
    public function getDescription($Locale = null)
    {
        if (is_null($Locale)) {
            $Locale = QUI::getLocale();
        }

        return $Locale->get('quiqqer/invitecode', 'registrar.description');
    }

    /**
     * Check if this Registrar can send passwords
     *
     * @return bool
     */
    public function canSendPassword()
    {
        return true;
    }
}
