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
 * Backup plugin class for local_h5pchapter.
 *
 * @package    local_h5pchapter
 * @copyright  2026 Matheus Mathias
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Provides the steps to backup H5P Chapter plugin data.
 *
 * @package    local_h5pchapter
 */
class backup_local_h5pchapter_plugin extends backup_local_plugin {
    /**
     * Define course-level plugin structure.
     * All data is wrapped at the course level as this is a local plugin.
     *
     * @return backup_plugin_element
     */
    protected function define_course_plugin_structure(): backup_nested_element {
        global $DB;
        
        $plugin = $this->get_plugin_element(null);

        // Use the recommended plugin wrapper to encapsulate all plugin data.
        $pluginwrapper = new backup_nested_element($this->get_recommended_name());
        $plugin->add_child($pluginwrapper);

        // Module-level settings container node.
        $modulesnode = new backup_nested_element('h5pchapter_settings');
        $pluginwrapper->add_child($modulesnode);

        $modulenode = new backup_nested_element('h5pchapter_setting', ['id'], ['cmid', 'chapter_target', 'block_navigation', 'timecreated', 'timemodified']);
        $modulesnode->add_child($modulenode);

        $courseid = $this->step->get_task()->get_courseid();

        // Get all settings for this course's modules.
        $sql = "SELECT hs.* 
                  FROM {local_h5pchapter_settings} hs
                  JOIN {course_modules} cm ON cm.id = hs.cmid
                 WHERE cm.course = ?";
        $records = $DB->get_records_sql($sql, [$courseid]);
        
        $modulesdata = [];
        if ($records) {
            foreach ($records as $record) {
                $modulesdata[] = (array)$record;
            }
        }
        $modulenode->set_source_array($modulesdata);

        return $plugin;
    }
}
