<?php

namespace BrodSolutions\Edi;

class Collector
{
    public static function combine(array $segments): array
    {
//        return (new SegmentCombinator($segments))
//            ->buildGroups()
//            ->buildSets()
//            ->buildClaims();

        $transactions = [];
        $currentSt = null;
        $currentClp = null;
        $currentSvc = null;
        $n1Type = null;

        foreach ($segments as $i => $segment) {
            switch ($segment['edi_qualifier']) {
                case 'ST':
                    $currentSt = $segment;
                    $currentSt['check_number'] = $currentSt['transaction_control_number'];
                    $transactions[] = &$currentSt;
                    break;
                case 'BPR':
                    $currentSt['paid_amount'] = $segment['paid_amount'];
                    $currentSt['payment_method'] = $segment['payment_method'];
                    $currentSt['payment_format'] = $segment['credit_or_debit'];
                    break;
                case 'TRN': //@TODO
                    break;
                case 'DTM':
                    switch ($segment['date_qualifier']) {
                        case '405': $currentSt['paid_date'] = $segment['date']; break;
                        case '472': $currentSvc['from_dos'] = $segment['date']; break;
                        case '232': $currentSt['from_dos'] = $segment['date']; break;
                        case '050': $currentClp['claim_received_date'] = $segment['date']; break;
                    }
                    break;
                case 'N1':
                    $n1Type = $segment['entity_identifier_code'];
                    if ($n1Type === 'PR') {
                        $currentSt['payer_name'] = $segment['name'];
                    } elseif ($n1Type === 'PE') {
                        $currentSt['prov_name'] = $segment['name'];
                        if($segment['identification_code_qualifier'] === 'XX') {
                            $currentSt['prov_npi'] = $segment['identification_code'];
                        }
                    }
                    break;
                case 'N3':
                    if ($n1Type === 'PR') {
                        $currentSt['payer_addr_1'] = $segment['address_information_1'];
                    } elseif ($n1Type === 'PE') {
                        $currentSt['prov_addr_1'] = $segment['address_information_1'];
                    }
                    break;
                case 'N4':
                    if ($n1Type === 'PR') {
                        $currentSt['payer_city'] = $segment['city'];
                        $currentSt['payer_state'] = $segment['state'];
                        $currentSt['payer_zip'] = $segment['postal_code'];
                    } elseif ($n1Type === 'PE') {
                        $currentSt['prov_city'] = $segment['city'];
                        $currentSt['prov_state'] = $segment['state'];
                        $currentSt['prov_zip'] = $segment['postal_code'];
                    }
                    break;
                case 'REF':
                    if ($n1Type === 'PR' && $segment['reference_qualifier'] === '2U') {
                        $currentSt['payerid'] = $segment['reference_value'];
                    } elseif ($n1Type === 'PE' && $segment['reference_qualifier'] === 'TJ') {
                        $currentSt['prov_taxid'] = $segment['reference_value'];
                    } elseif($segment['reference_qualifier'] === '6R') {
                        $currentSvc['chgid'] = $segment['reference_value'];
                    } else {
                        ray($segment);
                    }
                    break;
                case 'CLP':
                    unset($currentClp);
                    $currentClp = $segment;
                    $currentSt['claim'][] = &$currentClp;
                    break;
                case 'NM1':
                    if($segment['type'] === 'QC') {
                        $currentClp['pat_name_l'] = $segment['patient_name_l'];
                        $currentClp['pat_name_f'] = $segment['patient_name_f'];
                        $currentClp['pat_name_m'] = $segment['patient_name_m'];
                    }
                    if($segment['identifier_type'] === 'MI') {
                        $currentClp['ins_number'] = $segment['patient_identifier'];
                    }
                    break;
                case 'AMT':
                    switch ($segment['amount_qualifier']) {
                        case 'AU': $currentClp['coverage_amount'] = $segment['amount']; break;
                        case 'B6': $currentSvc['allowed'] = $segment['amount']; break;
                    }
                    break;
                case 'SVC':
                    unset($currentSvc);
                    $currentSvc = [
                        'proc_code' => $segment['id'],
                        'charge' => $segment['amount_charged'],
                        'paid' => $segment['amount_paid'],
                        'units' => $segment['units'],
                        'mod1' => $segment['extra']['mods'][0] ?? null,
                        'mod2' => $segment['extra']['mods'][1] ?? null,
                        'mod3' => $segment['extra']['mods'][2] ?? null,
                        'mod4' => $segment['extra']['mods'][3] ?? null,
                    ];
                    $currentClp['charge'][] = &$currentSvc;
                    break;
                case 'CAS':
                    $currentSvc['adjustment'][] = [
                        'group' => $segment['adjustment_group'],
                        'code' => $segment['adjustment_code'],
                        'amount' => $segment['adjustment_amount'],
                    ];
                    break;
            }
        }
        unset($currentSvc, $currentClp, $currentSt);

        return $transactions;
    }



    public static function nameSegments(array $segments): array
    {
        $result = [];
        foreach ($segments as $segment) {
            $code = $segment['edi_qualifier'];
            unset($segment['edi_qualifier']);
            $result[$code] = $segment;
        }

        return $result;
    }
}
