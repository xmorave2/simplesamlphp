<?php

namespace SimpleSAML\Module\mzk\Auth\Source;

class MultiAuthDefault extends \SimpleSAML\Auth\Source {

    /**
     * The key of the AuthId field in the state.
     */
    const AUTHID = '\SimpleSAML\Module\mzk\Auth\Source\MultiAuthDefault.AuthId';

    /**
     * The string used to identify our states.
     */
    const STAGEID = '\SimpleSAML\Module\mzk\Auth\Source\MultiAuthDefault.StageId';

    /**
     * The key where the selected source is saved in the session.
     */
    const SESSION_SOURCE = 'multiauthdefault:selectedSource';

    const DEFAULT_SOURCE_ID = 'default';

    /**
     * Constructor for this authentication source.
     *
     * @param array $info     Information about this authentication source.
     * @param array $config     Configuration.
     */
    public function __construct($info, $config) {
        assert('is_array($info)');
        assert('is_array($config)');

        /* Call the parent constructor first, as required by the interface. */
        parent::__construct($info, $config);
    }

    /**
     * Prompt the user with a list of authentication sources.
     *
     * This method saves the information about the configured sources,
     * and redirects to a page where the user must select one of these
     * authentication sources.
     *
     * This method never return. The authentication process is finished
     * in the delegateAuthentication method.
     *
     * @param array &$state     Information about the current authentication.
     */
    public function authenticate(&$state) {
        assert('is_array($state)');

        $state[self::AUTHID] = $this->authId;

        /* Save the $state array, so that we can restore if after a redirect */
        $id = \SimpleSAML\Auth\State::saveState($state, self::STAGEID);

        /* Redirect to the select source page. We include the identifier of the
        saved state array as a parameter to the login form */
        $url = \SimpleSAML\Module::getModuleURL('mzk/loginuserpass.php');
        $params = array('AuthState' => $id);

        \SimpleSAML\Utils\HTTP::redirectTrustedURL($url, $params);

        /* The previous function never returns, so this code is never
        executed */
        assert('FALSE');
    }

    /**
     * Handle login request.
     *
     * This function is used by the login form (core/www/loginuserpass.php) when the user
     * enters a username and password. On success, it will not return. On wrong
     * username/password failure, and other errors, it will throw an exception.
     *
     * @param string $authStateId  The identifier of the authentication state.
     * @param string $username  The username the user wrote.
     * @param string $password  The password the user wrote.
     */
    public static function handleLogin($authStateId, $username, $password) {
        $state = \SimpleSAML\Auth\State::loadState($authStateId, self::STAGEID);
        $state[\SimpleSAML\Module\core\Auth\UserPassBase::AUTHID] = self::DEFAULT_SOURCE_ID;
        $newAuthStateId = \SimpleSAML\Auth\State::saveState($state, \SimpleSAML\Module\core\Auth\UserPassBase::STAGEID);
        \SimpleSAML\Module\core\Auth\UserPassBase::handleLogin($newAuthStateId, $username, $password);
    }

    /**
     * Delegate authentication.
     *
     * This method is called once the user has choosen one authentication
     * source. It saves the selected authentication source in the session
     * to be able to logout properly. Then it calls the authenticate method
     * on such selected authentication source.
     *
     * @param string $authId    Selected authentication source
     * @param array     $state     Information about the current authentication.
     */
    public static function delegateAuthentication($authId, $state) {
        assert('is_string($authId)');
        assert('is_array($state)');

        $as = \SimpleSAML\Auth\Source::getById($authId);
        if ($as === NULL) {
            throw new Exception('Invalid authentication source: ' . $authId);
        }

        /* Save the selected authentication source for the logout process. */
        $session = \SimpleSAML\Session::getSessionFromRequest();
        $session->setData(self::SESSION_SOURCE, $state[self::AUTHID], $authId);

        try {
            $as->authenticate($state);
        } catch (\SimpleSAML\Error\Exception $e) {
            \SimpleSAML\Auth\State::throwException($state, $e);
        } catch (Exception $e) {
            $e = new \SimpleSAML\Error\UnserializableException($e);
            \SimpleSAML\Auth\State::throwException($state, $e);
        }
        \SimpleSAML\Auth\Source::completeAuth($state);
    }

    /**
     * Log out from this authentication source.
     *
     * This method retrieves the authentication source used for this
     * session and then call the logout method on it.
     *
     * @param array &$state     Information about the current logout operation.
     */
    public function logout(&$state) {
        assert('is_array($state)');

        /* Get the source that was used to authenticate */
        $session = \SimpleSAML\Session::getSessionFromRequest();
        $authId = $session->getData(self::SESSION_SOURCE, $this->authId);

        $source = \SimpleSAML\Auth\Source::getById(self::DEFAULT_SOURCE_ID);
        if ($source === NULL) {
            throw new Exception('Invalid authentication source during logout: ' . $source);
        }
        /* Then, do the logout on it */
        $source->logout($state);
    }

}

?>
