<?php

namespace SimpleSAML\Module\mzk\Auth\Process;

class AttributeLimitBySP extends \SimpleSAML\Auth\ProcessingFilter {

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
		$this->config = $config;
	}


	/**
	 * Add attributes from an LDAP server.
	 *
	 * @param array &$request  The current request
	 */
	public function process(&$request) {
		$entityId = $request['SPMetadata']['entityid'];
		$filter = $this->config['base'];
		if (isset($this->config['entities'][$entityId])) {
			$filterName = $this->config['entities'][$entityId];
			if (isset($this->config['filters'][$filterName])) {
				$filter = array_merge($filter, $this->config['filters'][$filterName]);
			}
		} else if (isset($this->config['filters']['default'])) {
			$filter = array_merge($filter, $this->config['filters']['default']);
		}

		$attributes =& $request['Attributes'];
		foreach ($attributes as $name => $values) {
			if(!in_array($name, $filter, TRUE)) {
				unset($attributes[$name]);
			}
		}
	}

}
