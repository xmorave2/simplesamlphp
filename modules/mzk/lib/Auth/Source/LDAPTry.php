<?php

namespace SimpleSAML\Module\mzk\Auth\Source;

class LDAPTry extends \SimpleSAML\Module\core\Auth\UserPassBase {

    private $servers = [];

    /**
     * Constructor for this authentication source.
     *
     * @param array $info  Information about this authentication source.
     * @param array $config  Configuration.
     */
    public function __construct($info, $config) {
        assert('is_array($info)');
        assert('is_array($config)');
        /* Call the parent constructor first, as required by the interface. */
        parent::__construct($info, $config);

        if (!(isset($config['sources']) && $config['sources'] && is_array($config['sources']))) {
            throw new \SimpleSAML\Error\Exception("Missing sources in authconfig");
        }
        $authsource = \SimpleSAML\Configuration::getConfig('authsources.php');

        foreach ($config['sources'] as $source) {
            if (!$authsource->hasValue($source)) {
                throw new \SimpleSAML\Error\Exception(
                    $this->title . 'Authsource [' . $source . '] '
                    . 'defined in filter parameters not found in authsources.php'
                );
            }
            $authSourceConfig = $authsource->getConfigItem($source)->toArray();
            $ldap = new \SimpleSAML\Module\ldap\ConfigHelper(
                $authSourceConfig,
                'Authentication source ' . var_export($this->authId, true)
            );
            $this->servers[$source] = [
                'id'   => $authSourceConfig['id'],
                'ldap' => $ldap
            ];
        }
    }

    /**
     * Attempt to log in using the given username and password.
     *
     * @param string $username  The username the user wrote.
     * @param string $password  The password the user wrote.
     * param array $sasl_arg  Associative array of SASL options
     * @return array  Associative array with the users attributes.
     */
    protected function login($username, $password, array $sasl_args = NULL) {
        assert('is_string($username)');
        assert('is_string($password)');
        $passwords = [ $password, strtolower($password) ];
        foreach($passwords as $pwd) {
            foreach($this->servers as $source => $server) {
                try {
                    $result = $server['ldap']->login($username, $pwd, $sasl_args);
                    $result['id'] = $result[$server['id']];
                    $result['ldap.source'] = [ $source ];
                    return $result;
                } catch (\Exception $ex) {
                }
            }
        }
        throw new \SimpleSAML\Error\Error('WRONGUSERPASS');
    }

}
