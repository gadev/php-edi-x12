<?php

namespace BrodSolutions\Edi\Segments;

use BrodSolutions\Edi\Segment;

class TrnSegment extends Segment
{
    public $segmentMapping = [
        0 => parent::EDI_QUALIFIER_KEY,
        1 => 'type_code',
        2 => 'trace_number',
        3 => 'payer_identifier',
    ];
}
