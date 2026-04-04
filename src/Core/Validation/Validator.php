<?php

declare(strict_types=1);

namespace Folio\Core\Validation;

use Folio\Core\Exceptions\ValidationException;

final class Validator
{
    /**
     * @param array<string, mixed> $data
     * @param array<string, list<string>> $rules
     * @return array<string, mixed>
     */
    public function validate(array $data, array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;
            $present = array_key_exists($field, $data);

            foreach ($fieldRules as $rule) {
                if ($rule === 'required') {
                    if (!$present || $value === null || $value === '') {
                        $errors[$field][] = 'The field is required.';
                    }

                    continue;
                }

                if (!$present || $value === null) {
                    continue;
                }

                if ($rule === 'string' && !is_string($value)) {
                    $errors[$field][] = 'The field must be a string.';
                    continue;
                }

                if ($rule === 'integer' && !is_int($value)) {
                    $errors[$field][] = 'The field must be an integer.';
                    continue;
                }

                if ($rule === 'array' && !is_array($value)) {
                    $errors[$field][] = 'The field must be an array.';
                }
            }
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        return $data;
    }
}
