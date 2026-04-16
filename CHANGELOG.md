# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-04-16

### Added
- Initial release with full multilingual blog functionality
- Support for 5 languages: EN, UK, DE, FR, ES
- Blog posts with title, excerpt, content, featured images
- Categories with hierarchical structure
- Tags system with usage counting
- Comments with threading support
- Ratings (1-5 stars) and favorites
- SEO features: slugs, meta tags, Open Graph
- RSS feed integration
- AI content generation (Anthropic/OpenAI)
- Filament v4 admin panel resources
- Auto-loading migrations
- Tailwind CSS styled frontend views
- Configuration-driven architecture
- Event system for blog lifecycle
- Caching support with configurable TTL
- Database seeders for sample data

## [Unreleased]

### Added
- `blog:update` command for easy package updates
- CHANGELOG.md for version tracking

### Changed
- Improved route ordering to prevent conflicts
- Fixed UserBlogController middleware compatibility

### Fixed
- Route conflicts between public and user-specific routes
- Category route model binding resolution
- MediaLibrary conditional loading in views
