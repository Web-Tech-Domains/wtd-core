<?php

declare(strict_types=1);

namespace WTD\Validation;

use WTD\Http\Request;

/**
 * Encapsulates request validation rules and validated input access.
 */
abstract class FormRequest
{
    /**
     * @var array<string, mixed>|null
     */
    private ?array $validated = null;

    final public function __construct(
        private readonly Request $request,
        private readonly Validator $validator,
    ) {
    }

    /**
     * Create and validate a form request from an HTTP request.
     */
    public static function from(Request $request, Validator $validator): static
    {
        $formRequest = new static($request, $validator);
        $formRequest->validateResolved();

        return $formRequest;
    }

    /**
     * Return validation rules.
     *
     * @return array<string, string|list<string>>
     */
    abstract public function rules(): array;

    /**
     * Return custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [];
    }

    /**
     * Determine whether this request is authorized.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validate authorization and input.
     */
    public function validateResolved(): void
    {
        if (!$this->authorize()) {
            throw new ValidationException([
                'authorization' => ['This action is unauthorized.'],
            ]);
        }

        $this->validated = $this->validator->validate(
            $this->request->all(),
            $this->rules(),
            $this->messages(),
        );
    }

    /**
     * Return all merged request input.
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->request->all();
    }

    /**
     * Return a request input value.
     */
    public function input(string $key, mixed $default = null): mixed
    {
        return $this->request->input($key, $default);
    }

    /**
     * Return validated input.
     *
     * @return array<string, mixed>
     */
    public function validated(): array
    {
        if ($this->validated === null) {
            $this->validateResolved();
        }

        return $this->validated ?? [];
    }

    /**
     * Return the underlying HTTP request.
     */
    public function request(): Request
    {
        return $this->request;
    }
}
