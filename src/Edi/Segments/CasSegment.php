<?php

namespace BrodSolutions\Edi\Segments;

use BrodSolutions\Edi\Segment;

class CasSegment extends Segment
{
    public $segmentMapping = [
        0 => parent::EDI_QUALIFIER_KEY, //adjustments
        1 => 'adjustment_group',
        2 => 'adjustment_code',
        3 => 'adjustment_amount',
        4 => 'adjustment_quantity',
    ];
}
