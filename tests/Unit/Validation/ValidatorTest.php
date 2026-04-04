<?php

declare(strict_types=1);

namespace Tests\Unit\Validation;

use Folio\Core\Exceptions\ValidationException;
use Folio\Core\Validation\Validator;
use PHPUnit\Framework\TestCase;

final class ValidatorTest extends TestCase
{
    public function test_validator_accepts_minimal_supported_rules(): void
    {
        $validator = new Validator();

        $validated = $validator->validate([
            'name' => '楚锦',
            'age' => 18,
            'tags' => ['core'],
        ], [
            'name' => ['required', 'string'],
            'age' => ['required', 'integer'],
            'tags' => ['required', 'array'],
        ]);

        self::assertSame([
            'name' => '楚锦',
            'age' => 18,
            'tags' => ['core'],
        ], $validated);
    }

    public function test_validator_throws_validation_exception_with_unified_errors(): void
    {
        $validator = new Validator();

        try {
            $validator->validate([
                'name' => 123,
                'age' => '18',
                'tags' => 'core',
            ], [
                'name' => ['required', 'string'],
                'age' => ['required', 'integer'],
                'tags' => ['required', 'array'],
                'email' => ['required', 'string'],
            ]);

            self::fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $exception) {
            self::assertSame(422, $exception->status());
            self::assertSame('VALIDATION_FAILED', $exception->errorCode());
            self::assertSame([
                'name' => ['The field must be a string.'],
                'age' => ['The field must be an integer.'],
                'tags' => ['The field must be an array.'],
                'email' => ['The field is required.'],
            ], $exception->meta()['errors']);
        }
    }
}
