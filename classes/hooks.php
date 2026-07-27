<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin version and other meta-data are defined here.
 *
 * @package     local_h5pchapter
 * @copyright   2026 Matheus Mathias
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_h5pchapter;

use core\hook\output\before_standard_top_of_body_html_generation;

/**
 * Class hooks for H5PChapter.
 *
 * @package    local_h5pchapter
 */
class hooks {
    /**
     * Injects the JS into the student's view page using PSR-14 hooks.
     */
    public static function before_standard_top_of_body_html_generation(before_standard_top_of_body_html_generation $hook) {
        global $PAGE, $DB;

        // 1. The Parent (Course Page)
        if ($PAGE->pagetype === 'mod-h5pactivity-view' && !empty($PAGE->cm->id)) {
            if (helper::is_interactive_book_cm((int)$PAGE->cm->id)) {
                if ($setting = $DB->get_record('local_h5pchapter_settings', ['cmid' => $PAGE->cm->id])) {
                    if (!empty($setting->chapter_target) || !empty($setting->block_navigation)) {
                        $params = [
                            'chapter_target' => $setting->chapter_target,
                            'block_navigation' => (bool)$setting->block_navigation,
                        ];
                        // Inject script in parent mode.
                        $PAGE->requires->js_call_amd('local_h5pchapter/deeplink', 'initParent', [$params]);
                    }
                }
            }
        } else if ($PAGE->pagelayout === 'embedded') {
            // 2. The Worker (Inside the Iframe).
            // Inject script in iframe mode (without params, it will ask the parent).
            $PAGE->requires->js_call_amd('local_h5pchapter/deeplink', 'initIframe', []);
        }
    }
}
