<?php
namespace SimpleSAML\Module\xcncip2\Auth\Source;

class XCNCIP2 extends \SimpleSAML\Module\core\Auth\UserPassBase {

	protected $url;

	protected $eppnScope;

	protected $trustSSLHost;

	protected $certificateAuthority;

	protected $eduPersonScopedAffiliation;

	protected $toAgencyId;

	protected $fromAgencyId;

	protected $needsUsername;

	protected $organizationName;

	protected $proxyServer;

	public function __construct($info, &$config) {
		parent::__construct($info, $config);

		$this->url = $config['url'];
		$this->eppnScope = $config['eppnScope'];

		if(empty($this->eppnScope)) {
			throw new \SimpleSAML\Error\Exception('Cannot have eppnScope empty! .. You have to set it in authsource.php');
		}

		$this->trustSSLHost = $config['trustSSLHost'];
		$this->certificateAuthority = $config['certificateAuthority'];
		if (isset($config['eduPersonScopedAffiliation'])) {
			$this->eduPersonScopedAffiliation = array($config['eduPersonScopedAffiliation']);
		} else {
			$this->eduPersonScopedAffiliation = array('member@' . $this->eppnScope);
		}

		$this->toAgencyId = $config['toAgencyId'];
		$this->fromAgencyId = $config['fromAgencyId'];
		$this->organizationName = $config['organizationName'];

		$this->needsUsername = isset($config['needsUsername']) ? $config['needsUsername'] : false;

		$this->excludeAcademicDegrees = isset($config['excludeAcademicDegrees']) ? $config['excludeAcademicDegrees'] : false;
		$config = \SimpleSAML\Configuration::getConfig();
		$this->proxyServer = $config->getValue('proxy');
	}

	public function login($username, $password) {
		$requestBody = $this->getLookupUserRequest($username, $password);
		$response = $this->doRequest($requestBody, $username);
		$id = $response->xpath(
				'ns1:LookupUserResponse/ns1:UserId/ns1:UserIdentifierValue'
				);
		if (empty($id)) {
			throw new \SimpleSAML\Error\Error('WRONGUSERPASS');
		}
		$userId = trim((String) $response->xpath(
				'ns1:LookupUserResponse/ns1:UserId/ns1:UserIdentifierValue')[0]);
		if(empty($userId)) {
			throw new Exception('UserId was not found - cannot continue without user\'s Institution Id Number');
		}
		$agencyId = trim((String) $response->xpath(
				'ns1:LookupUserResponse/ns1:UserId/ns1:AgencyId')[0]);
		$electronicAddresses = $response->xpath(
				'ns1:LookupUserResponse/ns1:UserOptionalFields/ns1:UserAddressInformation/ns1:ElectronicAddress'
				);
		$mail = $tel = null;
		foreach ($electronicAddresses as $recent) {
			if (strpos((String) $recent->xpath('ns1:ElectronicAddressType')[0], 'mail') !== FALSE) {
				$mail = trim((String) $recent->xpath('ns1:ElectronicAddressData')[0]);
			} else if (strpos((String) $recent->xpath('ns1:ElectronicAddressType')[0], 'tel') !== FALSE) {
				$tel = trim((String) $recent->xpath('ns1:ElectronicAddressData')[0]);
			}
		}
		$firstname = trim((String) $response->xpath(
				'ns1:LookupUserResponse/ns1:UserOptionalFields/ns1:NameInformation/' .
				'ns1:PersonalNameInformation/ns1:StructuredPersonalUserName/ns1:GivenName')[0]);
		$lastname = trim((String) $response->xpath(
				'ns1:LookupUserResponse/ns1:UserOptionalFields/ns1:NameInformation/' .
				'ns1:PersonalNameInformation/ns1:StructuredPersonalUserName/ns1:Surname')[0]);
		$unstructuredName = trim((String) $response->xpath(
				'ns1:LookupUserResponse/ns1:UserOptionalFields/ns1:NameInformation/' .
				'ns1:PersonalNameInformation/ns1:UnstructuredPersonalUserName')[0]);
		$academicDegrees = [];
		if (! empty($unstructuredName)) {
			// Assume the last word is firstname, all other words are part of lastname
			$names = preg_split('/[\s,]+/', $unstructuredName);
			// Look for academic degrees to extract those
			$i = 0;
			foreach($names as $name) {
				if (preg_match('/\w+\.|^et$/', $name)) {
					$academicDegrees[] = $name;
					unset($names[$i]);
				}
				++$i;
			}
			if (empty($firstname)) {
				$firstname = $names[count($names) - 1];
			}
			unset($names[count($names) - 1]);
			if (empty($lastname)) {
				$lastname = implode(' ', $names);
			}
		}
		$fullname = trim($firstname . ' ' . $lastname);
		if (! $this->excludeAcademicDegrees) {
			$academicDegreesWordy = array_reduce($academicDegrees, function($a, $b) { return $a . ' ' . $b; });
			if (! empty($academicDegreesWordy)) {
				$fullname .= ' ' . $academicDegreesWordy;
			}
		}
		$providedAttributes = array(
				'eduPersonPrincipalName' => array( $userId . '@' . $this->eppnScope ),
				'eduPersonUniqueId' => array( $userId . '@' . $this->eppnScope ),
				'eduPersonScopedAffiliation' => $this->eduPersonScopedAffiliation,
				'userLibraryId' => array( $userId ),
				'givenName' => empty( $firstname ) ? [] : array( $firstname ),
				'sn' => empty( $lastname ) ? [] : array( $lastname ),
				'cn' => empty( $fullname ) ? [] : array( $fullname ),
				'o' => empty( $this->organizationName ) ? [] : array( $this->organizationName ),
				'userHomeLibrary' => empty( $agencyId ) ? [] : array( $agencyId ),
		);
		if ($mail !== null) {
			$providedAttributes['mail'] = array( $mail );
		}
		return $providedAttributes;
	}

