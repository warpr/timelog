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

function main(): void
{
    global $argv;

    if (count($argv) < 2) {
        echo "Usage: tl <activity description>\n";
        exit(1);
    }

    $activity = implode(' ', array_slice($argv, 1));
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = $timestamp . ': ' . $activity . "\n";

    $home_dir = getenv('HOME');
    if ($home_dir === false) {
        echo "Error: Could not determine home directory\n";
        exit(1);
    }

    $log_file = $home_dir . '/timelog.txt';

    if (file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX) === false) {
        echo "Error: Could not write to log file\n";
        exit(1);
    }

    echo 'Logged: ' . trim($log_entry) . "\n";
}

