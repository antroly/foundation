# Releasing

## Pre-release checklist

### Tests and static analysis

- [ ] `composer pest` — all tests pass
- [ ] `composer lint` — no style violations
- [ ] `composer analyse` — no PHPStan errors

### Detachability proof

Verify the package can be removed after publishing without breaking the application.

```bash
# 1. Fresh Laravel install
composer create-project laravel/laravel detach-test
cd detach-test

# 2. Install and publish
composer require antroly/foundation --dev
php artisan antroly:install

# 3. Register providers in bootstrap/app.php as instructed

# 4. Remove the package
composer remove antroly/foundation

# 5. Verify the app still works
php artisan about
php artisan route:list
php artisan config:cache
```

- [ ] `php artisan about` exits without errors after package removal
- [ ] No reference to `Antroly\Foundation` remains in the running application
- [ ] Published files in `app/` compile and autoload correctly without the package

### Version bump

- [ ] Update version in `composer.json`
- [ ] Tag the release: `git tag vX.Y.Z`
- [ ] Push tag: `git push origin vX.Y.Z`
