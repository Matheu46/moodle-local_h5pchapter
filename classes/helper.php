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

defined('MOODLE_INTERNAL') || die();

/**
 * Helper class for local_h5pchapter.
 *
 * @package    local_h5pchapter
 * @copyright  2026 Matheus Mathias
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class helper {
    /**
     * Checks if the given form data / module object is an H5P Interactive Book.
     *
     * @param object $current Form current data object (from $formwrapper->get_current()).
     * @return bool True if the H5P activity is Interactive Book, false otherwise.
     */
    public static function is_interactive_book_form($current): bool {
        global $USER;

        if (empty($current) || !is_object($current)) {
            return false;
        }

        $modulename = $current->modulename ?? '';
        if ($modulename !== 'h5pactivity') {
            return false;
        }

        // 1. Try checking by course module ID if available.
        $cmid = $current->coursemodule ?? 0;
        if (!empty($cmid)) {
            if (self::is_interactive_book_cm((int)$cmid)) {
                return true;
            }
        }

        // 2. If not found or new module, try checking draft area.
        $draftitemid = $current->package ?? 0;
        if (!empty($draftitemid)) {
            $fs = get_file_storage();
            try {
                $usercontext = \context_user::instance($USER->id);
                $files = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftitemid, 'id', false);
                foreach ($files as $file) {
                    if (!$file->is_directory() && strtolower(pathinfo($file->get_filename(), PATHINFO_EXTENSION)) === 'h5p') {
                        if (self::is_interactive_book_file($file)) {
                            return true;
                        }
                    }
                }
            } catch (\Exception $e) {
                // Ignore context/permission exceptions.
            }
        }

        return false;
    }

    /**
     * Checks if the course module with the given cmid is an H5P Interactive Book.
     *
     * @param int $cmid Course module ID.
     * @return bool
     */
    public static function is_interactive_book_cm(int $cmid): bool {
        if (empty($cmid)) {
            return false;
        }

        $fs = get_file_storage();
        try {
            $context = \context_module::instance($cmid);
            $files = $fs->get_area_files($context->id, 'mod_h5pactivity', 'package', 0, 'id', false);
            foreach ($files as $file) {
                if (!$file->is_directory() && strtolower(pathinfo($file->get_filename(), PATHINFO_EXTENSION)) === 'h5p') {
                    if (self::is_interactive_book_file($file)) {
                        return true;
                    }
                }
            }
        } catch (\Exception $e) {
            // Module context not found.
        }

        return false;
    }

    /**
     * Checks if a stored .h5p file belongs to the H5P.InteractiveBook library.
     *
     * @param \stored_file $file
     * @return bool
     */
    public static function is_interactive_book_file(\stored_file $file): bool {
        global $DB;

        // Check Moodle DB table {h5p} and {h5p_libraries} first.
        $pathnamehash = $file->get_pathnamehash();
        $sql = "SELECT lib.name
                  FROM {h5p} h
                  JOIN {h5p_libraries} lib ON lib.id = h.mainlibraryid
                 WHERE h.pathnamehash = :pathnamehash";
        try {
            $libname = $DB->get_field_sql($sql, ['pathnamehash' => $pathnamehash]);
            if ($libname) {
                return (strpos($libname, 'H5P.InteractiveBook') !== false);
            }
        } catch (\Exception $e) {
            // DB query fail or tables missing.
        }

        // If not found in DB, inspect h5p.json inside the zip.
        return self::check_zip_is_interactive_book($file);
    }

    /**
     * Inspects a stored .h5p file archive to check if its main library is H5P.InteractiveBook.
     *
     * @param \stored_file $file
     * @return bool
     */
    protected static function check_zip_is_interactive_book(\stored_file $file): bool {
        if (!class_exists('\ZipArchive')) {
            return false;
        }

        $tempdir = make_request_directory();
        $temppath = $tempdir . '/package.h5p';

        if (!$file->copy_content_to($temppath)) {
            return false;
        }

        $isib = false;
        $zip = new \ZipArchive();
        if ($zip->open($temppath) === true) {
            $jsoncontent = $zip->getFromName('h5p.json');
            $zip->close();

            if ($jsoncontent) {
                $isib = self::is_interactive_book_json($jsoncontent);
            }
        }

        return $isib;
    }

    /**
     * Parses h5p.json content to check for Interactive Book library.
     *
     * @param string $jsoncontent
     * @return bool
     */
    public static function is_interactive_book_json(string $jsoncontent): bool {
        $data = json_decode($jsoncontent, true);
        if (!$data || !is_array($data)) {
            return false;
        }

        // Check mainLibrary
        if (!empty($data['mainLibrary'])) {
            $mainlib = $data['mainLibrary'];
            $name = is_array($mainlib) ? ($mainlib['machineName'] ?? $mainlib['name'] ?? '') : (string)$mainlib;
            if (strpos($name, 'H5P.InteractiveBook') !== false) {
                return true;
            }
        }

        // Check preloadedDependencies as fallback
        if (!empty($data['preloadedDependencies']) && is_array($data['preloadedDependencies'])) {
            foreach ($data['preloadedDependencies'] as $dep) {
                $name = is_array($dep) ? ($dep['machineName'] ?? $dep['name'] ?? '') : (string)$dep;
                if (strpos($name, 'H5P.InteractiveBook') !== false) {
                    return true;
                }
            }
        }

        return false;
    }
}
