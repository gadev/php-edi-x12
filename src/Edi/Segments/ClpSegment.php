<?php

namespace BrodSolutions\Edi\Segments;

use BrodSolutions\Edi\Segment;

class ClpSegment extends Segment
{
    public $segmentMapping = [
        0 => parent::EDI_QUALIFIER_KEY, //Claim info
        1 => 'pcn',
        2 => 'status_code',
        3 => 'total_charge',
        4 => 'total_paid',
        5 => 'patient_responsibility',
        6 => 'filling_code',
        7 => 'control_number',
        8 => 'facility_type',
        9 => 'frequency_code',
    ];
}
