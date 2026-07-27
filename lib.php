<?php
// This file is part of Moodle - http://moodle.org/
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
 * Library functions and callbacks for local_h5pchapter.
 *
 * @package    local_h5pchapter
 * @copyright  2026 Matheus Mathias
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Adds custom settings to the H5P activity settings form.
 *
 * @param object $formwrapper The form wrapper object.
 * @param \MoodleQuickForm $mform The form object.
 */
function local_h5pchapter_coursemodule_standard_elements($formwrapper, $mform) {
    $current = $formwrapper->get_current();

    // Check if it is an H5P Interactive Book activity.
    if (\local_h5pchapter\helper::is_interactive_book_form($current)) {
        $mform->addElement('header', 'local_h5pchapter_header', get_string('h5pchapter_control', 'local_h5pchapter'));

        $mform->addElement('text', 'chapter_target', get_string('chapter_target', 'local_h5pchapter'), ['maxlength' => 255]);
        $mform->addHelpButton('chapter_target', 'chapter_target', 'local_h5pchapter');
        $mform->setType('chapter_target', PARAM_TEXT);

        $mform->addElement('advcheckbox', 'block_navigation', get_string('block_navigation', 'local_h5pchapter'));
        $mform->addHelpButton('block_navigation', 'block_navigation', 'local_h5pchapter');
        $mform->setType('block_navigation', PARAM_BOOL);
        $mform->setDefault('block_navigation', 0);

        // Load existing settings if the module ID exists.
        if (!empty($current->coursemodule)) {
            global $DB;
            if ($setting = $DB->get_record('local_h5pchapter_settings', ['cmid' => $current->coursemodule])) {
                $mform->setDefault('chapter_target', $setting->chapter_target);
                $mform->setDefault('block_navigation', $setting->block_navigation);
            }
        }
    }
}

/**
 * Saves the custom settings when the H5P activity form is submitted.
 *
 * @param \stdClass $data The submitted form data.
 * @param \stdClass $course The course object.
 * @return \stdClass The modified form data.
 */
function local_h5pchapter_coursemodule_edit_post_actions($data, $course) {
    global $DB;

    // Only process H5P activities.
    if (empty($data->modulename) || $data->modulename !== 'h5pactivity' || empty($data->coursemodule)) {
        return $data;
    }

    $cmid = (int)$data->coursemodule;

    // Verify if the activity is an Interactive Book.
    if (!\local_h5pchapter\helper::is_interactive_book_form($data) && !\local_h5pchapter\helper::is_interactive_book_cm($cmid)) {
        // Remove settings if it is no longer an Interactive Book.
        $DB->delete_records('local_h5pchapter_settings', ['cmid' => $cmid]);
        return $data;
    }

    $record = new \stdClass();
    $record->cmid = $cmid;
    $record->chapter_target = $data->chapter_target ?? '';
    $record->block_navigation = !empty($data->block_navigation) ? 1 : 0;
    $record->timemodified = time();

    if ($existing = $DB->get_record('local_h5pchapter_settings', ['cmid' => $record->cmid])) {
        $record->id = $existing->id;
        $DB->update_record('local_h5pchapter_settings', $record);
    } else {
        $record->timecreated = time();
        $DB->insert_record('local_h5pchapter_settings', $record);
    }

    return $data;
}
