<?php

namespace BrodSolutions\Edi;

/**
 * Class Segment
 * @package BrodSolutions\Edi
 */
class Segment implements SegmentInterface
{
    /**
     *  Array key used for EDI QUALIFIER
     */
    public const EDI_QUALIFIER_KEY = 'edi_qualifier';

    public const MAX_SEGMENTS_KEY = 'max_segments';

    /**
     * Map segment indexes into keys
     * @var array
     */
    public $segmentMapping = [];

    /**
     * List of functions that will be called during generate
     * @var array
     */
    public $callFunction = [];

    /**
     * Parse the segment content.
     * @param $segment
     * @return array
     */
    public function parse($segment): array
    {
        $content = [];
        foreach ($segment as $key => $value) {
            if (isset($this->segmentMapping[$key]) && $this->segmentMapping[$key]) {
                $content[$this->segmentMapping[$key]] = $value;
                unset($segment[$key]);
            }
        }
        if(!empty($segment)){
            //Putting the extra content in the extra key so I can see what is missing
            $content['extra'] = $segment;
        }
        return $content;
    }

    /**
     * Generate the segment line
     * @param $array
     * @return string
     */
    public function generate($array): string
    {
        $content = [];
        if (is_array($array)) {
            $segmentMapping = $this->segmentMapping;
            if (isset($array[self::MAX_SEGMENTS_KEY])) {
                $segmentMapping = array_slice($segmentMapping, 0, $array[self::MAX_SEGMENTS_KEY] + 1);
            }
            foreach ($segmentMapping as $index => $key) {
                $value = $array[$key] ?? '';
                if (isset($this->callFunction[$index])) {
                    $value = call_user_func_array($this->callFunction[$index]['name'], array_merge([$value], $this->callFunction[$index]['args']));
                }
                $content[] = $value;
            }
        }
        return implode('*', $content);
    }
}
