<?php

namespace BrodSolutions\Edi\Segments;

use BrodSolutions\Edi\Segment;

class SvcSegment extends Segment
{
    public $segmentMapping = [
        0 => parent::EDI_QUALIFIER_KEY,
        1 => 'qualifier',
        2 => 'id',
        3 => 'amount_charged',
        4 => 'amount_paid',
        5 => 'revenue_code',
        6 => 'units',
    ];
}
