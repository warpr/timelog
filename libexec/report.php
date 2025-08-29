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
use timelog\summary;

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
        $summary_data = summary::generate_time_summary($entries);
        summary::print_report($summary_data);
    } catch (\Exception $e) {
        echo 'Error: ' . $e->getMessage() . "\n";
        exit(1);
    }
}
