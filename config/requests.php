<?php

declare(strict_types=1);

require_once __DIR__ . '/validation.php';
require_once __DIR__ . '/db.php';

function saveProjectRequest(array $input, bool $isDesignRequest = false): array
{
    $validation = validateProjectRequest($input, $isDesignRequest);
    if (!$validation['valid']) {
        return [
            'success' => false,
            'status' => 422,
            'message' => reset($validation['errors']),
            'errors' => $validation['errors'],
        ];
    }

    try {
        $statement = getDb()->prepare(
            'INSERT INTO contact_messages (subject, name, email, message, request_type, design_style)
             VALUES (:subject, :name, :email, :message, :request_type, :design_style)'
        );
        $statement->execute($validation['data']);

        return [
            'success' => true,
            'status' => 201,
            'message' => 'درخواست شما با موفقیت ثبت شد.',
        ];
    } catch (Throwable $exception) {
        error_log('Project request submission failed: ' . $exception->getMessage());
        return [
            'success' => false,
            'status' => 503,
            'message' => 'ثبت درخواست در حال حاضر ممکن نیست. لطفاً بعداً دوباره تلاش کنید.',
            'errors' => [],
        ];
    }
}
