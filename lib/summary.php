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

namespace timelog;

class summary
{
    static function group_entries_by_date(array $entries): array
    {
        $entries_by_date = [];
        foreach ($entries as $entry_line) {
            $entry = timelog_txt::parse_log_line($entry_line);
            if ($entry === null) {
                continue;
            }
            $entries_by_date[$entry['date']][] = $entry;
        }
        return $entries_by_date;
    }

    static function generate_time_summary(array $entries): array
    {
        $summary = [];

        // Group entries by date
        $entries_by_date = self::group_entries_by_date($entries);

        // Process each day separately
        foreach ($entries_by_date as $date => $day_entries) {
            if (count($day_entries) < 2) {
                // Skip days with only one entry (just start time, no tasks)
                continue;
            }

            // First entry is the start time, not a task
            $start_time = new \DateTime($day_entries[0]['datetime']);

            // Process each subsequent entry as the END of a task
            // Entry i=1 ends the first task (from i=0 to i=1)
            // Entry i=2 ends the second task (from i=1 to i=2), etc.
            for ($i = 1; $i < count($day_entries); $i++) {
                $task_start_entry = $day_entries[$i - 1];
                $task_end_entry = $day_entries[$i];

                $task_start_time = new \DateTime($task_start_entry['datetime']);
                $task_end_time = new \DateTime($task_end_entry['datetime']);
                $duration = $task_end_time->getTimestamp() - $task_start_time->getTimestamp();

                // The task description is from the END entry, not the start entry
                $task_name = self::get_task_name($task_end_entry['description']);

                if (!isset($summary[$task_name])) {
                    $summary[$task_name] = 0;
                }
                $summary[$task_name] += $duration;
            }

            // Handle the last task of the day
            $last_task_entry = end($day_entries);
            $start_of_last_task = new \DateTime($last_task_entry['datetime']);
            $now = new \DateTime();

            // If it's today, calculate until now, otherwise assume end of workday
            if ($date === $now->format('Y-m-d')) {
                $end_of_last_task = $now;
            } else {
                // Assume the task went until 18:00 (6 PM) for past days
                $end_of_last_task = new \DateTime($date . ' 18:00');
                // But if that would be before the start time, use start time + 1 hour as fallback
                if ($end_of_last_task <= $start_of_last_task) {
                    $end_of_last_task = clone $start_of_last_task;
                    $end_of_last_task->modify('+1 hour');
                }
            }

            $duration = $end_of_last_task->getTimestamp() - $start_of_last_task->getTimestamp();

            // Only count positive durations
            if ($duration > 0) {
                $task_name = self::get_task_name($last_task_entry['description']);

                if (!isset($summary[$task_name])) {
                    $summary[$task_name] = 0;
                }
                $summary[$task_name] += $duration;
            }
        }

        // Sort by total time spent (descending)
        arsort($summary);

        return $summary;
    }

    static function get_task_name(string $description): string
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

    static function print_report(array $summary): void
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

        // Print productive tasks and calculate total
        // Sort productive tasks alphabetically
        ksort($summary);

        $total_productive_seconds = 0;
        foreach ($summary as $task_name => $total_seconds) {
            $hours = floor($total_seconds / 3600);
            $minutes = floor(($total_seconds % 3600) / 60);

            $hours_str = $hours > 0 ? $hours . 'h' : '';
            printf("%6s %3sm    %s\n", $hours_str, $minutes, $task_name);

            $total_productive_seconds += $total_seconds;
        }

        // Print total productive time if there are productive tasks
        if ($total_productive_seconds > 0) {
            echo "\n";
            $hours = floor($total_productive_seconds / 3600);
            $minutes = floor(($total_productive_seconds % 3600) / 60);
            $hours_str = $hours > 0 ? $hours . 'h' : '';
            printf("%6s %3sm    %s\n", $hours_str, $minutes, 'total productive time');
        }
    }
}
