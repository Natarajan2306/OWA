# Production Deployment Guide for Open Web Analytics

## For Coolify Deployment

### DNS Configuration

**IMPORTANT**: Make sure your domain DNS is properly configured in Coolify:

1. In Coolify, go to your application settings
2. Configure the domain: `analytics.pdevsecops.com`
3. Ensure DNS records are set up:
   - A record pointing to your Coolify server IP, OR
   - CNAME record pointing to your Coolify domain
4. Wait for DNS propagation (can take a few minutes to hours)

If you see `DNS_PROBE_FINISHED_NXDOMAIN`, it means DNS isn't configured yet. The application is working (as shown by the 302 redirect in logs), but the domain isn't resolving.

### 1. Environment Variables

Set these environment variables in Coolify:

- `OWA_DB_HOST`: Your database host (e.g., `db` or database service name)
- `OWA_DB_NAME`: Database name (e.g., `owa`)
- `OWA_DB_USER`: Database username
- `OWA_DB_PASSWORD`: Database password
- `OWA_DB_PORT`: Database port (usually `3306`)
- `OWA_PUBLIC_URL`: Your public URL (e.g., `http://analytics.pdevsecops.com/`)

### 2. Config File

The `owa-config.php` file will be automatically created from the template on first container start. However, you need to configure it:

**Option A: Use Installation Wizard**
1. Access `http://analytics.pdevsecops.com/install.php`
2. Follow the installation wizard
3. It will create the config file automatically

**Option B: Create Config File Manually**

Create `owa-config.php` in the root directory with:

```php
<?php
define('OWA_DB_TYPE', 'mysql');
define('OWA_DB_NAME', 'owa');
define('OWA_DB_HOST', 'db'); // or your database host
define('OWA_DB_USER', 'owa_user');
define('OWA_DB_PASSWORD', 'owa_password');
define('OWA_DB_PORT', '3306');
define('OWA_PUBLIC_URL', 'http://analytics.pdevsecops.com/');
define('OWA_NONCE_KEY', 'your-random-key-here');
define('OWA_NONCE_SALT', 'your-random-salt-here');
define('OWA_AUTH_KEY', 'your-random-auth-key-here');
define('OWA_AUTH_SALT', 'your-random-auth-salt-here');
?>
```

### 3. Database Setup

Make sure your database is set up and accessible. The application will create tables during installation.

### 4. File Permissions

The Docker container will set proper permissions automatically. If you need to set them manually:

```bash
chmod 644 owa-config.php
chmod -R 755 owa-data
```

### 5. First Access

1. Go to `http://analytics.pdevsecops.com/install.php`
2. Complete the installation wizard
3. Create an admin user
4. Access the application at `http://analytics.pdevsecops.com/`

## Troubleshooting

### Config File Not Found

If you see "Failed to open stream" errors:
- The application will automatically redirect to `install.php` if config file doesn't exist
- Create the config file manually or run the installation wizard

### Database Connection Issues

- Verify database credentials in `owa-config.php`
- Check that the database service is running and accessible
- Ensure network connectivity between web and database containers

### Permission Issues

- Ensure `owa-data` directory is writable
- Check file permissions on `owa-config.php` (should be 644)

