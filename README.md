# H5P Page Controller

This is a local Moodle plugin that provides control over H5P Interactive Book activities. It allows administrators and teachers to automatically unlock specific pages and restrict navigation, ensuring a more guided learning experience.

## Features

- **Target Pages**: Specify one or more page numbers to be unlocked automatically when the student accesses the activity.
- **Block Navigation**: Hide the side menu and navigation arrows in the Interactive Book, preventing students from manually navigating to other pages.

## Requirements

- Moodle 4.5 or later.
- Moodle Core H5P activity module (`mod_h5pactivity`). 
  **Note**: This plugin only works with the native H5P integration in Moodle Core and does **NOT** support the third-party `mod_hvp` plugin.
- H5P Interactive Book content type.

## Installation

1. Clone or download this repository.
2. Extract the folder into `local/h5pchapter` in your Moodle directory.
3. Log in as an administrator and go to **Site administration** > **Notifications** to install the plugin.

## Usage

1. Add or edit an **H5P** activity in your course.
2. Ensure the content type used is an **Interactive Book**.
3. In the activity settings form, locate the **H5P Page Control** section.
4. Configure the settings:
   - **Target Page**: Enter the page numbers to unlock, separated by commas (e.g., `1, 3, 4`).
   - **Block navigation**: Check this box to hide the side menu and navigation arrows.
5. Save the activity.

## License

This plugin is licensed under the [GNU General Public License v3 or later](http://www.gnu.org/copyleft/gpl.html).
