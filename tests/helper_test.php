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
 * Helper tests for local_h5pchapter.
 *
 * @package    local_h5pchapter
 * @category   test
 * @copyright  2026 Matheus Mathias
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers \local_h5pchapter\helper
 */
final class helper_test extends \advanced_testcase {
    /**
     * Test the is_interactive_book_json method with various json formats.
     */
    public function test_is_interactive_book_json(): void {
        $this->resetAfterTest();

        // Scenario 1: Valid JSON with mainLibrary as a simple string.
        $json = json_encode([
            'mainLibrary' => 'H5P.InteractiveBook',
        ]);
        $this->assertTrue(helper::is_interactive_book_json($json), 'Should return true for mainLibrary string match.');

        // Scenario 2: Valid JSON with mainLibrary as an array containing machineName.
        $json = json_encode([
            'mainLibrary' => [
                'machineName' => 'H5P.InteractiveBook',
            ],
        ]);
        $this->assertTrue(helper::is_interactive_book_json($json), 'Should return true for mainLibrary array machineName match.');

        // Scenario 3: Valid JSON with mainLibrary as an array containing name.
        $json = json_encode([
            'mainLibrary' => [
                'name' => 'H5P.InteractiveBook',
            ],
        ]);
        $this->assertTrue(helper::is_interactive_book_json($json), 'Should return true for mainLibrary array name match.');

        // Scenario 4: Valid JSON, but not an Interactive Book.
        $json = json_encode([
            'mainLibrary' => 'H5P.Quiz',
        ]);
        $this->assertFalse(helper::is_interactive_book_json($json), 'Should return false for non-Interactive Book libraries.');

        // Scenario 5: Interactive book as a preloaded dependency (fallback).
        $json = json_encode([
            'preloadedDependencies' => [
                ['machineName' => 'H5P.Quiz'],
                ['machineName' => 'H5P.InteractiveBook'],
            ],
        ]);
        $this->assertTrue(helper::is_interactive_book_json($json), 'Should return true if found in preloadedDependencies array.');

        // Scenario 6: Preloaded dependency as a string (fallback).
        $json = json_encode([
            'preloadedDependencies' => [
                'H5P.InteractiveBook',
            ],
        ]);
        $this->assertTrue(helper::is_interactive_book_json($json), 'Should return true if found in preloadedDependencies strings.');

        // Scenario 7: Invalid JSON string.
        $this->assertFalse(helper::is_interactive_book_json('invalid json data'), 'Should return false for invalid JSON string.');

        // Scenario 8: Empty JSON object or missing library data.
        $this->assertFalse(helper::is_interactive_book_json('{}'), 'Should return false for empty JSON object.');
        $this->assertFalse(
            helper::is_interactive_book_json('{"otherProp": true}'),
            'Should return false for JSON missing library info.'
        );
    }

    /**
     * Test the is_interactive_book_form method with invalid inputs.
     */
    public function test_is_interactive_book_form_invalid_inputs(): void {
        $this->resetAfterTest();

        // Null or non-object.
        $this->assertFalse(helper::is_interactive_book_form(null));
        $this->assertFalse(helper::is_interactive_book_form('some string'));
        $this->assertFalse(helper::is_interactive_book_form(['modulename' => 'h5pactivity']));

        // Object without modulename.
        $this->assertFalse(helper::is_interactive_book_form(new \stdClass()));

        // Object with wrong modulename.
        $obj = new \stdClass();
        $obj->modulename = 'assign';
        $this->assertFalse(helper::is_interactive_book_form($obj));
    }

    /**
     * Test the is_interactive_book_cm method with empty/invalid course module IDs.
     */
    public function test_is_interactive_book_cm_invalid(): void {
        $this->resetAfterTest();

        // Should return false for 0 or negative cmid.
        $this->assertFalse(helper::is_interactive_book_cm(0));
        $this->assertFalse(helper::is_interactive_book_cm(-1));
    }
}
