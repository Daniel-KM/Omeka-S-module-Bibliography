<?php declare(strict_types=1);

namespace Bibliography\Stdlib;

/**
 * Expand the values "collected record" into the properties of the resource.
 *
 * With the data types "… (collected record)", the suggester stores the whole
 * record, converted into resource data, as json in the uri of the value. The
 * record fills the empty properties and the empty resource class, then the
 * value keeps the uri of the record and the formatted reference as label.
 */
class RecordExpander
{
    /**
     * Expand the values "collected record" of resource data.
     *
     * @param array $data Resource data, with properties keyed by term.
     * @param string[] $filledTerms Terms filled outside of the data, for
     *   example during a partial update.
     * @return array Associative array with keys "data", "filled" (terms
     *   filled from records) and "skipped" (terms already filled, so not
     *   modified). "filled" and "skipped" are empty when there is no record.
     */
    public function expand(array $data, array $filledTerms = []): array
    {
        $result = [
            'data' => $data,
            'filled' => [],
            'skipped' => [],
        ];

        $filled = array_fill_keys($filledTerms, true);
        foreach ($data as $term => $values) {
            if (is_array($values) && $values && strpos((string) $term, ':') && strpos((string) $term, 'o:') !== 0) {
                $filled[$term] = true;
            }
        }

        foreach ($data as $term => $values) {
            if (!is_array($values) || strpos((string) $term, 'o:') === 0) {
                continue;
            }
            foreach ($values as $key => $value) {
                $record = is_array($value) ? $this->recordFromValue($value) : null;
                if ($record === null) {
                    continue;
                }

                foreach ($record as $recordTerm => $recordValues) {
                    if (!is_array($recordValues) || !$recordValues || strpos((string) $recordTerm, 'o:') === 0) {
                        continue;
                    }
                    if (isset($filled[$recordTerm])) {
                        $result['skipped'][$recordTerm] = $recordTerm;
                        continue;
                    }
                    $result['data'][$recordTerm] = array_values($recordValues);
                    $result['filled'][$recordTerm] = $recordTerm;
                    $filled[$recordTerm] = true;
                }

                if (!empty($record['o:resource_class']['o:id'])
                    && empty($result['data']['o:resource_class']['o:id'])
                ) {
                    $result['data']['o:resource_class'] = ['o:id' => (int) $record['o:resource_class']['o:id']];
                }

                $uri = $this->recordUri($record);
                if ($uri) {
                    $result['data'][$term][$key]['@id'] = $uri;
                } else {
                    unset($result['data'][$term][$key]['@id']);
                    $result['data'][$term][$key]['type'] = 'literal';
                    $result['data'][$term][$key]['@value'] = $value['o:label'] ?? '';
                }
            }
        }

        $result['filled'] = array_values($result['filled']);
        $result['skipped'] = array_values(array_diff_key($result['skipped'], $result['filled']));
        return $result;
    }

    /**
     * Get the record stored in a value "collected record", if any.
     */
    public function recordFromValue(array $value): ?array
    {
        $type = (string) ($value['type'] ?? '');
        $id = $value['@id'] ?? null;
        if (substr($type, -7) !== ':record'
            || !is_string($id)
            || substr(ltrim($id), 0, 1) !== '{'
        ) {
            return null;
        }
        $record = json_decode($id, true);
        return is_array($record) ? $record : null;
    }

    /**
     * Get the uri of the record (doi or url of the notice).
     */
    public function recordUri(array $record): ?string
    {
        foreach (['bibo:doi', 'bibo:uri'] as $term) {
            foreach ($record[$term] ?? [] as $value) {
                if (!empty($value['@id']) && is_string($value['@id'])) {
                    return $value['@id'];
                }
            }
        }
        return null;
    }
}
