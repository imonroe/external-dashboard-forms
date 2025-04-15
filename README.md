# External Dashboard Form

External Dashboard Form is a WordPress plugin that allows administrators to embed custom external forms directly on the WordPress dashboard screen, with dynamic management of form instances via a sidebar interface.  This is designed to work particularly well with [n8n form trigger forms](https://docs.n8n.io/integrations/builtin/core-nodes/n8n-nodes-base.formtrigger/), which only require a single webhook URL for embedding.

## Features

- Add multiple custom external forms via a sidebar UI
- Embed forms directly on the main dashboard screen as draggable widgets (dashboard meta boxes)
- Manage form name and secure URL configuration
- Remove custom forms when no longer needed
- See quick access links to each form in the admin sidebar

## Requirements

- WordPress 5.0 or higher
- PHP 7.0 or higher

## Installation

1. [Download the latest release](https://github.com/imonroe/external-dashboard-forms/archive/refs/tags/1.0.0.zip) as a zip file.
2. Upload the zip file to your Wordpress site (`/wp-admin/plugin-install.php`), using the "Upload Plugin" button.
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Go to the 'External Forms' menu in the admin sidebar

## Usage

### Adding a Form

1. Navigate to the "External Forms" menu in the admin sidebar
2. Enter a name for your form and a secure URL (must begin with https://)
3. Click "Add Form"
4. The form will now appear as a widget on your dashboard and as a link in the sidebar

### Removing a Form

1. Go to the dashboard
2. In the form widget, click the "Screen Options" at the top of the page
3. Click "Remove Form" in the widget settings
4. The form will be removed from both the dashboard and the sidebar

## Security

- Only users with the `manage_options` capability can add or remove forms
- Only secure URLs (https://) are accepted

## License

GPL v2 or later
