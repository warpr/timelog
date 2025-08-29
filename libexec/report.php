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

function help()
{
    echo "Usage: tl report\n";
    exit(1);
}

function main(array $files): void
{
    try {
        $timelog = new timelog_txt();
    } catch (\Exception $e) {
        echo 'Error: ' . $e->getMessage() . "\n";
        exit(1);
    }

    // Calculate the start of the current week (Monday)
    $today = new \DateTime();
    $days_since_monday = ($today->format('N') - 1) % 7; // Monday = 1
    $start_of_current_week = clone $today;
    $start_of_current_week->modify("-{$days_since_monday} days")->setTime(0, 0, 0);

    try {
        $entries = $timelog->get_entries_for_week($start_of_current_week->format('Y-m-d'));
        $summary = generate_time_summary($entries);
        print_report($summary);
    } catch (\Exception $e) {
        echo 'Error: ' . $e->getMessage() . "\n";
        exit(1);
    }
}

function generate_time_summary(array $entries): array
{
    $summary = [];
    $previous_entry = null;
    $first_entry_of_day = [];

    // First pass: identify which entries are the first of each day
    foreach ($entries as $entry_line) {
        $entry = timelog_txt::parse_log_line($entry_line);
        if ($entry === null) {
            continue;
        }

        if (!isset($first_entry_of_day[$entry['date']])) {
            $first_entry_of_day[$entry['date']] = $entry_line;
        }
    }

    // Second pass: calculate task durations, skipping first entries of each day
    foreach ($entries as $entry_line) {
        $entry = timelog_txt::parse_log_line($entry_line);
        if ($entry === null) {
            continue;
        }

        // Only count tasks if the previous entry wasn't the first entry of its day
        if ($previous_entry !== null && $previous_entry['date'] === $entry['date']) {
            $is_previous_first_of_day =
                $first_entry_of_day[$previous_entry['date']] === $previous_entry_line;

            if (!$is_previous_first_of_day) {
                $start_time = new \DateTime($previous_entry['datetime']);
                $end_time = new \DateTime($entry['datetime']);
                $duration = $end_time->getTimestamp() - $start_time->getTimestamp();

                $task_name = get_task_name($previous_entry['description']);

                if (!isset($summary[$task_name])) {
                    $summary[$task_name] = 0;
                }
                $summary[$task_name] += $duration;
            }
        }

        $previous_entry = $entry;
        $previous_entry_line = $entry_line;
    }

    // Sort by total time spent (descending)
    arsort($summary);

    return $summary;
}

function get_task_name(string $description): string
{
    // Handle non-productive tasks (ending with **)
    if (str_ends_with(trim($description), '**')) {
        return 'procrastination **';
    }

    // Handle project tasks (containing colon)
    if (strpos($description, ':') !== false) {
        $parts = explode(':', $description, 2);
        return trim($parts[0]);
    }

    return trim($description);
}

function print_report(array $summary): void
{
    echo "Report:\n\n";

    // Print procrastination first if it exists
    if (isset($summary['procrastination **'])) {
        $total_seconds = $summary['procrastination **'];
        $hours = floor($total_seconds / 3600);
        $minutes = floor(($total_seconds % 3600) / 60);

        $hours_str = $hours > 0 ? $hours . 'h' : '';
        printf("%6s %3sm    %s\n", $hours_str, $minutes, 'procrastination **');

        echo "\n"; // Empty line separator
        unset($summary['procrastination **']); // Remove from main list
    }

    // Print productive tasks
    foreach ($summary as $task_name => $total_seconds) {
        $hours = floor($total_seconds / 3600);
        $minutes = floor(($total_seconds % 3600) / 60);

        $hours_str = $hours > 0 ? $hours . 'h' : '';
        printf("%6s %3sm    %s\n", $hours_str, $minutes, $task_name);
    }
}
