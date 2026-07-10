<?php

declare(strict_types=1);

namespace Tests\Validation;

use PHPUnit\Framework\TestCase;
use WTD\Application\Application;
use WTD\Config\Repository;
use WTD\Container\Container;
use WTD\Validation\Rule;
use WTD\Validation\ValidationException;
use WTD\Validation\ValidationServiceProvider;
use WTD\Validation\Validator;

final class ValidatorTest extends TestCase
{
    public function testValidatorReturnsValidatedFields(): void
    {
        $validated = (new Validator())->validate(
            [
                'name' => 'Taylor',
                'email' => 'taylor@example.test',
                'age' => '30',
                'active' => true,
                'extra' => 'ignored',
            ],
            [
                'name' => 'required|string|min:3|max:10',
                'email' => 'required|email',
                'age' => 'required|integer|between:18,65',
                'active' => 'boolean',
            ],
        );

        self::assertSame([
            'name' => 'Taylor',
            'email' => 'taylor@example.test',
            'age' => '30',
            'active' => true,
        ], $validated);
    }

    public function testValidatorCollectsErrors(): void
    {
        $result = (new Validator())->make(
            [
                'email' => 'invalid',
                'password' => 'secret',
                'roles' => ['admin', 'editor', 'owner'],
            ],
            [
                'name' => 'required|string',
                'email' => 'required|email',
                'password' => 'required|confirmed',
                'roles' => 'array|max:2',
            ],
        );

        self::assertTrue($result->fails());
        self::assertArrayHasKey('name', $result->errors());
        self::assertArrayHasKey('email', $result->errors());
        self::assertArrayHasKey('password', $result->errors());
        self::assertArrayHasKey('roles', $result->errors());
    }

    public function testValidationResultThrowsForInvalidData(): void
    {
        $result = (new Validator())->make(['name' => ''], ['name' => 'required']);

        try {
            $result->validated();
            self::fail('Expected validation exception.');
        } catch (ValidationException $exception) {
            self::assertSame('The given data was invalid.', $exception->getMessage());
            self::assertArrayHasKey('name', $exception->errors());
        }
    }

    public function testOptionalEmptyFieldsAreIgnored(): void
    {
        $validated = (new Validator())->validate(
            ['name' => 'Taylor', 'nickname' => ''],
            [
                'name' => 'required|string',
                'nickname' => 'string|min:3',
            ],
        );

        self::assertSame(['name' => 'Taylor', 'nickname' => ''], $validated);
    }

    public function testValidatorSupportsNestedFieldsAndCustomMessages(): void
    {
        $result = (new Validator())->make(
            ['user' => ['email' => 'invalid']],
            ['user.email' => 'required|email'],
            ['user.email.email' => 'Use a valid user email.'],
        );

        self::assertSame(['Use a valid user email.'], $result->errors()['user.email']);
    }

    public function testValidatedNestedFieldsPreserveNestedOutput(): void
    {
        $validated = (new Validator())->validate(
            ['user' => ['email' => 'taylor@example.test'], 'extra' => 'ignored'],
            ['user.email' => 'required|email'],
        );

        self::assertSame(['user' => ['email' => 'taylor@example.test']], $validated);
    }

    public function testLiteralDottedFieldsArePreservedWhenPresent(): void
    {
        $validated = (new Validator())->validate(
            ['metadata.version' => '1'],
            ['metadata.version' => 'required|string'],
        );

        self::assertSame(['metadata.version' => '1'], $validated);
    }

    public function testValidatorSupportsConditionalRules(): void
    {
        $validator = new Validator();

        $requiredIf = $validator->make(
            ['type' => 'company'],
            ['company_name' => 'required_if:type,company|string'],
        );
        $requiredUnless = $validator->make(
            ['status' => 'published'],
            ['published_at' => 'required_unless:status,draft|date'],
        );

        self::assertArrayHasKey('company_name', $requiredIf->errors());
        self::assertArrayHasKey('published_at', $requiredUnless->errors());
    }

