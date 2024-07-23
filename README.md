# Vulpes LMS

Vulpes LMS is a Learning Management System (LMS) plugin for WordPress. This plugin allows administrators to manage training courses, employees, groups, and track training logs. The plugin includes features for custom user roles, custom fields, and more.

## Features

- Custom user roles: Administrator, Superuser, Manager, Employee
- Custom user profile fields: Position, Manager, Location, Group
- Manage employee groups and subject groups
- Create and manage training courses
- Log training records for employees
- Upload and manage training documents
- Frontend shortcodes for displaying user profiles and training logs

## Installation

1. Download the plugin zip file.
2. In your WordPress admin panel, go to Plugins > Add New.
3. Click Upload Plugin and choose the downloaded zip file.
4. Click Install Now and then Activate the plugin.

## Shortcodes

### User Profile

Display the logged-in user's profile information.

```shortcode
[academy_user_profile]
```

### User Training Log
Display the logged-in user's training log.

```shortcode
[academy_user_training_log]
```

## Admin Pages

### Vulpes LMS
An information page for the Vulpes LMS plugin.

### Training Log
View and manage the training log for all employees. Search by employee or course and sort the table by different columns.

### Training Courses
Create and manage training courses. Assign courses to subject groups and set competency scores.

### Subject Groups
Create and manage subject groups. Assign courses to subject groups.

### Employees
Add and manage employees. Assign employees to groups and log their training records.

### Employee Groups
Create and manage employee groups. Assign employees to groups and set group managers.

## Additional Features

### Custom User Roles
- Administrator: Full access to all plugin features.
- Superuser: Equivalent to the Editor role.
- Manager: Equivalent to the Author role.
- Employee: Equivalent to the Subscriber role.

### Custom User Profile Fields
- Position
- Manager
- Location
- Group

### File Uploads
Training documents are uploaded to the vulpes_lms_uploads directory in the WordPress uploads folder.

## Contributing
Contributions are welcome! Please fork the repository and submit a pull request.

### License
This plugin is licensed under the GPLv2 or later.

### Author
SFDIGITAL
https://strayfoxdigital.com

### Plugin URI
https://academy.strayfox.co.uk
