# Installation Guide

**Version:** 1.0.0  
**Last Updated:** 2026-03-26

This guide covers installation of the Dummy Data Generator plugin for OJS 3.5+.

---

## Prerequisites

Before installing, ensure you have:

- [x] OJS 3.5.0 or later installed
- [x] PHP 8.2 or later
- [x] Journal Manager or Site Administrator access
- [x] Write access to OJS installation directory

---

## Installation Steps

### Step 1: Download Plugin

**Option A: Clone from GitHub**

```bash
cd /tmp
git clone https://github.com/munir-abbasi/dummyDataGenerator.git
```

**Option B: Download ZIP**

```bash
cd /tmp
wget https://github.com/munir-abbasi/dummyDataGenerator/archive/refs/heads/main.zip
unzip main.zip
mv dummyDataGenerator-main dummyDataGenerator
```

**Option C: Manual Copy**
Copy the `dummyDataGenerator` directory from your local development environment.

---

### Step 2: Copy to OJS Plugins Directory

Copy the plugin to your OJS installation:

```bash
cp -r dummyDataGenerator /path/to/ojs/plugins/generic/
```

**Verify directory structure:**

```
/path/to/ojs/plugins/generic/dummyDataGenerator/
├── DummyDataGeneratorPlugin.php
├── api/
├── classes/
├── locale/
├── tests/
├── composer.json
└── version.xml
```

---

### Step 3: Set Permissions

Ensure the plugin files are readable by the web server:

```bash
cd /path/to/ojs/plugins/generic/
chown -R www-data:www-data dummyDataGenerator/
chmod -R 755 dummyDataGenerator/
```

**Note:** Replace `www-data` with your web server user (e.g., `apache`, `nginx`, `http`).

---

### Step 4: Clear OJS Cache

Clear the template and data cache:

**Method A: Via Admin Panel (Recommended)**

1. Log in as Site Administrator
2. Navigate to: **Website Administration → Settings → Website**
3. Click **Clear Cache** button

**Method B: Manual Cache Clearing**

```bash
rm -rf /path/to/ojs/cache/*
```

**Note:** The `tools/clearCache.php` script may not be available in all OJS 3.5+ installations. Use the admin panel or manual method instead.

---

### Step 5: Enable Plugin

**Method A: Via Web Interface (Recommended)**

1. Log in as Journal Manager or Site Admin
2. Navigate to: **Website Administration → Settings → Website → Plugins**
3. Find **"Dummy Data Generator"** in the plugin list
4. Check the enable checkbox
5. Click **Save**

**Method B: Via CLI**

```bash
cd /path/to/ojs
php tools/plugins.php enable dummyDataGenerator
```

**Verify plugin is enabled:**

```bash
php tools/plugins.php list | grep dummyDataGenerator
```

---

### Step 6: Verify Installation

**Check plugin appears in admin panel:**

1. Go to Website Administration → Settings → Website → Plugins
2. Verify "Dummy Data Generator" is listed and enabled

**Test API endpoint:**

```bash
curl -X POST \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Content-Type: application/json" \
  http://your-ojs-url/api/v1/users/generate-users \
  -d '{"count": 1}'
```

**Expected response:**

```json
{
  "success": true,
  "created": 1,
  "userIds": [1],
  "defaultPassword": "DummyUser123!"
}
```

---

## Troubleshooting

### Plugin Not Showing in List

**Symptoms:** Plugin doesn't appear in the plugin gallery

**Solutions:**

1. **Verify directory location:**
   
   ```bash
   ls -la /path/to/ojs/plugins/generic/dummyDataGenerator/
   ```

2. **Check version.xml exists and is valid:**
   
   ```bash
   cat /path/to/ojs/plugins/generic/dummyDataGenerator/version.xml
   ```

3. **Clear template cache:**
   
   ```bash
   cd /path/to/ojs
   php tools/clearCache.php
   ```

4. **Check file permissions:**
   
   ```bash
   find /path/to/ojs/plugins/generic/dummyDataGenerator -type f -exec ls -la {} \;
   ```

---

### Error: "No context available"

**Symptoms:** API returns 400 error with "No context available" message

**Cause:** API endpoint accessed without journal context

**Solution:** Include journal path in URL:

```bash
# ❌ Wrong (no context)
http://your-ojs-url/api/v1/users/generate-users

# ✅ Correct (with journal context)
http://your-ojs-url/my-journal/api/v1/users/generate-users
```

---

### Error: "No author users found"

**Symptoms:** Submission generation fails

**Cause:** No users with author role exist

**Solution:** Generate users first:

