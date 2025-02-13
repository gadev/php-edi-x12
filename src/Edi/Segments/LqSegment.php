<?php

namespace BrodSolutions\Edi\Segments;

use BrodSolutions\Edi\Segment;

class LqSegment extends Segment
{
    public $segmentMapping = [
        0 => parent::EDI_QUALIFIER_KEY,
        1 => 'qualifier_code',
        2 => 'remark_code',
    ];
}
