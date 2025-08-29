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

class timelog_txt
{
    private string $log_file_path;

    function __construct()
    {
        $home_dir = getenv('HOME');
        if ($home_dir === false) {
            throw new \Exception('Could not determine home directory');
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

    function append_log_entry(string $entry): void
    {
        if (file_put_contents($this->log_file_path, $entry, FILE_APPEND | LOCK_EX) === false) {
            throw new \Exception('Could not write to log file: ' . $this->log_file_path);
        }
    }

    function get_log_file_path(): string
    {
        return $this->log_file_path;
    }

    function get_all_log_entries(): array
    {
        if (!file_exists($this->log_file_path)) {
            return [];
        }

        $file = file($this->log_file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($file === false) {
            throw new \Exception('Could not read log file: ' . $this->log_file_path);
        }

        return $file;
    }

    function get_entries_for_week(string $start_date): array
    {
        $entries = $this->get_all_log_entries();
        $filtered_entries = [];

        $start_timestamp = strtotime($start_date);
        $end_timestamp = $start_timestamp + 7 * 24 * 60 * 60; // 7 days

        foreach ($entries as $entry) {
            $date = self::extract_date_from_log_line($entry);
            if ($date === null) {
                continue;
            }

            $entry_timestamp = strtotime($date);
            if ($entry_timestamp >= $start_timestamp && $entry_timestamp < $end_timestamp) {
                $filtered_entries[] = $entry;
            }
        }

        return $filtered_entries;
    }

    static function parse_log_line(string $line): ?array
    {
        if (preg_match('/^(\d{4}-\d{2}-\d{2}) (\d{2}:\d{2}): (.+)$/', $line, $matches)) {
            return [
                'date' => $matches[1],
                'time' => $matches[2],
                'datetime' => $matches[1] . ' ' . $matches[2],
                'description' => $matches[3],
            ];
        }
        return null;
    }
}
