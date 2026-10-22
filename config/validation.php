<?php

declare(strict_types=1);

function textLength(string $value): int
{
    if (function_exists('mb_strlen')) {
        return mb_strlen($value, 'UTF-8');
    }

    $characterCount = preg_match_all('/./us', $value, $characters);
    return $characterCount === false ? PHP_INT_MAX : $characterCount;
}

function validateProjectRequest(array $input, bool $isDesignRequest = false, bool $requireSubject = true): array
{
    $name = trim((string) ($input['name'] ?? ''));
    $email = trim((string) ($input['email'] ?? ''));
    $subject = trim((string) ($input['subject'] ?? ''));
    $message = trim((string) ($input['message'] ?? ''));
    $style = trim((string) ($input['design_style'] ?? ''));
    $errors = [];

    if ($name === '' || textLength($name) > 100) {
        $errors['name'] = 'نام را وارد کنید (حداکثر ۱۰۰ نویسه).';
    }
    if ($email === '' || strlen($email) > 254 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        $errors['email'] = 'یک ایمیل معتبر وارد کنید.';
    }
    if (($requireSubject && $subject === '') || textLength($subject) > 180) {
        $errors['subject'] = 'موضوع را وارد کنید (حداکثر ۱۸۰ نویسه).';
    }
    if ($message === '' || textLength($message) > 5000) {
        $errors['message'] = 'توضیحات را وارد کنید (حداکثر ۵۰۰۰ نویسه).';
    }

    $allowedStyles = ['minimal', 'modern', 'formal', 'custom'];
    if ($isDesignRequest && !in_array($style, $allowedStyles, true)) {
        $errors['design_style'] = 'یکی از سبک‌های موجود را انتخاب کنید.';
    }

    return [
        'valid' => $errors === [],
        'errors' => $errors,
        'data' => [
            'name' => $name,
            'email' => $email,
            'subject' => $subject,
            'message' => $message,
            'request_type' => $isDesignRequest ? 'design' : 'contact',
            'design_style' => $isDesignRequest ? $style : '',
        ],
    ];
}
