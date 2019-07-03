<?php

namespace SimpleSAML\Module\mzk\Auth\Process;

class AffilationFilterLDAP extends \SimpleSAML\Auth\ProcessingFilter {

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
		if ($attributes["ldap.source"][0] == "employees") {
			$attributes["uid"] = $attributes["id"];
			$attributes["eduPersonAffiliation"][] = "staff";
			$attributes["eduPersonScopedAffiliation"][] = "staff@mzk.cz";
			$attributes["eduPersonAffiliation"][] = "member";
			$attributes["eduPersonScopedAffiliation"][] = "member@mzk.cz";
			$attributes["eduPersonAffiliation"][] = "employee";
			$attributes["eduPersonScopedAffiliation"][] = "employee@mzk.cz";
			$attributes["mzkPermission"][] = "wifi";
			$attributes["eduPersonEntitlement"][] = "urn:mace:dir:entitlement:common-lib-terms";
		}
		if (in_array('mzkWifiAccount', $attributes['objectClass'])) {
			$attributes["mzkPermission"][] = "wifi";
		}
		if (in_array('mzkProxyAccount', $attributes['objectClass'])) {
			$attributes["eduPersonAffiliation"][] = "member";
			$attributes["eduPersonScopedAffiliation"][] = "member@mzk.cz";
			$attributes["eduPersonEntitlement"][] = "urn:mace:dir:entitlement:common-lib-terms";
		}
	}

}
