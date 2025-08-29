<?php
/**
 *   Copyright (C) 2025  Kuno Woudt <kuno@frob.nl>
 *
 *   This program is free software: you can redistribute it and/or modify it under the
 *   terms of the GNU Affero General Public License as published by the Free Software
 *   Foundation, either version 3 of the License, or (at your option) any later version.
 *
 *   SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

use timelog\timelog_txt;
use timelog\timezone;

function help() {
        echo "Usage: tl <activity description>\n";
        exit(1);
}

function main(array $files): void
{
    // Set timezone for consistent timestamp formatting
    timezone::set_default();

    if (empty($files)) {
        help();
    }

    try {
        $timelog = new timelog_txt();
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        exit(1);
    }

    $activity = implode(' ', $files);
    $timestamp = date('Y-m-d H:i');
    $current_date = date('Y-m-d');

    $last_line = $timelog->get_last_log_line();
    $add_day_separator = false;

    if ($last_line !== null) {
        $last_date = timelog_txt::extract_date_from_log_line($last_line);
        if ($last_date !== null && $last_date !== $current_date) {
            $add_day_separator = true;
        }
    }

    $log_entry = $timestamp . ': ' . $activity . "\n";
    if ($add_day_separator) {
        $log_entry = "\n" . $log_entry;
    }

    try {
        $timelog->append_log_entry($log_entry);
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        exit(1);
    }

    echo 'Logged: ' . trim($log_entry) . "\n";
}

