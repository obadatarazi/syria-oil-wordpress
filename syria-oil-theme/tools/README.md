# WXR to Demo JSON Converter

This tool converts WordPress eXtended RSS (WXR) export files into a simplified JSON format for use in the theme's demo import system.

## Purpose

When you have a WordPress export file from a production site, this tool extracts:
- **Categories** - All unique categories with their names and slugs
- **Sample Posts** - A configurable number of sample posts (default: 8) for demo purposes

The generated JSON file is then used by the theme's one-click demo import to populate the site with realistic content structure.

## Usage

### From Command Line

```bash
cd syria-oil-theme/tools
php convert-wxr-to-json.php
```

### From Browser

Navigate to:
```
http://yoursite.com/wp-content/themes/syria-oil-theme/tools/convert-wxr-to-json.php
```

## Configuration

Edit the `convert-wxr-to-json.php` file to adjust:

```php
// Source WXR file
$wxr_file = '/Users/obadaal-tarazi/Downloads/WordPress.2026-08-17.xml';

// Output JSON file location
$json_file = __DIR__ . '/../demo-content/demo-from-production.json';

// Number of sample posts to extract
$post_limit = 8;
```

## Output

The tool generates a JSON file with this structure:

```json
{
    "categories": [
        {
            "slug": "category-slug",
            "name": "Category Name"
        }
    ],
    "posts": [
        {
            "title": "Post Title",
            "content": "Post content...",
            "excerpt": "Post excerpt...",
            "date": "2026-08-17 12:00:00",
            "categories": ["category-slug"],
            "tags": ["tag1", "tag2"]
        }
    ],
    "meta": {
        "source": "syria-oil.com Production WXR Export",
        "created_at": "2026-08-17 09:06:52",
        "post_count": 8,
        "cat_count": 38,
        "original_url": "https://www.syria-oil.com"
    }
}
```

## Integration with Theme

Once generated, the JSON file is automatically used by:
- **One-Click Demo Import** - Categories are imported during Step 2
- **Setup Page Status** - Category count is displayed in the status checklist

## Why Not Import All Posts?

The tool extracts only a small number of sample posts (default: 8) because:
1. The theme is meant to be installed on the production site where posts already exist
2. Sample posts provide examples of content structure without overwhelming the database
3. Categories are the main structural element needed for the theme to function properly

## Technical Details

### Requirements
- PHP 5.6+ with XMLReader extension
- Read access to the WXR file
- Write access to the demo-content directory

### Performance
- Uses XMLReader for memory-efficient parsing
- Processes files up to several hundred MB
- Completes in 1-3 seconds for most WXR files

### Category Collision Handling
The import system checks for existing categories before creating new ones:
- Checks by slug first, then by name
- Skips categories that already exist
- Reports both created and skipped counts

## Troubleshooting

### "WXR file not found"
- Check the `$wxr_file` path in the script
- Ensure the file exists and is readable

### "Invalid production categories data"
- The WXR file may be corrupted
- Try re-exporting from WordPress

### "Failed to write JSON file"
- Check directory permissions for `demo-content/`
- The script will auto-create the directory if missing

## Maintenance

When updating the production site and wanting to refresh demo categories:
1. Export fresh WXR from production WordPress (Tools > Export)
2. Update `$wxr_file` path in the converter script
3. Run the converter again
4. The new categories will be available on next demo import
