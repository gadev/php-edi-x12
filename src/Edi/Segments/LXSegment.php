<?php

namespace BrodSolutions\Edi\Segments;

use BrodSolutions\Edi\Segment;

class LXSegment extends Segment
{
    public $segmentMapping = [
        0 => parent::EDI_QUALIFIER_KEY,
        1 => 'assigned_number',
    ];
}
