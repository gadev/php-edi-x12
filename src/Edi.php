<?php

namespace BrodSolutions;

use BrodSolutions\Edi\Document;

/**
 * A class to parse ASC X12 EDI documents
 */
class Edi
{

    public const SEGMENT_TERMINATOR_POSITION = 105;
    public const SUBELEMENT_SEPARATOR_POSITION = 104;
    public const ELEMENT_SEPARATOR_POSITION = 3;

    /**
     * @TODO:
     * BPR (?)
     * TRN (?)
     * LQ
     */

    public static $segmentMapping = [
        'AMT' => 'BrodSolutions\Edi\Segments\AmtSegment',
        'B4' => 'BrodSolutions\Edi\Segments\B4Segment',
        'BCH' => 'BrodSolutions\Edi\Segments\BchSegment',
        'BEG' => 'BrodSolutions\Edi\Segments\BegSegment',
        'BPR' => 'BrodSolutions\Edi\Segments\BprSegment',
        'CAS' => 'BrodSolutions\Edi\Segments\CasSegment',
        'CLP' => 'BrodSolutions\Edi\Segments\ClpSegment',
        'CSH' => 'BrodSolutions\Edi\Segments\CshSegment',
        'CTB' => 'BrodSolutions\Edi\Segments\CtbSegment',
        'CTT' => 'BrodSolutions\Edi\Segments\CttSegment',
        'CUR' => 'BrodSolutions\Edi\Segments\CurSegment',
        'DTM' => 'BrodSolutions\Edi\Segments\DtmSegment',
        'FOB' => 'BrodSolutions\Edi\Segments\FobSegment',
        'GE' => 'BrodSolutions\Edi\Segments\GeSegment',
        'GS' => 'BrodSolutions\Edi\Segments\GsSegment',
        'IEA' => 'BrodSolutions\Edi\Segments\IeaSegment',
        'ISA' => 'BrodSolutions\Edi\Segments\IsaSegment',
        'ITD' => 'BrodSolutions\Edi\Segments\ItdSegment',
        'MSG' => 'BrodSolutions\Edi\Segments\MsgSegment',
        'L0' => 'BrodSolutions\Edi\Segments\L0Segment',
        'L1' => 'BrodSolutions\Edi\Segments\L1Segment',
        'L3' => 'BrodSolutions\Edi\Segments\L3Segment',
        'LQ' => 'BrodSolutions\Edi\Segments\LqSegment',
        'LX' => 'BrodSolutions\Edi\Segments\LXSegment',
        'N1' => 'BrodSolutions\Edi\Segments\N1Segment',
        'N2' => 'BrodSolutions\Edi\Segments\N2Segment',
        'N3' => 'BrodSolutions\Edi\Segments\N3Segment',
        'N4' => 'BrodSolutions\Edi\Segments\N4Segment',
        'N9' => 'BrodSolutions\Edi\Segments\N9Segment',
        'NM1' => 'BrodSolutions\Edi\Segments\Nm1Segment',
        'PER' => 'BrodSolutions\Edi\Segments\PerSegment',
        'PID' => 'BrodSolutions\Edi\Segments\PidSegment',
        'PO1' => 'BrodSolutions\Edi\Segments\Po1Segment',
        'PO4' => 'BrodSolutions\Edi\Segments\Po4Segment',
        'POC' => 'BrodSolutions\Edi\Segments\PocSegment',
        'Q2' => 'BrodSolutions\Edi\Segments\Q2Segment',
        'R4' => 'BrodSolutions\Edi\Segments\R4Segment',
        'REF' => 'BrodSolutions\Edi\Segments\RefSegment',
        'SAC' => 'BrodSolutions\Edi\Segments\SacSegment',
        'SE' => 'BrodSolutions\Edi\Segments\SeSegment',
        'SLN' => 'BrodSolutions\Edi\Segments\SlnSegment',
        'SVC' => 'BrodSolutions\Edi\Segments\SvcSegment',
        'ST' => 'BrodSolutions\Edi\Segments\StSegment',
        'TC2' => 'BrodSolutions\Edi\Segments\Tc2Segment',
        'TD1' => 'BrodSolutions\Edi\Segments\Td1Segment',
        'TD4' => 'BrodSolutions\Edi\Segments\Td4Segment',
        'TD5' => 'BrodSolutions\Edi\Segments\Td5Segment',
        'TRN' => 'BrodSolutions\Edi\Segments\TrnSegment',
        'MTX' => 'BrodSolutions\Edi\Segments\MtxSegment',
    ];

