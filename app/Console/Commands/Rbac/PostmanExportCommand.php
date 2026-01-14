<?php

namespace App\Console\Commands\Rbac;

class PostmanExportCommand extends BaseRbacCommand
{
    protected $signature = 'rbac:postman:export {--base=http://127.0.0.1:8000}';
    protected $description = 'Print a minimal Postman collection JSON for RBAC endpoints';

    public function handle(): int
    {
        $base = rtrim((string) $this->option('base'), '/');

        $collection = [
            'info' => [
                'name' => 'LaravelStartup RBAC',
                'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
            ],
            'item' => [
                [
                    'name' => 'Auth Login',
                    'request' => [
                        'method' => 'POST',
                        'header' => [['key' => 'Accept', 'value' => 'application/json']],
                        'url' => $base . '/api/auth/login',
                    ],
                ],
                [
                    'name' => 'Auth Me',
                    'request' => [
                        'method' => 'GET',
                        'header' => [
                            ['key' => 'Accept', 'value' => 'application/json'],
                            ['key' => 'Authorization', 'value' => 'Bearer {{token}}'],
                        ],
                        'url' => $base . '/api/auth/me',
                    ],
                ],
                [
                    'name' => 'RBAC Info',
                    'request' => [
                        'method' => 'GET',
                        'header' => [['key' => 'Accept', 'value' => 'application/json']],
                        'url' => $base . '/api/rbac/info',
                    ],
                ],
            ],
        ];

        $this->line(json_encode($collection, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        return self::SUCCESS;
    }
}