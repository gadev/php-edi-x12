<?php

namespace BrodSolutions\Edi\Segments;

use BrodSolutions\Edi\Segment;

class Nm1Segment extends Segment
{
    public $segmentMapping = [
        0 => parent::EDI_QUALIFIER_KEY, //patient name
        1 => 'type', //QC - patient
        2 => 'type_qualifier',
        3 => 'patient_name_l',
        4 => 'patient_name_f',
        5 => 'patient_name_m',
        6 => '',
        7 => 'patient_name_suffix',
        8 => 'identifier_type',
        9 => 'patient_identifier',
    ];
}
