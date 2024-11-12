# Markdown Exporter for WordPress®

* * *

**Markdown Exporter for WordPress®** is a free WordPress® plugin that allows you to quickly convert your WordPress® content - posts, pages, and custom content types — into well structured Markdown (MD) files. 

Whether you're migrating content, backing up your site, or preparing to switch from WordPress® to a static site generator like [Stattic](https://stattic.site), this plugin provides a customizable and efficient solution.

## Table of Contents

- [Features](#features)
- [Installation](#installation)
- [Usage](#usage)
- [Screenshots](#screenshots)
- [Frequently Asked Questions](#frequently-asked-questions)
- [Changelog](#changelog)
- [Support](#support)
- [Contributing](#contributing)
- [License](#license)

## Features

- **Comprehensive Export Options**: Convert posts, pages, and any custom post types into Markdown files.
- **Customizable Settings**: Filter exports by post types, date range, author, post status, and taxonomies.
- **Support for ACF and Pods**: Includes Advanced Custom Fields (ACF) and Pods metadata in the export.
- **Real-Time Progress Bar**: Monitor the export process with a dynamic progress bar.
- **Export Log**: View detailed logs of the export process, including any issues encountered.
- **YAML Front Matter**: Generates Markdown files with YAML front matter for easy integration with static site generators.
- **Automatic Updates**: Stay up-to-date with the latest features and security patches via GitHub.
- **Parsedown Integration**: Converts post content from HTML to Markdown using Parsedown for accurate formatting.

## Installation

### Prerequisites

- **WordPress® Version**: 5.0 or higher
- **PHP Version**: 7.0 or higher

### Steps

1. **Download the Plugin**

    You can download the latest version of the plugin from the [GitHub repository](https://github.com/robertdevore/markdown-exporter-for-wordpress/).

    ```
    git clone https://github.com/robertdevore/markdown-exporter-for-wordpress.git
    ```

2. **Upload to WordPress®**

   - **Via FTP/SFTP**:
     - Upload the `markdown-exporter-for-wordpress` folder to the `/wp-content/plugins/` directory.

   - **Via WordPress® Admin Dashboard**:
     - Navigate to `Plugins > Add New > Upload Plugin`.
     - Click `Choose File` and select the `.zip` file of the plugin.
     - Click `Install Now` and then `Activate`.

3. **Activate the Plugin**

   After installation, activate the plugin through the `Plugins` menu in WordPress®.

## Usage

1. **Access the Settings Page**

   - Navigate to `Tools > Markdown Exporter` in your WordPress® admin dashboard.

2. **Configure Export Settings**

   - **Post Types**: Select specific post types to include in the export. Leave empty to export all public post types.
   - **Date Range**: Specify the start and end dates to filter the content by publication date.
   - **Author**: Choose a specific author to export content from, or select "All Authors".
   - **Post Status**: Filter by post status (e.g., Published, Draft) or select "All Statuses".
   - **Taxonomies**: Select specific taxonomies (e.g., Categories, Tags) to include in the export.

3. **Initiate Export**

   - Click the `Export` button to start the export process.
   - Monitor the progress through the real-time progress bar.

4. **Access Exported Files**

   - The exported ZIP file will be available in your WordPress® uploads directory.
   - Download and extract the ZIP to access your Markdown files organized by post type and slug.

## Frequently Asked Questions

### 1. **Can I export custom fields from Advanced Custom Fields (ACF) or Pods?**

Yes, the plugin supports exporting custom fields created with ACF and Pods. All post meta data is included in the YAML front matter of the Markdown files.

### 2. **What happens if the export process fails?**

If an error occurs during the export, it will be logged in the Export Log section on the settings page. Ensure that your server has the necessary PHP extensions, such as `ZipArchive`, enabled.

### 3. **Can I schedule exports to run automatically?**

Currently, the plugin does not support scheduled exports. However, you can contribute to the plugin or request this feature by opening an issue on the [GitHub repository](https://github.com/robertdevore/markdown-exporter-for-wordpress/issues).

### 4. **Is there a limit to the number of posts I can export?**

The export process is designed to handle a large number of posts efficiently. However, server limitations such as memory and execution time may affect the export of extremely large datasets. It's recommended to export in smaller batches using the date range filter if you encounter issues.

### 5. **How can I contribute to the plugin?**

Feel free to fork the repository and submit pull requests. For any issues or feature requests, please open an issue on the [GitHub repository](https://github.com/robertdevore/markdown-exporter-for-wordpress/issues).

## Support

For support, please visit the [Support Page](https://robertdevore.com/contact/) or open an issue on the [GitHub repository](https://github.com/robertdevore/markdown-exporter-for-wordpress/issues).

## Contributing

Contributions are welcome! Please follow these steps:

1. **Fork the Repository**

   Click the `Fork` button on the top right of the repository page.

2. **Clone Your Fork**

    ```
    git clone https://github.com/robertdevore/markdown-exporter-for-wordpress.git
    ```

3. **Create a New Branch**

    ```
    git checkout -b feature/your-feature-name
    ```

4. **Make Your Changes**

   Implement your feature or fix the bug.

5. **Commit Your Changes**

    ```
    git commit -m "Add feature: your-feature-name"
    ```

6. **Push to Your Fork**

    ```
    git push origin feature/your-feature-name
    ```

7. **Open a Pull Request**

   Navigate to the original repository and open a pull request.

Please ensure your code follows the existing coding standards and includes appropriate documentation and tests.

## License

This plugin is licensed under the [GNU General Public License v2.0 or later](http://www.gnu.org/licenses/gpl-2.0.html).

---

**Developed by [Robert DeVore](https://robertdevore.com/)**  
Visit [robertdevore.com](https://robertdevore.com/) for more information and other projects.
