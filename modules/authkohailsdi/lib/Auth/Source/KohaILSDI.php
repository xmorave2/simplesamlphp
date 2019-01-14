<?php

namespace SimpleSAML\Module\authkohailsdi\Auth\Source;

use SimpleSAML\Module;

/**
 * Authenticate using Koha ILS-DI API
 *
 * @author Josef Moravec
 * @package SimpleSAMLphp
 */

class KohaILSDI extends \SimpleSAML\Module\core\Auth\UserPassBase
{

    protected $defaultAffiliation;

    protected $affiliationMapping;

    protected $apiUrl;

    protected $domain;

    /**
     * Constructor for this authentication source.
     *
     * @param array $info  Information about this authentication source.
     * @param array $config  Configuration.
     */
    public function __construct($info, $config)
    {
        assert(is_array($info));
        assert(is_array($config));

        // Call the parent constructor first, as required by the interface
        parent::__construct($info, $config);

        $this->apiUrl = $config['ilsdi_api_url'];
        $this->affiliationMapping = $config['affiliation_mapping'];
        $this->defaultAffiliation = $config['default_affiliation'];
        $this->domain = $config['domain'];
    }

    /**
     * Log-in using Koha ILS-DI API
     *
     * @param array &$state  Information about the current authentication.
     */

    protected function login($username, $password)
    {
        $request = "AuthenticatePatron" . "&username="
            . urlencode($username) . "&password=" . urlencode($password);
        $idObj = $this->makeRequest($request);
        $id = $idObj->{'id'};
        if ($id) {
            $rsp = $this->makeRequest(
                "GetPatronInfo&patron_id=$id&show_contact=1"
            );
            \SimpleSAML\Logger::debug(var_export($this->affiliationMapping,true));
            $eduPersonScopedAffiliation = $this->defaultAffiliation;
            if ( is_array($this->affiliationMapping[(string)$rsp->{'categorycode'}])) {
                $eduPersonScopedAffiliation = $this->affiliationMapping[(string)$rsp->{'categorycode'}];
            }
            $profile = [
                'cn' => [ $rsp->{'firstname'} . " " . $rsp->{'surname'} ],
                'eduPersonPrincipalName' => [ $this->addScope($rsp->{'cardnumber'}) ],
                'eduPersonScopedAffiliation' => $this->addScope($eduPersonScopedAffiliation),
                'eduPersonUniqueId' => [$this->addScope($idObj->{'id'})],
                'unstructuredName' => [(string)$idObj->{'id'}],
                'givenName' => [(string)$rsp->{'firstname'}],
                'mail' => [(string)$rsp->{'email'}],
                'sn' => [(string)$rsp->{'surname'}],
                'uid' => [(string)$idObj->{'id'}],
                'displayName' => [$rsp->{'firstname'} . " " . $rsp->{'surname'}],
                'eduPersonAffiliation' => [(string)$rsp->{'categorycode'}],
            ];
            \SimpleSAML\Logger::debug(var_export($profile, true));
            return $profile;
        } else {
            throw new \SimpleSAML\Error\Error('WRONGUSERPASS');
        }
    }

    /**
     * Make Request
     *
     * Makes a request to the Koha ILSDI API
     *
     * @param string $api_query   Query string for request
     * @param string $http_method HTTP method (default = GET)
     *
     * @throws ILSException
     * @return obj
     */
    protected function makeRequest($api_query)
    {
        $url = $this->apiUrl . "?service=" . $api_query;
        $http_headers = [
            "Accept: text/xml",
            "Accept-encoding: plain",
        ];
        try {
            $client = curl_init($url);
            curl_setopt($client, CURLOPT_HTTPHEADER, $http_headers );
            curl_setopt($client, CURLOPT_RETURNTRANSFER , true );
            $result = curl_exec($client);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        if (!$result) {
            throw new \Exception('HTTP error');
        }
        $resultxml = simplexml_load_string($result);
        if (!$resultxml) {
            throw new \Exception(
                "XML is not valid, URL: $url method: $http_method answer: $result."
            );
        }
        return $resultxml;
    }

    protected function addScope( $var )
    {
        if ( is_array($var) ) {
            return array_map(function ($val) { return $val . "@" . $this->domain; }, $var);
        }
        return $var . "@" . $this->domain;
    }
}
