<?php

namespace BrodSolutions\Edi;

use BrodSolutions\Edi;

/**
 * Class SegmentParser
 * @package BrodSolutions\Parse
 */
class SegmentParser
{
    /**
     * @param array $documents
     * @return array
     */
    public static function parseAllSegmentsAsArray(array $documents) {
        $documentParsed = array();
        foreach ($documents as $document) {
            foreach ($document->getSegments() as $segment) {
                $code = $segment[0];

                if (! isset(Edi::$segmentMapping[$code])) {
                    ray("Код $code отсутствует в Edi::\$segmentMapping")->red();
                } else {
                    try {
                        $className = Edi::$segmentMapping[$code];
                        if (! class_exists($className)) {
                            dd("Класс `$className` не существует");
                        }
                    } catch (\Exception $e) {
                        dd('Ошибка при получении класса', $segment, $e);
                    }

                    $documentParsed[] = (new $className)->parse($segment);
                }
            }
        }

        return $documentParsed;
    }

    //This function goes through the parsed EDI and organizes it to be easier to work with in php
    public static function formatParsedArray(array $parsed) : array{
        $formatted = ['header' => [], 'payload' => [], 'footer' => [], 'extra' => []];
        $searching = true;

        //Headers
        //Header segments (ISA, GS)
        $sections = ['ISA', 'GS'];
        foreach($sections as $section){
            foreach($parsed as $key => $item) {
                if($item[Segment::EDI_QUALIFIER_KEY] === $section){
                    $formatted['header'][$section] = $item;
                    unset($parsed[$key]);
                }
            }
        }

        //Footer
        //Footer segments (GE, IEA)
        $sections = ['GE', 'IEA'];
        foreach($sections as $section){
            foreach($parsed as $key => $item) {
                if($item[Segment::EDI_QUALIFIER_KEY] === $section){
                    $formatted['footer'][$section] = $item;
                    unset($parsed[$key]);
                }
            }
        }

        while($searching){
            $section = [];
            $segment = self::getParsedSegment('ST', $parsed);
            if($segment){
                $section['ST'] = $segment['item'];
                unset($parsed[$segment['key']]);

                //Found a transaction set, now we need to find the segments that belong to it
                //Beginning segment
                $beg = self::getParsedSegment('BEG', $parsed);
                if($beg){
                    $section['BEG'] = $beg['item'];
                    unset($parsed[$beg['key']]);
                }

                //BCH
                $bch = self::getParsedSegment('BCH', $parsed);
                if($bch){
                    $section['BCH'] = $bch['item'];
                    unset($parsed[$bch['key']]);
                }

                //Currency
                $cur = self::getParsedSegment('CUR', $parsed);
                if($cur){
                    $section['CUR'] = $cur['item'];
                    unset($parsed[$cur['key']]);
                }

                //Reference
                $section['REF'] = [];
                while($ref = self::getParsedSegment('REF', $parsed)){
                    $section['REF'][] = $ref['item'];
                    unset($parsed[$ref['key']]);
                }

                //FOB
                $section['FOB'] = [];
                while($fob = self::getParsedSegment('FOB', $parsed)){
                    $section['FOB'][] = $fob['item'];
                    unset($parsed[$fob['key']]);
                }

                //Sales requirement
                $csh = self::getParsedSegment('CSH', $parsed);
                if($csh){
                    $section['CSH'] = $csh['item'];
                    unset($parsed[$csh['key']]);
                }

                //SAC Loop
                $section['SAC'] = [];
                while($sac = self::getParsedSegment('SAC', $parsed)){
                    $section['SAC'][] = $sac['item'];
                    unset($parsed[$sac['key']]);
                }

                //ITD
                $section['ITD'] = [];
                while($itd = self::getParsedSegment('ITD', $parsed)){
                    $section['ITD'][] = $itd['item'];
                    unset($parsed[$itd['key']]);
                }

                //DTM
                $section['DTM'] = [];
                while($dtm = self::getParsedSegment('DTM', $parsed)){
                    $section['DTM'][] = $dtm['item'];
                    unset($parsed[$dtm['key']]);
                }

                //TD5
                $section['TD5'] = [];
                //Dont take TD5 segments from the PO1 loop
                while($td5 = self::getParsedSegment('TD5', $parsed, 'PO1')){
                    $section['TD5'][] = $td5['item'];
                    unset($parsed[$td5['key']]);
                }

                //n9 Loop
                $section['N9'] = [];
                while($n9 = self::getParsedSegment('N9', $parsed)){
                    if(!empty($n9['additionalItems'])){
                        $section['N9'][] = [$n9['item'], $n9['additionalItems']];
                    }else $section['N9'][] = [$n9['item']];
                    unset($parsed[$n9['key']]);
                }

                //n1 Loop
                $section['N1'] = [];
                while($n1 = self::getParsedSegment('N1', $parsed)){
                    if(!empty($n1['additionalItems'])){
                        $section['N1'][] = [$n1['item'], $n1['additionalItems']];
                    }else $section['N1'][] = [$n1['item']];
                    unset($parsed[$n1['key']]);
                }

                //Po1 Loop
                $section['PO1'] = [];
                while($po1 = self::getParsedSegment('PO1', $parsed)){
                    if(!empty($po1['additionalItems'])){
                        $section['PO1'][] = [$po1['item'], $po1['additionalItems']];
                    }else $section['PO1'][] = [$po1['item']];
                    unset($parsed[$po1['key']]);
                }

                //POC
                $section['POC'] = [];
                while($poc = self::getParsedSegment('POC', $parsed)){
                    $section['POC'][] = $poc['item'];
                    unset($parsed[$poc['key']]);
                }

                //Ctt loop
                $section['CTT'] = [];
                while($ctt = self::getParsedSegment('CTT', $parsed)){
                    if(!empty($ctt['additionalItems'])){
                        $section['CTT'][] = [$ctt['item'], $ctt['additionalItems']];
                    }else $section['CTT'][] = [$ctt['item']];
                    unset($parsed[$ctt['key']]);
                }

                $se = self::getParsedSegment('SE', $parsed);
                if($se){
                    $section['SE'] = $se['item'];
                    unset($parsed[$se['key']]);
                }

                //Load the rest of the data before the ST segment
                foreach($parsed as $key => $item){
                    if($item[Segment::EDI_QUALIFIER_KEY] === 'ST') break;
                    $section[] = $item;
                    unset($parsed[$key]);
                }

                //Add the section to the formatted array
                $formatted['payload'][] = $section;
            }else $searching = false;
        }

        //Add any remaining segments to the formatted array
        $formatted['extra'] = $parsed;
        return $formatted;
    }

