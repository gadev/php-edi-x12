<?php

namespace BrodSolutions\Edi\Segments;

use BrodSolutions\Edi\Segment;

/**
 * Class N4Segment
 * @package BrodSolutions\Edi\Segments
 */
class N4Segment extends Segment
{
    public $segmentMapping = [
        0 => parent::EDI_QUALIFIER_KEY, //Address Information
        1 => 'city', //Free-form text for city name
        2 => 'state', //Code (Standard State/Province) as defined by appropriate government agency
        3 => 'postal_code', //Code defining international postal zone code excluding punctuation and blanks (zip code for United States)
        4 => 'country_code', //Code identifying the country
        5 => 'location_qualifier', //Code identifying type of location
        6 => 'location_identifier', //Code which identifies a specific location
        7 => 'country_subdivision_code' //Code identifying the country subdivision
    ];
}
