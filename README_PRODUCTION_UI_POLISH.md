# Production UI Polish

- Corrected storefront header/logo sizing with hard dimensions so the source logo can never expand over the viewport.
- Mobile loader disabled; desktop loader uses a lightweight SBA text mark instead of the large source logo.
- Rebuilt home hero as a controlled luxury split-banner using live catalog imagery.
- Preserved live catalog, product cards, campaign sections, checkout, customer features and admin CRUD.
- Existing admin product create/edit/update/delete, variants, collections, stock and multi-image upload remain intact.

After deploy:
1. npm run build (or deploy public/build generated locally)
2. php artisan optimize:clear
3. php artisan route:cache
4. php artisan view:cache
