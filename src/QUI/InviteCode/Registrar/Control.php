<?php

/**
 * This file contains QUI\InviteCode\Registrar\Controls
 */

namespace QUI\InviteCode\Registrar;

use QUI;
use QUI\Countries\Controls\Select as CountrySelect;

/**
 * Class Control
 */
class Control extends QUI\Control
{
    /**
     * Control constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = array())
    {
        $this->setAttributes(array(
            'invalidFields' => array(),
            'fields'        => $_POST
        ));

        parent::__construct($attributes);

        $this->addCSSFile(dirname(__FILE__) . '/Control.css');
        $this->addCSSClass('quiqqer-registration');
    }

    /**
     * @return string
     */
    public function getBody()
    {
        $Engine = QUI::getTemplateManager()->getEngine();

        $Engine->assign(array(
            'invalidFields' => $this->getAttribute('invalidFields'),
            'fields'        => $this->getAttribute('fields')
        ));

        return $Engine->fetch(dirname(__FILE__) . '/Control.html');
    }
}
