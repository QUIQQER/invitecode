<?php

$Registration = new QUI\FrontendUsers\Controls\Registration([
    'registrars' => [QUI\InviteCode\Registrar\Registrar::class]
]);

$Engine->assign('Registration', $Registration);
