<?php

declare(strict_types=1);

namespace Tests\Validation;

use PHPUnit\Framework\TestCase;
use WTD\Application\Application;
use WTD\Config\Repository;
use WTD\Container\Container;
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

    public function testApplicationRegistersValidator(): void
    {
        /** @var non-empty-string $basePath */
        $basePath = dirname(__DIR__, 2);
        $app = new Application($basePath, new Container(), new Repository());
        $app->register(ValidationServiceProvider::class);

        self::assertInstanceOf(Validator::class, $app->container()->get(Validator::class));
    }
}
