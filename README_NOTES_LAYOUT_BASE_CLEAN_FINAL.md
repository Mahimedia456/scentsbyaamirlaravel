# Scents by Aamir — Notes Layout Revert + Base Notes Clean Fix

This build makes two targeted changes only:

1. The fragrance story + 3 note cards are restored to the previous layout:
   - fragrance copy on the left
   - Top / Heart / Base note cards on the right

2. Base Notes parsing now stops before non-note sections such as:
   - Product Description
   - Description
   - Short Description
   - Story
   - Materials & Care
   - Care
   - Packaging
   - Longevity
   - Occasion
   - Why Choose
   - Features
   - Discover
   - Content

Example:
`Musk, Patchouli, Ambergris Product Description Bold Heat combines...`

now renders as:
`Musk, Patchouli, Ambergris`

After extracting:

```powershell
cd E:\ScentsByAamirLaravel\frontend
php artisan optimize:clear
php artisan view:clear
php artisan view:cache
npm run build
php artisan serve
```
