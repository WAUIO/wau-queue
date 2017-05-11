<?php

require_once dirname(dirname(dirname(__FILE__))) . "/podio-php/PodioAPI.php";

Podio::setup('CLIENT_ID', 'CLIENT_SECRET');
Podio::authenticate('password', [
    'username' => 'email.mail.co',
    'password' => 'your_password',
]);