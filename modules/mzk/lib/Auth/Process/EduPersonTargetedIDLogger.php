<?php

namespace SimpleSAML\Module\mzk\Auth\Process;

class EduPersonTargetedIDLogger extends \SimpleSAML\Auth\ProcessingFilter {

    /**
     * The configuration.
     *
     * Associative array of strings.
     */
    private $config = array();


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
        assert('is_array($request)');
        assert('array_key_exists("Attributes", $request)');
        $attributes = & $request['Attributes'];
        $metadata = & $request['SPMetadata'];
        $eduPersonPrincipalName = $attributes['urn:mace:dir:attribute-def:eduPersonPrincipalName'][0];
        $eduPersonTargetedID = $attributes['urn:mace:dir:attribute-def:eduPersonTargetedID'][0];
        $consumerService = $metadata['entityid'];
        \SimpleSAML\Logger::info("$eduPersonPrincipalName ($eduPersonTargetedID) accesses $consumerService");
    }

}
