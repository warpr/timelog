#!/usr/bin/env php
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

function get_timezone(): string
{
    // Try reading from timedatectl (systemd systems) first
    $timedatectl_output = shell_exec('timedatectl show --property=Timezone --value 2>/dev/null');
    if ($timedatectl_output !== null) {
        $system_timezone = trim($timedatectl_output);
        if (!empty($system_timezone)) {
            return $system_timezone;
        }
    }

    // Try reading from /etc/timezone (Debian/Ubuntu)
    if (file_exists('/etc/timezone')) {
        $system_timezone = trim(file_get_contents('/etc/timezone'));
        if (!empty($system_timezone)) {
            return $system_timezone;
        }
    }

    // Try macOS timezone detection via /etc/localtime symlink
    if (file_exists('/etc/localtime') && is_link('/etc/localtime')) {
        $link_target = readlink('/etc/localtime');
        if ($link_target !== false) {
            // Extract timezone from paths like /var/db/timezone/zoneinfo/Europe/Madrid
            if (preg_match('/\/zoneinfo\/(.+)$/', $link_target, $matches)) {
                return $matches[1];
            }
            // Also handle paths like /usr/share/zoneinfo/Europe/Madrid  
            if (preg_match('/\/usr\/share\/zoneinfo\/(.+)$/', $link_target, $matches)) {
                return $matches[1];
            }
        }
    }

    // Try to get timezone from PHP default
    $timezone = date_default_timezone_get();
    if (!empty($timezone) && $timezone !== 'UTC') {
        return $timezone;
    }

    // Fallback to Europe/Amsterdam for UTC-based timezones
    return 'Europe/Amsterdam';
}

function get_last_log_line(string $log_file): ?string
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

function extract_date_from_log_line(string $line): ?string
{
    if (preg_match('/^(\d{4}-\d{2}-\d{2})/', $line, $matches)) {
        return $matches[1];
    }
    return null;
}

function get_log_file_path(): string
{
    $home_dir = getenv('HOME');
    if ($home_dir === false) {
        echo "Error: Could not determine home directory\n";
        exit(1);
    }

    return $home_dir . '/timelog.txt';
}

function main(): void
{
    global $argv;

    if (count($argv) < 2) {
        echo "Usage: tl <activity description>\n";
        exit(1);
    }

    // Set timezone for consistent timestamp formatting
    $timezone = get_timezone();
    date_default_timezone_set($timezone);

    $activity = implode(' ', array_slice($argv, 1));
    $timestamp = date('Y-m-d H:i');
    $current_date = date('Y-m-d');

    $log_file = get_log_file_path();

    $last_line = get_last_log_line($log_file);
    $add_day_separator = false;

    if ($last_line !== null) {
        $last_date = extract_date_from_log_line($last_line);
        if ($last_date !== null && $last_date !== $current_date) {
            $add_day_separator = true;
        }
    }

    $log_entry = $timestamp . ': ' . $activity . "\n";
    if ($add_day_separator) {
        $log_entry = "\n" . $log_entry;
    }

    if (file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX) === false) {
        echo "Error: Could not write to log file\n";
        exit(1);
    }

    echo 'Logged: ' . trim($log_entry) . "\n";
}

