<?php

$config = array(
    'xcncip2' => array(
        'xcncip2:XCNCIP2',
        'fullname' => 'Example library',
        // fullname attr will be Title & heading above the form input if you set in config.php 'theme.use' => 'xcncip2:pretty', so feel free to name your authsource as you wish ;)

        'url' => 'https://ncip.library.example',
        'eduPersonScopedAffiliation' => array('member'),
        'trustSSLHost' => 0,
        'certificateAuthority' => '/etc/ssl/certs/cpk_cacert.pem',
        'redirect.validate' => FALSE,
        'validate.logout' => FALSE,
        'eppnScope' => 'example-library-idp.knihovny.cz',
    ),
);