    public static function getParsedSegment(string $segment, array &$parsed, string $stopAt = null) {
        foreach($parsed as $key => $item) {
            //Stop if we reach the end of the current transaction or if we reach a segment we want to stop at
            if(($item[Segment::EDI_QUALIFIER_KEY] === 'ST' && $segment !== 'ST') || ($stopAt !== null && $item[Segment::EDI_QUALIFIER_KEY]) === $stopAt) return null;

            if($item[Segment::EDI_QUALIFIER_KEY] == $segment){
                $additionalItems = [];
                //Gets additional segments that are grouped with the parent segment
                switch($segment){
                    case 'N9':
                        $additionalKey = $key + 1;
                        //Get other segments in this case MTX (may need to add more options)
                        while(isset($parsed[$additionalKey]) && $parsed[$additionalKey][Segment::EDI_QUALIFIER_KEY] === 'MTX'){
                            $additionalItems[] = $parsed[$additionalKey];
                            unset($parsed[$additionalKey]);
                            $additionalKey++;
                        }
                    break;
                    case 'N1':
                        $additionalKey = $key + 1;
                        //get other n segments but not n1
                        while(isset($parsed[$additionalKey])){
                            $segmentKey = $parsed[$additionalKey][Segment::EDI_QUALIFIER_KEY];
                            if((str_starts_with($segmentKey, 'N') && !str_ends_with($segmentKey, '1')) || $segmentKey === 'PER') {
                                if($segmentKey === 'PER') {//Per segment has multiple items
                                  if(!isset($additionalItems[$segmentKey])) $additionalItems[$segmentKey] = [];
                                  $additionalItems[$segmentKey][] = $parsed[$additionalKey];
                                }else $additionalItems[$segmentKey] = $parsed[$additionalKey];
                                unset($parsed[$additionalKey]);
                                $additionalKey++;
                            }else break;
                        }
                    break;
                    case 'PO1':
                        $additionalKey = $key + 1;
                        $looping = true;
                        //get other po segments but not po1
                        while(isset($parsed[$additionalKey]) && $looping){
                            $childSegment = $parsed[$additionalKey][Segment::EDI_QUALIFIER_KEY];
                            switch($childSegment){
                                case 'PID':
                                case 'AMT':
                                case 'PO4':
                                case 'DMT':
                                case 'TD5':
                                case 'CTB':
                                case 'SLN':
                                case 'MSG':
                                    if($childSegment === 'MSG' && isset($additionalItems['SLN'])){
                                        //Part of the SLN loop
                                        $additionalItems['SLN'][] = $parsed[$additionalKey];
                                    }else $additionalItems[$childSegment][] = $parsed[$additionalKey];
                                    //Could be more than one PID, ATM, or PO4
                                    unset($parsed[$additionalKey]);
                                    $additionalKey++;
                                break;
                                default:
                                    $looping = false;
                                break;
                            }
                        }
                    break;
                    case 'CTT':
                        $additionalKey = $key + 1;
                        //Get other segments in this case AMT
                        while(isset($parsed[$additionalKey]) && $parsed[$additionalKey][Segment::EDI_QUALIFIER_KEY] === 'AMT'){
                            $additionalItems[] = $parsed[$additionalKey];
                            unset($parsed[$additionalKey]);
                            $additionalKey++;
                        }
                    break;
                }
                return ['item' => $item, 'key' => $key, 'additionalItems' => $additionalItems];
            }
        }
        return null;
    }
}
