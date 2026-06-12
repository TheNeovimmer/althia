<?php
namespace App\Services;

class FileService
{
    private const ALLOWED_TYPES = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx'];
    private const MAX_SIZE = 10485760; // 10MB

    public static function upload(array $file, string $directory = 'uploads'): ?string
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, self::ALLOWED_TYPES)) {
            return null;
        }

        if ($file['size'] > self::MAX_SIZE) {
            return null;
        }

        $uploadDir = __DIR__ . '/../../public/assets/' . $directory;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = uniqid() . '_' . time() . '.' . $extension;
        $filepath = $uploadDir . '/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return $directory . '/' . $filename;
        }

        return null;
    }

    public static function delete(string $path): bool
    {
        $fullPath = __DIR__ . '/../../public/assets/' . $path;
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
        return false;
    }

    public static function getFileInfo(string $path): ?array
    {
        $fullPath = __DIR__ . '/../../public/assets/' . $path;
        if (!file_exists($fullPath)) {
            return null;
        }
        return [
            'name' => basename($path),
            'size' => filesize($fullPath),
            'type' => mime_content_type($fullPath),
            'extension' => pathinfo($path, PATHINFO_EXTENSION),
        ];
    }

    public static function getAllowedTypes(): array
    {
        return self::ALLOWED_TYPES;
    }

    public static function getMaxSize(): int
    {
        return self::MAX_SIZE;
    }
}
