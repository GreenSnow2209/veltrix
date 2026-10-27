# Changelog

All notable changes to `veltrix/laravel-admin` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-10-26

### Added
- Initial release of **Veltrix** — "Familiar to Build. Modern by Design."
- Complete functional parity with `z-song/laravel-admin` core APIs:
  - `Grid`: Columns, filters, sorting, quickSearch, pagination, actions, batch actions, CSV export.
  - `Form`: Text, Password, Email, Number, Textarea, Hidden, Display, Select, MultipleSelect, Radio, Checkbox, Switch, Date, Datetime, File, Image, BelongsToMany.
  - `Show`: Field detail displays, image viewer, relationship badges.
  - `Tree`: Hierarchical nested model view with branch customizations.
  - `Layout`: Content, Row, Column, Buildable contracts.
  - `Widgets`: Box, InfoBox, Navbar items.
- Modern SaaS UI:
  - Pure CSS design tokens with system-aware Dark/Light mode toggle.
  - Zero mandatory host build dependencies (no Livewire/Node/NPM requirement).
- Enterprise RBAC system:
  - Administrators, Roles, Permissions, Nested Menus, Operation Logs.
- Built-in Security Hardening:
  - Patch for CVE-2023-24249 (arbitrary file upload / RCE mitigation).
  - Multi-extension attack prevention and script blocking.
  - Strict server-side authorization on AJAX action endpoints.
  - Sensitive attribute protection on inline switch mutations.
  - Credential masking in operation audit logs.
- Legacy Compatibility Shims (`Encore\Admin\*`):
  - Drop-in aliases for `AdminController`, `AuthController`, `Grid`, `Form`, `Show`, `Tree`, `Content`, `Facades\Admin`, and legacy helpers.
- Full compatibility with Laravel 11.x, 12.x, and 13.x on PHP 8.2 through 8.5.
