<?php

namespace SimpleSAML\Module\mzk\Auth\Process;

class MetadataRequestedAttributeLimiter extends \SimpleSAML\Auth\ProcessingFilter {

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

    public function process(&$request) {
        $requestedAttributes = $this->getRequestedAttributes($request);
        if (empty($requestedAttributes)) {
            return;
        }
        $attributes =& $request['Attributes'];
        foreach ($attributes as $name => $values) {
            if(!in_array($name, $requestedAttributes, TRUE)) {
                unset($attributes[$name]);
            }
        }
    }

    protected function getRequestedAttributes($request) {
        if (isset($request['SPMetadata']['attributes']) && !empty($request['SPMetadata']['attributes'])) {
            return $request['SPMetadata']['attributes'];
        }
        $isRefeds = false;
        if (isset($request['SPMetadata']['EntityAttributes']) && isset($request['SPMetadata']['EntityAttributes']
            ['http://macedir.org/entity-category'])) {
            $categories = array_values($request['SPMetadata']['EntityAttributes']
                ['http://macedir.org/entity-category']);
            $isRefeds = in_array('http://refeds.org/category/research-and-scholarship', $categories, TRUE);
        }
        if ($isRefeds) {
            return [
                'urn:oid:1.3.6.1.4.1.5923.1.1.1.6',
                'urn:oid:0.9.2342.19200300.100.1.3',
                'urn:oid:1.3.6.1.4.1.5923.1.1.1.10',
                'urn:oid:2.16.840.1.113730.3.1.241',
                'urn:oid:2.5.4.42',
                'urn:oid:2.5.4.4',
            ];
        }
        return [];
    }

}
