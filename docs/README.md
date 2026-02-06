# Wizard - Lightweight Document Management System

## Overview

Wizard is an open-source document management system designed for modern development teams. It provides a unified platform to manage different types of technical documentation with powerful collaboration features.

### Supported Document Types

- **Markdown**: The primary document type, enhanced with templates, JSON-to-table conversion, and paste-to-upload image functionality
- **Swagger**: Full support for OpenAPI 3.0 specifications with integrated Swagger Editor, templates, full-screen editing, and auto-sync
- **Table**: Excel-like spreadsheet functionality powered by x-spreadsheet

### Key Features

- Version control with complete document modification history
- Visual document diff comparison
- User permission management (admin/regular users)
- Project grouping and organization
- LDAP authentication integration
- Multi-type search (documents, tags)
- Reading mode for distraction-free viewing
- Document commenting system
- Real-time notifications
- Document sharing capabilities
- Statistics and analytics
- Rich media support (flowcharts, sequence diagrams, pie charts, Tex LaTex formulas)
- Dark/light theme switching
- Auto-save to local storage to prevent data loss

## Quick Start with Docker

This modified version of Wizard comes with updated Docker configurations for improved compatibility, including support for Apple Silicon (macOS).

### Prerequisites

- Docker and Docker Compose installed
- At least 2GB of RAM available

### Launch with Docker Compose

For a painless setup, use the provided Docker Compose configuration:

```bash
docker-compose up -d
```

## Future Works

### Planned Features

- **Upgrade the Laravel Framework**: Upgrade the underlying Laravel framework to a modern version.
- **System Setting**: Admin can manage system settings directly from the web interface.
- **Fine-grained permission management**: A permission management system that allows strict but flexible access controls to different resources and actions.
- **Hash the exposure of ids**: Hash the entity ids to prevent direct exposure in URLs (e.g., document IDs).
- **More features for table type**: Export, diff and so on.
- **Https support**: Host the service by https protocal with docker

### Ongoing Work

- **Hash the exposure of document id**

## Technical Stack

- **Framework**: Laravel 5.8
- **Database**: MySQL/MariaDB
- **Search**: Multiple driver support (ElasticSearch, GoFound, ZincSearch)
- **Frontend**: Bootstrap Material Design, Editor.md, x-spreadsheet
- **Authentication**: JWT, LDAP
- **Export**: PDF (mPDF), CSV, ZIP

## License and Copyright

The original project is licensed under Apache 2.0. Copyright 管宜尧 \<[mylxsw@aicode.cc](mylxsw@aicode.cc)\>

Modified parts are licensed under MPL 2.0. Copyright Fendy \<[mylxsw@aicode.cc](mylxsw@aicode.cc)\>

## Contributing

Contributions are welcome! Please feel free to submit issues, feature requests, or pull requests.

## Support

For questions and support, please open an issue on the GitHub repository.