	protected function doRequest($body, $username) {
		$req = curl_init($this->url);
		curl_setopt($req, CURLOPT_POST, 1);
		curl_setopt($req, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($req, CURLOPT_HTTPHEADER, array(
					'Content-type: application/xml; charset=utf-8',
					));
		curl_setopt($req, CURLOPT_POSTFIELDS, $body);
		if ($this->proxyServer) {
			curl_setopt($req, CURLOPT_PROXY, $this->proxyServer);
		}

		if ($this->trustSSLHost) {
			curl_setopt($req, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($req, CURLOPT_SSL_VERIFYPEER, 0);
		} else {
			curl_setopt($req, CURLOPT_VERBOSE, 1);
			curl_setopt($req, CURLOPT_CERTINFO, 1);

			if (!empty($this->certificateAuthority))
				curl_setopt($req, CURLOPT_CAINFO, $this->certificateAuthority);
		}

		// Do not log the real NCIP request body, it contains private credentials!!!!
		\SimpleSAML\Logger::info("NCIP request sent to $this->url: ". $this->getLookupUserRequest($username, "********"));

		$response = curl_exec($req);
		\SimpleSAML\Logger::info("NCIP response: ". $response);
		$result = simplexml_load_string($response);

		if (is_a($result, 'SimpleXMLElement')) {
			$result->registerXPathNamespace('ns1', 'http://www.niso.org/2008/ncip');
			return $result;
		} else {
			throw new \SimpleSAML\Error\Exception("Problem parsing XML");
		}
	}

	protected function getLookupUserRequest($username, $password) {
		return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
			'<ns1:NCIPMessage xmlns:ns1="http://www.niso.org/2008/ncip" ' .
			'ns1:version="http://www.niso.org/schemas/ncip/v2_0/imp1/' .
			'xsd/ncip_v2_0.xsd">' .
			'<ns1:LookupUser>' .
			'<ns1:InitiationHeader>' .
			'<ns1:FromAgencyId>' .
			'<ns1:AgencyId ns1:Scheme="http://www.niso.org/ncip/v1_0/schemes/agencyidtype/agencyidtype.scm">' .
			$this->fromAgencyId .
			'</ns1:AgencyId>' .
			'</ns1:FromAgencyId>' .
			'<ns1:ToAgencyId>' .
			'<ns1:AgencyId ns1:Scheme="http://www.niso.org/ncip/v1_0/schemes/agencyidtype/agencyidtype.scm">' .
			$this->toAgencyId .
			'</ns1:AgencyId>' .
			'</ns1:ToAgencyId>' .
			'</ns1:InitiationHeader>' .
			'<ns1:AuthenticationInput>' .
			'<ns1:AuthenticationInputData>' .
			htmlspecialchars($username) .
			'</ns1:AuthenticationInputData>' .
			'<ns1:AuthenticationDataFormatType>' .
			'text/plain' .
			'</ns1:AuthenticationDataFormatType>' .
			'<ns1:AuthenticationInputType>' .
			( $this->needsUsername ? 'Username' : 'User Id') .
			'</ns1:AuthenticationInputType>' .
			'</ns1:AuthenticationInput>' .
			'<ns1:AuthenticationInput>' .
			'<ns1:AuthenticationInputData>' .
			htmlspecialchars($password) .
			'</ns1:AuthenticationInputData>' .
			'<ns1:AuthenticationDataFormatType>' .
			'text/plain' .
			'</ns1:AuthenticationDataFormatType>' .
			'<ns1:AuthenticationInputType>' .
			'Password' .
			'</ns1:AuthenticationInputType>' .
			'</ns1:AuthenticationInput>' .
			'<ns1:UserElementType ns1:Scheme="http://www.niso.org/ncip/v1_0/schemes/userelementtype/userelementtype.scm">Name Information</ns1:UserElementType>' .
			'<ns1:UserElementType ns1:Scheme="http://www.niso.org/ncip/v1_0/schemes/userelementtype/userelementtype.scm">User Address Information</ns1:UserElementType>' .
			'<ns1:UserElementType ns1:Scheme="http://www.niso.org/ncip/v1_0/schemes/userelementtype/userelementtype.scm">User Privilege</ns1:UserElementType>' .
			'</ns1:LookupUser>' .
			'</ns1:NCIPMessage>';
	}

	/**
	 * Converts all accent characters to ASCII characters.
	 *
	 * If there are no accent characters, then the string given is just returned.
	 *
	 * @param string $string Text that might have accent characters
	 * @return string Filtered string with replaced "nice" characters.
	 */
	protected function removeAccents($string) {
		return iconv("UTF-8", "ASCII//TRANSLIT", $string);
	}

}
?>
