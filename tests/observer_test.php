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

namespace local_h5pchapter;

/**
 * Observer tests for local_h5pchapter.
 *
 * @package    local_h5pchapter
 * @category   test
 * @copyright  2026 Matheus Mathias
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers \local_h5pchapter\observer
 */
class observer_test extends \advanced_testcase {
    /**
     * Test course_module_deleted event cleans up the local_h5pchapter_settings table.
     */
    public function test_course_module_deleted() {
        global $DB, $CFG;
        require_once($CFG->dirroot . '/course/lib.php');

        $this->resetAfterTest();
        $this->setAdminUser();

        // 1. Create a course.
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();

        // 2. Create an h5pactivity module.
        $h5pactivity = $generator->create_module('h5pactivity', ['course' => $course->id]);

        // Get the course module id.
        $cm = get_coursemodule_from_instance('h5pactivity', $h5pactivity->id);
        $cmid = $cm->id;

        // 3. Manually insert a settings record for this module in our custom table.
        // We bypass the form creation here because we are explicitly testing the deletion event.
        $record = new \stdClass();
        $record->cmid = $cmid;
        $record->chapter_target = 'Introduction';
        $record->block_navigation = 1;
        $record->timecreated = time();
        $record->timemodified = time();
        $DB->insert_record('local_h5pchapter_settings', $record);

        // Verify the record exists before deletion.
        $this->assertTrue(
            $DB->record_exists('local_h5pchapter_settings', ['cmid' => $cmid]),
            'The settings record should exist before deleting the module.'
        );

        // 4. Delete the course module to trigger the core\event\course_module_deleted event.
        // Our observer should catch this and delete the settings.
        $cmactions = new \core_courseformat\local\cmactions($course);
        $cmactions->delete($cmid);

        // 5. Verify the record was successfully deleted.
        $this->assertFalse(
            $DB->record_exists('local_h5pchapter_settings', ['cmid' => $cmid]),
            'The settings record should be deleted automatically when the module is deleted.'
        );
    }
}
