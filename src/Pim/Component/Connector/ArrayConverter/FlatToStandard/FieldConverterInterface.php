<?php

namespace Pim\Component\Connector\ArrayConverter\FlatToStandard;

/**
 * Object that represent the converted field (family, category, etc.) from a flat file data (CSV, XLSX)
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FieldConverterInterface
{
    /**
     * Convert a field from a flat file data (CSV or XLSX file for instance).
     * It guesses its names and its values depending on the data read from the data source.
     *
     * @param string $fieldName
     * @param string $value
     *
     * @return ConvertedField[]
     */
    public function convert(string $fieldName, string $value): array;
}