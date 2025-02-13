<?php

namespace BrodSolutions\Edi\Segments;

use BrodSolutions\Edi\Segment;

class L0Segment extends Segment
{
    public $segmentMapping = [
        0 => parent::EDI_QUALIFIER_KEY, // Line Item Quantity & Weight
        1 => 'lading_line_item_number',
        2 => 'billed_quantity',
        3 => 'billed_qualifier',
        4 => 'weight',
        5 => 'weight_qualifier',
        6 => 'volume',
        7 => 'volume_unit_qualifier',
        8 => 'lading_quantity',
        9 => 'packaging_form_code',
        10 => 'dunnage_description',
        11 => 'weight_unit_code',
        12 => 'type_of_service_code',
        13 => 'quantity',
        14 => 'packaging_form_code',
        15 => 'yes_no_condition_or_response_code',
    ];
}
