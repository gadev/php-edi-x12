<?php

namespace BrodSolutions\Edi\Segments;

use BrodSolutions\Edi\Segment;

class L1Segment extends Segment
{
    public $segmentMapping = [
        0 => parent::EDI_QUALIFIER_KEY, // Line Item Rates & Charges
        1 => 'lading_line_item_number',
        2 => 'freight_rate',
        3 => 'rate_qualifier',
        4 => 'charge',
        5 => 'advances',
        6 => 'prepaid_amount',
        7 => 'rate_combination_point_code',
        8 => 'special_charge_code',
        9 => 'rate_class_code',
        10 => 'entitlement_code',
        11 => 'charge_method_of_payment',
        12 => 'special_charge_description',
        13 => 'tariff_application_code',
        14 => 'declared_value',
        15 => 'value_qualifier',
        16 => 'lading_liability_code',
        17 => 'billed_quantity',
        18 => 'billed_qualifier',
        19 => 'percent',
        20 => 'currency_code',
        21 => 'amount',
    ];
}
