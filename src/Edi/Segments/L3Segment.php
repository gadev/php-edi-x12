<?php

namespace BrodSolutions\Edi\Segments;

use BrodSolutions\Edi\Segment;

class L3Segment extends Segment
{
    public $segmentMapping = [
        0 => parent::EDI_QUALIFIER_KEY, // Line Item Weight & Charges
        1 => 'weight',
        2 => 'weight_qualifier',
        3 => 'freight_rate',
        4 => 'rate_value_qualifier',
        5 => 'charge',
        6 => 'advances',
        7 => 'prepaid_amount',
        8 => 'special_charge_or_allowance_code',
        9 => 'volume',
        10 => 'volume_unit_qualifier',
        11 => 'lading_quantity',
        12 => 'weight_unit_code',
        13 => 'tariff_number',
        14 => 'declared_value',
        15 => 'rate_value_qualifier',
    ];
}
