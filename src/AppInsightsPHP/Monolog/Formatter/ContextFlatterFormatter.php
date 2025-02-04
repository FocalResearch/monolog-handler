<?php

declare(strict_types=1);

namespace AppInsightsPHP\Monolog\Formatter;

use Monolog\Formatter\NormalizerFormatter;
use Monolog\LogRecord;

final class ContextFlatterFormatter extends NormalizerFormatter
{
    private $prefix;

    public function __construct(string $prefix = '', ?string $dateFormat = null)
    {
        parent::__construct($dateFormat);
        $this->prefix = $prefix;
    }

    public function format(LogRecord $record)
    {
        $recordArray = $record->toArray();
        if (!\array_key_exists('context', $recordArray) || !\is_array($recordArray['context'])) {
            return $recordArray;
        }

        $formatted = $recordArray;
        $formatted['context'] = $this->flatterArray($recordArray['context']);

        return $formatted;
    }

    public function formatBatch(array $records)
    {
        foreach ($records as $key => $record) {
            $records[$key] = $this->format($record);
        }

        return $records;
    }

    private function flatterArray(array $array, $prefix = '')
    {
        $result = [];

        foreach ($array as $key => $value) {
            if (\is_array($value)) {
                $result = $result + $this->flatterArray($value, $prefix . $key . '.');
            } else {
                $normalized = $this->normalize($value);

                if (\is_array($normalized)) {
                    $result = $result + $this->flatterArray($normalized, $prefix . $key . '.');
                } else {
                    $result[$this->prefix . $prefix . $key] = $normalized;
                }
            }
        }

        return $result;
    }
}
