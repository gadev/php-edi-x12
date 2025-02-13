<?php

namespace BrodSolutions\Edi;

class SegmentCombinator
{
    private ?array $currentGroup;

    public function __construct(public array $segments)
    {
    }

    public function get(): array
    {
        return $this->segments;
    }

    public function buildGroups(): self
    {
        $this->segments = $this->buildLoop($this->segments, 'GS', 'GE', 'groups');

        return $this;
    }

    public function buildSets(): self
    {
        foreach ($this->segments['groups'] as &$group) {
            $group['sets'] = $this->buildLoop($group['segments'], 'ST', 'SE', null, function($segment) {
                if($segment['edi_qualifier'] === 'DTM') {
                    $this->currentGroup['date'] = $segment['date'];
                    return false;
                }
                return true;
            });
            unset($group['segments']);
        }
        unset($group);

        return $this;
    }

    public function buildClaims()
    {

    }

    public function buildClaimsOLD(): array
    {
        return $this->buildLoop($this->segments, 'CLP', ['GE', 'SE', 'IEA'], null, function($segment) {
            switch($segment['edi_qualifier']) {
                case 'NM1':
                    $this->currentGroup['patient_name_l'] = $segment['patient_name_l'];
                    $this->currentGroup['patient_name_f'] = $segment['patient_name_f'];
                    $this->currentGroup['patient_name_m'] = $segment['patient_name_m'];
                    $this->currentGroup['patient_identifier'] = $segment['patient_identifier'];
                    return false;
                case 'DTM':
                    if($segment['date_qualifier'] === '232') {
                        $this->currentGroup['init_date'] = $segment['date'];
                    } else {
                        $this->currentGroup['received_date'] = $segment['date'];
                    }
                    return false;
                case 'AMT':
                    if($segment['amount_qualifier'] === 'AU') {
                        $this->currentGroup['coverage_amount'] = $segment['amount'];
                    }
                    return false;
                case 'GE':
                case 'SE':
                case 'IEA':
                    return false;
                default:
                    return true;
            }
        });
    }

    private function buildLoop(array $loopData, string $loopStartCode, string|array $loopEndCode, string $loopName = null, Callable $segmentCallback = null): array
    {
        $res = [];
        $grouped = [];
        $this->currentGroup = null;

        foreach ($loopData as $segment) {
            $code = $segment['edi_qualifier'];

            if ($code === $loopStartCode) {
                $this->currentGroup = $segment;
            } elseif ($this->currentGroup !== null) {
                if ((is_array($loopEndCode) ? in_array($code, $loopEndCode, true) : $code === $loopEndCode)) {
                    if (!empty($this->currentGroup)) {
                        $grouped[] = $this->currentGroup;
                    }
                    if($loopName) {
                        $res[$loopName] = $grouped;
                    } else {
                        $res = $grouped;
                    }
                    $grouped = [];
                    $this->currentGroup = null;
                    //$res[] = $segment; //ignore loop end code
                } else {
                    $continue = true;
                    if($segmentCallback) {
                        $continue = $segmentCallback($segment);
                    }
                    if ($continue) {
                        if(empty($this->currentGroup['segments'])) {
                            $this->currentGroup['segments'] = [];
                        }
                        $this->currentGroup['segments'][] = $segment;
                    }
                }
            } else {
                if ((is_array($loopEndCode) ? in_array($code, $loopEndCode, true) : $code === $loopEndCode)) {
                    continue;
                }
                $res[] = $segment;
            }
        }

        return $res;
    }
}
