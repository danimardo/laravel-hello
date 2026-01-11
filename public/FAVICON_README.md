# Favicon Configuration

## Current Setup

This Laravel application uses a custom favicon with Wisteria branding:

- **SVG Favicon**: `/public/favicon.svg` - Modern vector format with Wisteria purple (#8B5FBF) color
- **ICO Favicon**: `/public/favicon.ico` - Legacy format for older browsers

## Design

The favicon features:
- Wisteria purple background (#8B5FBF)
- Simple counter/tally mark icon in white
- Number indicator (4) showing the counter functionality
- 32x32 pixel dimensions (scalable via SVG)

## Generating ICO from SVG

To generate a proper ICO file from the SVG:

### Option 1: Online Converter
1. Visit https://www.icoconverter.com/ or https://favicon.io/
2. Upload the `favicon.svg` file
3. Download the generated ICO file
4. Replace `/public/favicon.ico` with the downloaded file

### Option 2: Using ImageMagick (if installed)
```bash
convert favicon.svg -resize 16x16 favicon-16x16.ico
convert favicon.svg -resize 32x32 favicon-32x32.ico
convert favicon-16x16.ico favicon-32x32.ico favicon.ico
```

### Option 3: Using RealFaviconGenerator
1. Visit https://realfavicongenerator.net/
2. Upload `favicon.svg`
3. Follow the setup instructions
4. Download and replace `/public/favicon.ico`

## Browser Compatibility

The favicon is configured for maximum compatibility:

- `favicon.svg` - Modern browsers (Chrome, Firefox, Safari, Edge)
- `favicon.ico` - Legacy browsers (Internet Explorer, older versions)

## Files Updated

The following views have been updated to include the favicon:

- `/resources/views/auth/login.blade.php`
- `/resources/views/auth/change-password.blade.php`
- `/resources/views/counter/index.blade.php`
- `/resources/views/admin/users/index.blade.php`
- `/resources/views/admin/users/create.blade.php`
- `/resources/views/admin/users/edit.blade.php`

Each view now includes:
```html
<!-- Favicon -->
<link rel="icon" type="image/svg+xml" href="/favicon.svg">
<link rel="alternate icon" href="/favicon.ico">
```

## Testing

To test the favicon:
1. Open the application in a browser
2. Check the browser tab - the favicon should appear next to the title
3. Check browser bookmarks - the favicon should appear in bookmarks
4. Clear browser cache if changes don't appear immediately
