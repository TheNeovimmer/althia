<?php
namespace App\Core;

class Validator
{
    private array $errors = [];

    public function validate(array $data, array $rules): bool
    {
        $this->errors = [];
        foreach ($rules as $field => $ruleSet) {
            $ruleList = is_array($ruleSet) ? $ruleSet : explode('|', $ruleSet);
            $value = $data[$field] ?? null;
            foreach ($ruleList as $rule) {
                $params = [];
                if (str_contains($rule, ':')) {
                    [$rule, $paramStr] = explode(':', $rule, 2);
                    $params = explode(',', $paramStr);
                }
                $methodName = 'rule' . ucfirst($rule);
                if (method_exists($this, $methodName)) {
                    $this->$methodName($field, $value, $params, $data);
                }
            }
        }
        return empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): ?string
    {
        return $this->errors[0] ?? null;
    }

    private function addError(string $field, string $message): void
    {
        $this->errors[] = str_replace(':field', $field, $message);
    }

    private function ruleRequired(string $field, $value, array $params, array $data): void
    {
        if ($value === null || $value === '') {
            $this->addError($field, 'The :field field is required');
        }
    }

    private function ruleEmail(string $field, $value, array $params, array $data): void
    {
        if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, 'The :field must be a valid email address');
        }
    }

    private function ruleMin(string $field, $value, array $params, array $data): void
    {
        if ($value && strlen($value) < (int)$params[0]) {
            $this->addError($field, "The :field must be at least {$params[0]} characters");
        }
    }

    private function ruleMax(string $field, $value, array $params, array $data): void
    {
        if ($value && strlen($value) > (int)$params[0]) {
            $this->addError($field, "The :field must not exceed {$params[0]} characters");
        }
    }

    private function ruleConfirmed(string $field, $value, array $params, array $data): void
    {
        $confirmationField = $field . '_confirmation';
        if ($value !== ($data[$confirmationField] ?? null)) {
            $this->addError($field, 'The :field confirmation does not match');
        }
    }

    private function ruleUnique(string $field, $value, array $params, array $data): void
    {
        if ($value) {
            [$table, $column] = $params;
            $db = Database::getInstance();
            $existing = $db->fetch("SELECT id FROM {$table} WHERE {$column} = ?", [$value]);
            if ($existing) {
                $this->addError($field, 'The :field has already been taken');
            }
        }
    }

    private function ruleNumeric(string $field, $value, array $params, array $data): void
    {
        if ($value && !is_numeric($value)) {
            $this->addError($field, 'The :field must be a number');
        }
    }

    private function rulePhone(string $field, $value, array $params, array $data): void
    {
        if ($value && !preg_match('/^[+\d\s\-()]{7,20}$/', $value)) {
            $this->addError($field, 'The :field must be a valid phone number');
        }
    }

    private function ruleIn(string $field, $value, array $params, array $data): void
    {
        if ($value && !in_array($value, $params)) {
            $this->addError($field, 'The :field must be one of: ' . implode(', ', $params));
        }
    }

    private function ruleDate(string $field, $value, array $params, array $data): void
    {
        if ($value && !strtotime($value)) {
            $this->addError($field, 'The :field must be a valid date');
        }
    }
}
