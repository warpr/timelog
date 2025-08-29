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
    private string $log_file_path;

    function __construct() {
        $home_dir = getenv('HOME');
        if ($home_dir === false) {
            throw new \Exception("Could not determine home directory");
        }
        $this->log_file_path = $home_dir . '/timelog.txt';
    }

    function get_last_log_line(): ?string
    {
        if (!file_exists($this->log_file_path)) {
            return null;
        }

        $file = file($this->log_file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
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

    function append_log_entry(string $entry): bool
    {
        return file_put_contents($this->log_file_path, $entry, FILE_APPEND | LOCK_EX) !== false;
    }

    function get_log_file_path(): string
    {
        return $this->log_file_path;
    }
}
