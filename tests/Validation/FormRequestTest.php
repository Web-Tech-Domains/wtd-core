<?php

declare(strict_types=1);

namespace Tests\Validation;

use PHPUnit\Framework\TestCase;
use WTD\Http\Request;
use WTD\Validation\FormRequest;
use WTD\Validation\ValidationException;
use WTD\Validation\Validator;

final class FormRequestTest extends TestCase
{
    public function testFormRequestReturnsValidatedInput(): void
    {
        $request = UserFormRequest::from(
            new Request('POST', '/users', body: ['name' => 'Taylor', 'email' => 'taylor@example.test', 'extra' => 'ignored']),
            new Validator(),
        );

        self::assertSame([
            'name' => 'Taylor',
            'email' => 'taylor@example.test',
        ], $request->validated());
    }

    public function testFormRequestUsesCustomMessages(): void
    {
        try {
            UserFormRequest::from(
                new Request('POST', '/users', body: ['name' => 'Taylor', 'email' => 'invalid']),
                new Validator(),
            );
            self::fail('Expected validation exception.');
        } catch (ValidationException $exception) {
            self::assertSame(['Use a valid email address.'], $exception->errors()['email']);
        }
    }

    public function testFormRequestCanRejectUnauthorizedRequests(): void
    {
        $this->expectException(ValidationException::class);

        UnauthorizedFormRequest::from(new Request('POST', '/users'), new Validator());
    }
}

final class UserFormRequest extends FormRequest
{
    /**
     * @return array<string, string|list<string>>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'email' => 'required|email',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.email' => 'Use a valid email address.',
        ];
    }
}

final class UnauthorizedFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return false;
    }

    /**
     * @return array<string, string|list<string>>
     */
    public function rules(): array
    {
        return [];
    }
}
