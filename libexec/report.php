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

    // FIXME: read the timelog file and summarize the time spent in the previous week.
    // Output should look like something this:
    //
    // Report:
    //
    //     2h 59m    procrastination
    //
    //     4h  5m    timelog
    //    15h  3m    deep-10753
    //     1h 11m    office hours
    //
    // The first line of each day starts the time for the tasks of that day, but is not itself
    // task.  Each subsequent line of the same day records the end time of a task.
    // So the duration of each task can be calculated by taking the date/time of the previous
    // line as the start time and the date/time of the current line as the end time.
    //
    // Some task descriptions are special:
    // any task ending in "**" is a non-productive task, and should be counted as "procrastination"
    // any task with a colon in the activity description is part of a project, these tasks should
    // grouped together by their project title.
    //
    // So, for example if you encounter these two tasks on a day:
    //
    // 2025-08-27 10:00: arrived
    // 2025-08-27 10:50: deep-10753: write API tests
    // 2025-08-27 11:20: deep-10753: PUT test
    //
    // Then the summary for that day would include  "1h 20m    deep-10753", as the first task took
    // 50 minutes, the second task 30 minutes, and the tasked are grouped together by their project name.
}
