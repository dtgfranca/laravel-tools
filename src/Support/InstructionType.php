<?php

namespace LaravelTools\Upgrade\Support;

enum InstructionType: string
{
    case SetPhp = 'set_php';
    case RequirePackage = 'require';
    case RequireDevPackage = 'require_dev';
    case UpdatePackage = 'update';
    case RemovePackage = 'remove';
    case ReplaceInFile = 'replace_in_file';
    case ReplaceInFiles = 'replace_in_files';
    case SetConfigValue = 'set_config_value';
    case PublishMigrations = 'publish_migrations';
    case RectorSets = 'rector_sets';
}
