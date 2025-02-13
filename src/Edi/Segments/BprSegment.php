<?php

namespace BrodSolutions\Edi\Segments;

use BrodSolutions\Edi\Segment;

class BprSegment extends Segment
{
    public $segmentMapping = [
        0 => parent::EDI_QUALIFIER_KEY, //adjustments
        1 => 'handling_code',
        2 => 'paid_amount',
        3 => 'credit_or_debit',
        4 => 'payment_method',
    ];
}
