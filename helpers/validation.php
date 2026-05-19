<?php
if (!defined('APP_RUNNING')) {
    die('Direct access not allowed.');
}

function validate($data, $rules)
{
    $errors = [];

    foreach ($rules as $field => $ruleString) {
        $value = trim($data[$field] ?? '');
        $ruleList = explode('|', $ruleString);

        foreach ($ruleList as $rule) {
            $param = null;
            if (strpos($rule, ':') !== false) {
                [$rule, $param] = explode(':', $rule, 2);
            }

            $error = check_rule($field, $value, $rule, $param);

            if ($error !== null) {
                $errors[$field] = $error;
                break;
            }
        }
    }

    return $errors;
}

function check_rule($field, $value, $rule, $param)
{
    $label = ucwords(str_replace('_', ' ', $field));

    switch ($rule) {
        case 'required':
            return $value === '' ? "$label is required." : null;

        case 'email':
            return !filter_var($value, FILTER_VALIDATE_EMAIL)
                ? "$label must be a valid email address." : null;

        case 'min':
            return strlen($value) < (int)$param
                ? "$label must be at least $param characters." : null;

        case 'max':
            return strlen($value) > (int)$param
                ? "$label must not exceed $param characters." : null;

        case 'numeric':
            return !is_numeric($value)
                ? "$label must be a number." : null;

        case 'in':
            $allowed = explode(',', $param);
            return !in_array($value, $allowed)
                ? "$label must be one of: " . implode(', ', $allowed) . "." : null;

        case 'matches':
            return ($value !== ($_POST[$param] ?? null))
                ? "$label must match $param." : null;

        default:
            return null;
    }
}

?>