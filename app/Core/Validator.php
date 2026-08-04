<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Rule-based validator with Bangla error messages.
 * Usage: (new Validator($data, ['field' => 'required|max:100']))->fails()
 */
final class Validator
{
    private array $data;
    private array $rules;
    private array $errors = [];

    public function __construct(array $data, array $rules)
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->run();
    }

    private function run(): void
    {
        foreach ($this->rules as $field => $ruleString) {
            $rules = explode('|', $ruleString);
            $value = $this->data[$field] ?? null;

            foreach ($rules as $rule) {
                [$name, $param] = array_pad(explode(':', $rule, 2), 2, null);
                $this->applyRule($field, $value, $name, $param);
            }
        }
    }

    private function applyRule(string $field, mixed $value, string $name, ?string $param): void
    {
        switch ($name) {
            case 'required':
                if ($value === null || $value === '') {
                    $this->addError($field, 'এই ফিল্ডটি আবশ্যক');
                }
                break;
            case 'numeric':
                if ($value !== null && $value !== '' && !is_numeric($value)) {
                    $this->addError($field, 'শুধুমাত্র সংখ্যা লিখুন');
                }
                break;
            case 'max':
                if ($value !== null && mb_strlen((string) $value) > (int) $param) {
                    $this->addError($field, "সর্বোচ্চ {$param} অক্ষর অনুমোদিত");
                }
                break;
            case 'min':
                if ($value !== null && $value !== '' && mb_strlen((string) $value) < (int) $param) {
                    $this->addError($field, "সর্বনিম্ন {$param} অক্ষর প্রয়োজন");
                }
                break;
            case 'regex':
                if ($value !== null && $value !== '' && !preg_match($param, (string) $value)) {
                    $this->addError($field, 'সঠিক ফরম্যাটে দিন');
                }
                break;
            case 'in':
                $allowed = explode(',', (string) $param);
                if ($value !== null && $value !== '' && !in_array((string) $value, $allowed, true)) {
                    $this->addError($field, 'অবৈধ মান');
                }
                break;
        }
    }

    private function addError(string $field, string $message): void
    {
        $this->errors[$field] ??= [];
        $this->errors[$field][] = $message;
    }

    public function fails(): bool
    {
        return count($this->errors) > 0;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    /**
     * Bangladeshi mobile number: 11 digits, Robi (016/019) or Airtel (017) prefix only.
     * @return string|null 'robi', 'airtel', or null if invalid
     */
    public static function operatorForMobile(string $mobile): ?string
    {
        if (!preg_match('/^01[0-9]{9}$/', $mobile)) {
            return null;
        }
        $prefix = substr($mobile, 0, 3);
        if (in_array($prefix, ['016', '019'], true)) {
            return 'robi';
        }
        if ($prefix === '017') {
            return 'airtel';
        }
        return null;
    }
}
