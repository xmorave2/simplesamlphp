<?php

namespace SimpleSAML\Metadata\XML;

class RepublishRequest
{

    const NS = 'http://eduid.cz/schema/metadata/1.0';

    private $targets;

    public function __construct($targets) {
        $this->targets = $targets;
    }

    public function toXML(\DOMElement $parent)
    {
        $doc = $parent->ownerDocument;
        $rr = $doc->createElementNS(self::NS, 'eduidmd:RepublishRequest');
        foreach ($this->targets as $target) {
            $rt = $doc->createElementNS(self::NS, 'eduidmd:RepublishTarget', $target);
            $rr->appendChild($rt);
        }
        $parent->appendChild($rr);
    }

}
