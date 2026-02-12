# Wizard 1.4.0 Release

## What's New

### Features

- **Default Document Naming**: Documents now get default names when created
- **History Table Indexing**: History tables are now indexed by version for improved performance
- **Page ID**: The page_id will not be exposed to frontend

### Bug Fixes

- **[Issue#98] User Group**: User can see all project through different user_group
- **[Issue#115] Mind Map**: Guests can see mind_mapping in read_only now instead of login page
- **[Issue#165] Attachment**: Fixed attachment loss when moving pages
- **[Issue#181] User Group Privileges**: Fixed 404 error when revoking user_group privileges in

### Improvements

- **[Issue#118] Sidebar**: The sidebar and main content scroll independently

## Compatibility

This release has database schema changes.

- new field `external_id` in `wz_pages`
- new field `version` in `wz_page_histories`
