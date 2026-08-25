<?php

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function sanitize_input(?string $value): string
{
    return trim((string) $value);
}

function sanitize_post(array $fields): array
{
    $data = [];

    foreach ($fields as $field) {
        $data[$field] = sanitize_input($_POST[$field] ?? '');
    }

    return $data;
}

function redirect_to(string $path): void
{
    header("Location: {$path}");
    exit();
}

function set_flash(string $key, string $message): void
{
    $_SESSION['flash'][$key] = $message;
}

function get_flash(string $key): ?string
{
    if (!isset($_SESSION['flash'][$key])) {
        return null;
    }

    $message = $_SESSION['flash'][$key];
    unset($_SESSION['flash'][$key]);

    return $message;
}

function require_admin_auth(): void
{
    if (
        !isset($_SESSION['user_id']) ||
        !isset($_SESSION['role']) ||
        $_SESSION['role'] !== 'admin'
    ) {
        redirect_to('../login.php');
    }
}

function get_request_int(array $source, string $key): int
{
    $value = $source[$key] ?? null;

    if (!is_numeric($value)) {
        return 0;
    }

    return (int) $value;
}

function student_genders(): array
{
    return ['Male', 'Female', 'Other'];
}

function student_faculties(): array
{
    return ['BCA', 'BIT', 'BSc CSIT'];
}

function validate_student_data(array $data, bool $requireName = true): array
{
    $errors = [];

    if ($requireName && ($data['name'] ?? '') === '') {
        $errors[] = 'Please fill in all fields.';
    }

    $required = ['gender', 'faculty', 'dob', 'address', 'phone', 'email'];

    foreach ($required as $field) {
        if (($data[$field] ?? '') === '') {
            $errors[] = 'Please fill in all fields.';
            break;
        }
    }

    if (($data['gender'] ?? '') !== '' && !in_array($data['gender'], student_genders(), true)) {
        $errors[] = 'Select a valid gender.';
    }

    if (($data['faculty'] ?? '') !== '' && !in_array($data['faculty'], student_faculties(), true)) {
        $errors[] = 'Select a valid faculty.';
    }

    if (($data['email'] ?? '') !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (($data['phone'] ?? '') !== '' && !preg_match('/^[0-9]{10}$/', $data['phone'])) {
        $errors[] = 'Phone number must contain 10 digits.';
    }

    return $errors;
}

function generate_password(int $length = 8): string
{
    $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
    $password = '';

    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[random_int(0, strlen($characters) - 1)];
    }

    return $password;
}
