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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Restore plugin class for local_h5pchapter.
 *
 * @package    local_h5pchapter
 * @copyright  2026 Matheus Mathias
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Provides the steps to restore H5P Chapter plugin data.
 *
 * @package    local_h5pchapter
 */
class restore_local_h5pchapter_plugin extends restore_local_plugin {

    /** @var array Temporary storage for module settings during XML reading. */
    protected $moduleconfigs = [];

    /**
     * Define the restore plugin structure.
     *
     * @return restore_path_element[]
     */
    protected function define_course_plugin_structure(): array {
        return [
            new restore_path_element('h5pchapter_setting', $this->get_pathfor('/h5pchapter_settings/h5pchapter_setting')),
        ];
    }

    /**
     * Temporarily store a module-level setting element from the XML.
     *
     * @param array $data Record data from the backup file.
     * @return void
     */
    public function process_h5pchapter_setting($data) {
        $this->moduleconfigs[] = (object)$data;
    }

    /**
     * Executes after the course and all its modules have been fully restored.
     * Processes and maps IDs for the stored configurations.
     *
     * @return void
     */
    public function after_restore_course() {
        global $DB;
        
        if (empty($this->moduleconfigs)) {
            return;
        }

        foreach ($this->moduleconfigs as $setting) {
            // Map the original module ID to the newly restored module ID.
            $newcmid = $this->get_mappingid('course_module', $setting->cmid);

            // Fallback to 'module' mapping if 'course_module' is not found.
            if (empty($newcmid)) {
                $newcmid = $this->get_mappingid('module', $setting->cmid);
            }

            if (!empty($newcmid)) {
                $newrecord = new stdClass();
                $newrecord->cmid = $newcmid;
                $newrecord->chapter_target = $setting->chapter_target;
                $newrecord->block_navigation = $setting->block_navigation;
                $newrecord->timecreated = isset($setting->timecreated) ? $setting->timecreated : time();
                $newrecord->timemodified = isset($setting->timemodified) ? $setting->timemodified : time();

                // Check if already exists just in case
                $existing = $DB->get_record('local_h5pchapter_settings', ['cmid' => $newcmid]);
                if (!$existing) {
                    $DB->insert_record('local_h5pchapter_settings', $newrecord);
                } else {
                    $newrecord->id = $existing->id;
                    $DB->update_record('local_h5pchapter_settings', $newrecord);
                }
            }
        }
    }
}
