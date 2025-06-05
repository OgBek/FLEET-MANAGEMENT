<?php

return [
    'default_log_name' => 'default',
    'enabled_when_environment' => ['local', 'production'],
    'delete_records_older_than_days' => 365,
    'default_auth_driver' => null,
    'subject_returns_softdeleted_models' => false,
    'activity_model' => \Spatie\Activitylog\Models\Activity::class,
    'table_name' => 'activity_log',
    'database_connection' => env('DB_CONNECTION', 'mysql'),
];
