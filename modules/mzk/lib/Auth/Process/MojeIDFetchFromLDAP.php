<?php

namespace SimpleSAML\Module\mzk\Auth\Process;

class MojeIDFetchFromLDAP extends \SimpleSAML\Module\ldap\Auth\Process\AttributeAddFromLDAP {

    /**
     * Initialize this filter.
     *
     * @param array $config  Configuration information about this filter.
     * @param mixed $reserved  For future use.
     */
    public function __construct($config, $reserved) {
        parent::__construct($config, $reserved);
    }

    /**
     * Add attributes from an LDAP server.
     *
     * @param array &$request  The current request
     */
    public function process(&$request) {
        $attributes = &$request['Attributes'];
        if (isset($attributes['openid.local_id'])) {
            parent::process($request);
            if (!isset($attributes['uidNumber'])) {
                $id  = \SimpleSAML\Auth\State::saveState($request, 'mzk:mojeid_missing');
                $url = \SimpleSAML\Module::getModuleURL('mzk/mojeid_missing.php');
                \SimpleSAML\Utils\HTTP::redirectTrustedURL($url, array('StateId' => $id));
            }
        }
    }

}
