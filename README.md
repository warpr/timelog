timelog
=======

A simple command-line time logging utility written in PHP. This tool helps you track your activities by logging timestamped entries to a text file.

## Usage

Use the `tl` command followed by a description of your activity:

```bash
tl working on timelog project
tl lunch break
tl meeting with client
```

Each entry is logged with a timestamp in the format `YYYY-MM-DD HH:MM: activity description` and saved to `~/timelog.txt`.

The tool automatically adds day separators (blank lines) when logging activities on different dates.

## Installation

Add the `bin/` directory to your PATH to use the `tl` command from anywhere.

## Log Format

The log format is compatible with gtimelog. For more information about the format, see: https://gtimelog.org/formats.html


Open-source, not open-contribution
----------------------------------

This project is open source but closed to contributions.

This project is not my day job, and the enjoyment I get out of this project is mainly
down to these two properties:

1. There are no deadlines, I can work on a feature as long as I want.
2. I can make completely unreasonable and stubborn technology choices.

Collaborating on features or accepting and maintaining third party patches would
compromise this.

License
=======

Copyright 2025 Kuno Woudt <kuno@frob.nl>

This program is free software: you can redistribute it and/or modify it under the
terms of the GNU Affero General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later version.

See [LICENSE.md](LICENSE.md).

SPDX-License-Identifier: AGPL-3.0-or-later