    public function testValidatorSupportsCommonRules(): void
    {
        $validated = (new Validator())->validate(
            [
                'website' => 'https://example.test',
                'starts_at' => '2026-07-10',
                'role' => 'admin',
                'password' => 'secret',
                'password_confirmation' => 'secret',
                'terms' => '1',
            ],
            [
                'website' => 'required|url',
                'starts_at' => 'required|date',
                'role' => 'required|not_in:guest,banned',
                'password' => 'required|confirmed',
                'terms' => 'required|boolean',
            ],
        );

        self::assertSame('admin', $validated['role']);
        self::assertSame('https://example.test', $validated['website']);
    }

    public function testValidatorSupportsAcceptedDeclinedSizeAndRegexRules(): void
    {
        $validated = (new Validator())->validate(
            [
                'terms' => 'on',
                'marketing' => 'no',
                'code' => 'WTD-001',
                'roles' => ['admin', 'editor'],
            ],
            [
                'terms' => 'accepted',
                'marketing' => 'declined',
                'code' => 'regex:/^WTD-[0-9]{3}$/',
                'roles' => 'array|size:2',
            ],
        );

        self::assertSame('WTD-001', $validated['code']);
        self::assertSame(['admin', 'editor'], $validated['roles']);
    }

    public function testValidatorCollectsAcceptedDeclinedSizeAndRegexFailures(): void
    {
        $result = (new Validator())->make(
            [
                'terms' => 'off',
                'marketing' => 'yes',
                'code' => 'BAD',
                'roles' => ['admin'],
            ],
            [
                'terms' => 'accepted',
                'marketing' => 'declined',
                'code' => 'regex:/^WTD-[0-9]{3}$/',
                'roles' => 'array|size:2',
            ],
        );

        self::assertArrayHasKey('terms', $result->errors());
        self::assertArrayHasKey('marketing', $result->errors());
        self::assertArrayHasKey('code', $result->errors());
        self::assertArrayHasKey('roles', $result->errors());
    }

    public function testSometimesSkipsMissingFields(): void
    {
        $result = (new Validator())->make([], ['nickname' => 'sometimes|required|string']);

        self::assertTrue($result->passes());
    }

    public function testPresentAndFilledRulesRunForOptionalFields(): void
    {
        $result = (new Validator())->make(
            ['name' => ''],
            [
                'token' => 'present',
                'name' => 'filled',
            ],
        );

        self::assertArrayHasKey('token', $result->errors());
        self::assertArrayHasKey('name', $result->errors());
    }

    public function testValidatorSupportsClosureRules(): void
    {
        $validator = (new Validator())->extend(
            'starts_with',
            /**
             * @param array<string, mixed> $data
             */
            static fn (string $field, mixed $value, ?string $parameter, array $data): bool => is_string($value)
                && is_string($parameter)
                && str_starts_with($value, $parameter),
            'The :field field has an invalid prefix.',
        );

        $result = $validator->make(['code' => 'WTD-001'], ['code' => 'required|starts_with:APP']);

        self::assertSame(['The code field has an invalid prefix.'], $result->errors()['code']);
    }

    public function testValidatorSupportsObjectRules(): void
    {
        $validator = (new Validator())->extend('uppercase', new UppercaseRule());

        $result = $validator->make(['code' => 'abc'], ['code' => 'required|uppercase']);

        self::assertSame(['The code field must be uppercase.'], $result->errors()['code']);
    }

    public function testUnknownRulesFailValidation(): void
    {
        $result = (new Validator())->make(['code' => 'abc'], ['code' => 'unknown_rule']);

        self::assertSame(['The code field failed unknown_rule validation.'], $result->errors()['code']);
    }

    public function testApplicationRegistersValidator(): void
    {
        /** @var non-empty-string $basePath */
        $basePath = dirname(__DIR__, 2);
        $app = new Application($basePath, new Container(), new Repository());
        $app->register(ValidationServiceProvider::class);

        self::assertInstanceOf(Validator::class, $app->container()->get(Validator::class));
    }
}

final class UppercaseRule implements Rule
{
    /**
     * @param array<string, mixed> $data
     */
    public function passes(string $field, mixed $value, ?string $parameter, array $data): bool
    {
        return is_string($value) && strtoupper($value) === $value;
    }

    public function message(string $field, ?string $parameter): string
    {
        return sprintf('The %s field must be uppercase.', $field);
    }
}
