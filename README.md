# LLMs.txt for WP

LLMs.txt for WP is a WordPress plugin designed to generate machine-learning-friendly content from your site. It automatically creates an `llms.txt` file that compiles key content in a standardized format, making your site ready for Large Language Models (LLMs). This ensures that your content can be easily discovered and utilized by AI tools and applications.

Additionally, this plugin allows you to access **Markdown versions of your posts** by appending `.md` to the post URLs.

### About the `llms.txt` Standard

The `llms.txt` standard provides a simple, machine-readable file format for webmasters to communicate how their content should be utilized by Large Language Models (LLMs). It works similarly to `robots.txt`, enabling websites to specify which content is intended for training or reference by AI models. By implementing `llms.txt`, you can:

- **Promote Discovery**: Help LLMs understand the structure and purpose of your site.
- **Define Content Use**: Indicate permissions or restrictions on specific content.
- **Control Visibility**: Exclude or highlight specific sections of your site.

For more information on the `llms.txt` standard, visit the official website: [https://llmstxt.org/](https://llmstxt.org/).

### Key Features

- **Generate llms.txt**: Aggregate key content from your site into an easy-to-read format for machine learning models.
- **Markdown Support**: Generate Markdown versions of posts, suitable for LLMs or for lightweight sharing.
- **Fully Customizable**: Customize which page or posts are included via an intuitive admin settings page.

## Installation

1. Download the plugin ZIP file from the [releases page](https://github.com/WP-Autoplugin/llms-txt-for-wp/releases).
2. Upload it to your WordPress site via the **Plugins** > **Add New** > **Upload Plugin**.
3. Activate the plugin through the **Plugins** menu in WordPress.

## Usage

1. **Configure Plugin Settings**: Navigate to **Settings** > **LLMs.txt Settings**.
2. **Select Content**: Choose which page or post types to include in the `llms.txt` file.
3. **Markdown Support**: Enable Markdown output via settings and append `.md` to post URLs to view Markdown versions.
4. **Access llms.txt**: Open `https://yourdomain.com/llms.txt` to view the file ready for LLM consumption.

### Admin Settings

- **Selected Content for llms.txt**: Include specific pages or posts.
- **Post Types to Include**: Define which types of content (e.g., posts, pages) appear in `llms.txt`.
- **Post Limit**: Set the maximum number of posts to include.
- **Markdown Support**: Enable or disable `.md` file extension for post URLs.

## Frequently Asked Questions

### Why use the llms.txt standard?

The `llms.txt` standard allows webmasters to provide structured data for Large Language Models, similar to how `robots.txt` serves search engines. This can improve the discoverability and usability of your content by AI tools, while also giving you control over what is included. It's [quickly becoming a standard practice](https://directory.llmstxt.cloud/) for AI-friendly websites.

### How do I access the Markdown versions of posts?

Once Markdown support is enabled, append `.md` to any post's URL (e.g., `https://example.com/your-post.md`) to view it in Markdown format.

### Can I control which posts are included in llms.txt?

Yes! The plugin can be configured to include specific post types and limit the number of posts included in the `llms.txt` file. You can also choose to include a specific page only.

## Screenshots

1. **Admin Settings Page** - Easily manage content for `llms.txt` and enable Markdown support.

![Admin Settings Page](assets/screenshot-1.png)

2. **Markdown Output** - Example of a blog post converted to Markdown format.

![Markdown Output](assets/screenshot-2.png)

## Demo

You can view a demo of the plugin in action on my blog at [WebWizWork.com](https://www.webwizwork.com/llms.txt).

## Changelog

### 1.0.0

- Initial release of LLMs.txt for WP.
- Added functionality to generate `llms.txt`.
- Added Markdown support for posts.

## License

This plugin is licensed under the GPL v2 or later. For more information, please see the [GNU General Public License](https://www.gnu.org/licenses/gpl-2.0.html).

## Contributing

Contributions are welcome! Feel free to fork the repository, submit issues, or create pull requests.

---

**Note:** This plugin uses the [league/html-to-markdown](https://github.com/thephpleague/html-to-markdown) library for HTML to Markdown conversion.
