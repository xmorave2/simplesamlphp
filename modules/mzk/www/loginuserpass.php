<?php

/**
 * This page shows a username/password login form, and passes information from it
 * to the sspmod_core_Auth_UserPassBase class, which is a generic class for
 * username/password authentication.
 *
 * @author Olav Morken, UNINETT AS.
 * @package simpleSAMLphp
 * @version $Id$
 */


if (!array_key_exists('AuthState', $_REQUEST)) {
    throw new \SimpleSAML\Error\BadRequest('Missing AuthState parameter.');
}
$authStateId = $_REQUEST['AuthState'];

/* Retrieve the authentication state. */
$state = \SimpleSAML\Auth\State::loadState($authStateId, \SimpleSAML\Module\mzk\Auth\Source\MultiAuthDefault::STAGEID);

if (array_key_exists('source', $_REQUEST)) {
    $source = $_REQUEST['source'];
    SimpleSAML\Module\mzk\Auth\Source\MultiAuthDefault::delegateAuthentication($source, $state);
}

$source = \SimpleSAML\Auth\Source::getById(SimpleSAML\Module\mzk\Auth\Source\MultiAuthDefault::DEFAULT_SOURCE_ID);
if ($source === NULL) {
    throw new Exception('Could not find authentication source with id ' . $state[\SimpleSAML\Module\core\Auth\UserPassBase::AUTHID]);
}


if (array_key_exists('username', $_REQUEST)) {
    $username = $_REQUEST['username'];
} elseif (isset($state['core:username'])) {
    $username = (string)$state['core:username'];
} else {
    $username = '';
}

if (array_key_exists('password', $_REQUEST)) {
    $password = $_REQUEST['password'];
} else {
    $password = '';
}

if (!empty($_REQUEST['username']) || !empty($password)) {
    /* Either username or password set - attempt to log in. */

    if (array_key_exists('forcedUsername', $state)) {
        $username = $state['forcedUsername'];
    }
    try {
        \SimpleSAML\Module\mzk\Auth\Source\MultiAuthDefault::handleLogin($authStateId, $username, $password);
    } catch (\SimpleSAML\Error\Error $e) {
        // Login failed. Extract error code and parameters, to display the error
        $errorCode = $e->getErrorCode();
        $errorParams = $e->getParameters();
        $state['error'] = [
            'code' => $errorCode,
            'params' => $errorParams
        ];
        $authStateId = \SimpleSAML\Auth\State::saveState($state, \SimpleSAML\Module\mzk\Auth\Source\MultiAuthDefault::STAGEID);
        $queryParams = ['AuthState' => $authStateId];
    }
    if (isset($state['error'])) {
        unset($state['error']);
    }
} else {
    $errorCode = NULL;
}

$globalConfig = \SimpleSAML\Configuration::getInstance();
$t = new \SimpleSAML\XHTML\Template($globalConfig, 'mzk:loginuserpass.php');
$t->data['stateparams'] = array('AuthState' => $authStateId);
if (array_key_exists('forcedUsername', $state)) {
    $t->data['username'] = $state['forcedUsername'];
    $t->data['forceUsername'] = TRUE;
} else {
    $t->data['username'] = $username;
    $t->data['forceUsername'] = FALSE;
}
$t->data['links'] = $source->getLoginLinks();
$t->data['errorcode'] = $errorCode;

if (isset($state['SPMetadata'])) {
    $t->data['SPMetadata'] = $state['SPMetadata'];
} else {
    $t->data['SPMetadata'] = NULL;
}

$t->show();
exit();


?>