```bash
# Step 1: Generate users
curl -X POST http://your-ojs-url/journal/api/v1/users/generate-users \
  -H "Authorization: Bearer TOKEN" \
  -d '{"count": 10}'

# Step 2: Generate submissions
curl -X POST http://your-ojs-url/journal/api/v1/users/generate-submissions \
  -H "Authorization: Bearer TOKEN" \
  -d '{"count": 5}'
```

---

### Error: "Decision validation failed"

**Symptoms:** Submissions created but workflow doesn't progress

**Cause:** Missing editor permissions or incorrect workflow stage

**Solution:**

1. Ensure API token belongs to Manager or Site Admin
2. Verify journal has editorial workflow configured
3. Check OJS logs for detailed error:
   
   ```bash
   tail -f /path/to/ojs/logs/error.log
   ```

---

### Plugin Causes White Screen / 500 Error

**Symptoms:** OJS crashes after enabling plugin

**Cause:** PHP syntax error or compatibility issue

**Solution:**

1. **Disable plugin via database:**
   
   ```sql
   UPDATE versions SET current = 0 WHERE product = 'dummyDataGenerator';
   UPDATE plugin_settings SET setting_value = 0 WHERE setting_name = 'enabled';
   ```

2. **Check PHP version:**
   
   ```bash
   php -v
   # Should be 8.2+
   ```

3. **Check error logs:**
   
   ```bash
   tail -f /path/to/ojs/logs/error.log
   ```

4. **Verify OJS version:**
   
   ```bash
   cd /path/to/ojs
   php tools/clearCache.php
   ```

---

### Composer Dependencies Missing

**Symptoms:** Tests fail, autoloader not found

**Solution:**

```bash
cd /path/to/ojs/plugins/generic/dummyDataGenerator
composer install --dev
```

**Note:** Composer dependencies are only required for development/testing, not runtime.

---

## Uninstallation

### Step 1: Cleanup Generated Data

Delete all dummy data before uninstalling:

```bash
curl -X DELETE \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  http://your-ojs-url/journal/api/v1/users/cleanup?confirm=DELETE_ALL_DUMMY_DATA
```

### Step 2: Disable Plugin

**Via Web Interface:**

1. Website Administration → Settings → Website → Plugins
2. Uncheck "Dummy Data Generator"
3. Click **Save**

**Via CLI:**

```bash
cd /path/to/ojs
php tools/plugins.php disable dummyDataGenerator
```

### Step 3: Remove Plugin Files

```bash
rm -rf /path/to/ojs/plugins/generic/dummyDataGenerator
```

### Step 4: Clear Cache

```bash
cd /path/to/ojs
php tools/clearCache.php
```

---

## Multi-Journal Installation

The plugin works with OJS multi-journal installations. Each journal context has isolated data:

- Users are assigned to specific journals
- Submissions belong to journal sections
- Issues are journal-specific
- Tracking data is per-journal

**No special configuration needed** - the plugin automatically detects the journal context from the API URL.

---

## Upgrade from Previous Versions

### From v0.1.0 to v1.0.0

1. **Backup database:**
   
   ```bash
   mysqldump -u ojs_user -p ojs_database > backup.sql
   ```

2. **Remove old plugin:**
   
   ```bash
   rm -rf /path/to/ojs/plugins/generic/dummyDataGenerator
   ```

3. **Install new version:**
   
   ```bash
   cp -r dummyDataGenerator /path/to/ojs/plugins/generic/
   ```

4. **Clear cache and re-enable:**
   
   ```bash
   php tools/clearCache.php
   php tools/plugins.php enable dummyDataGenerator
   ```

---

## Post-Installation Checklist

- [ ] Plugin enabled and visible in admin panel
- [ ] API endpoints accessible with valid token
- [ ] Test user generation (create 1-2 users)
- [ ] Verify users have author role assigned
- [ ] Test submission generation (create 1 submission)
- [ ] Verify workflow progression
- [ ] Test cleanup (delete test data)
- [ ] Document API token for team members
- [ ] Review security considerations

---

## Support

If you encounter issues not covered in this guide:

1. **Check logs:** `/path/to/ojs/logs/error.log`
2. **Review requirements:** Ensure OJS 3.5+ and PHP 8.2+
3. **GitHub Issues:** [Create an issue](https://github.com/munir-abbasi/dummyDataGenerator/issues)
4. **PKP Forum:** [Post in Community Forum](https://forum.pkp.sfu.ca)

---

**Next Steps:** After successful installation, proceed to [API_DOCUMENTATION.md](API_DOCUMENTATION.md) for usage instructions.
