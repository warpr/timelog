<?php
/**
 *   Copyright (C) 2024  Kuno Woudt <kuno@frob.nl>
 *
 *   This program is free software: you can redistribute it and/or modify it under the
 *   terms of the GNU Affero General Public License as published by the Free Software
 *   Foundation, either version 3 of the License, or (at your option) any later version.
 *
 *   SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace timelog;

class timelog_txt {
    static function get_last_log_line(string $log_file): ?string
    {
        if (!file_exists($log_file)) {
            return null;
        }

        $file = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($file === false || empty($file)) {
            return null;
        }

        return end($file);
    }

    static function extract_date_from_log_line(string $line): ?string
    {
        if (preg_match('/^(\d{4}-\d{2}-\d{2})/', $line, $matches)) {
            return $matches[1];
        }
        return null;
    }

    static function get_log_file_path(): string
    {
        $home_dir = getenv('HOME');
        if ($home_dir === false) {
            echo "Error: Could not determine home directory\n";
            exit(1);
        }

        return $home_dir . '/timelog.txt';
    }
}