    /**
     * Parse an EDI document. Data will be returned as an array of instances of
     * EDI\Document. Document should contain exactly one ISA/IEA envelope.
     */
    public static function parse($res)
    {
        if (!$res) {
            throw new \Exception('No resource or string passed to parse()');
        }

        $documents = array();
        if (is_resource($res)) {
//            $res = $data;
            $meta = stream_get_meta_data($res);
            if (!$meta['seekable']) {
                throw new \Exception('Stream is not seekable');
            }

            throw new \Exception('Not implemented!');
        } else {
            $data = $res;
            // treat as string.
            if (strcasecmp(substr($data, 0, 3), 'ISA') != 0) {
                throw new \Exception('ISA segment not found in data stream');
            }

            $segment_terminator = substr($data, self::SEGMENT_TERMINATOR_POSITION, 1);
            $element_separator = substr($data, self::ELEMENT_SEPARATOR_POSITION, 1);
            $subelement_separator = substr($data, self::SUBELEMENT_SEPARATOR_POSITION, 1);

            $raw_segments = explode($segment_terminator, $data);
        }

        $current_isa = null;
        $current_gs = null;
        $current_st = null;

        foreach ($raw_segments as $segment) {
            $elements = array_map('trim', explode($element_separator, $segment));
            $identifier = strtoupper($elements[0]);

            // only inspect each element if the subelement separator is present in the string
            if (str_contains($segment, $subelement_separator) && ! in_array($identifier, ['ISA', 'SVC'])) {
                foreach ($elements as &$element) {
                    if (str_contains($segment, $subelement_separator)) {
                        $element = explode($subelement_separator, $element);
                    }
                }
                unset($element);
            }

            /* This is a ginormous switch statement, but necessarily so.
            * The idea is that the parser will, for each transaction set
            * in the ISA envelope, create a new Document instance with
            * the containing ISA and GS envelopes copied in.
            */
            switch ($identifier) {
                case 'ISA':
                    $current_isa = array('isa' => $elements);
                    break;
                case 'GS':
                    $current_gs = array('gs' => $elements);
                    break;
                case 'ST':
                    $current_st = array('st' => $elements);
                    break;
                case 'SVC':
                    $data = explode($subelement_separator, $elements[1]);
                    $elements[1] = $data[0];
                    array_splice($elements, 2, 0, $data[1]);
                    if(count($data) > 2) {
                        $elements['mods'] = array_splice($data, 2);
                    }

                    $current_st['segments'][] = $elements;
                    break;
                case 'SE':
                    assert($current_gs !== null, 'GS data structure isset');
                    $current_st['se'] = $elements;
                    if (!isset($current_gs['txn_sets'])) {
                        $current_gs['txn_sets'] = array();
                    }
                    $current_gs['txn_sets'][] = $current_st;
                    $current_st = null;
                    break;
                case 'GE':
                    assert($current_isa !== null, 'ST data structure isset');
                    $current_gs['ge'] = $elements;
                    if (!isset($current_isa['func_groups'])) {
                        $current_isa['func_groups'] = array();
                    }
                    $current_isa['func_groups'][] = $current_gs;
                    $current_gs = null;
                    break;
                case 'IEA':
                    $current_isa['iea'] = $elements;
                    foreach ($current_isa['func_groups'] as $gs) {
                        foreach ($gs['txn_sets'] as $st) {
                            $segments = array_merge(
                                array(
                                    $current_isa['isa'],
                                    $gs['gs'],
                                    $st['st']
                                ),
                                $st['segments'],
                                array(
                                    $st['se'],
                                    $gs['ge'],
                                    $current_isa['iea']
                                )
                            );
                            $document = new Document($segments);
                            $documents[] = $document;
                        }
                    }
                    break;
                default:
                    if (!isset($current_st['segments'])) {
                        $current_st['segments'] = array();
                    }

                    $current_st['segments'][] = $elements;
                    break;
            }
        }

        return $documents;
    }

    /**
     * @param $file
     * @return array
     * @throws \Exception
     */
    public static function parseFile($file)
    {
        $contents = file_get_contents($file);
        return self::parse($contents);
    }
}
