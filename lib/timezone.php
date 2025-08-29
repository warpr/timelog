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

class timezone {
static function get_timezone(): string
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

    static function set_default() {
        $timezone = static::get_timezone();
        date_default_timezone_set($timezone);
    }
}
