# How to Reset OWA Password Using Docker

There are several ways to reset a user password in OWA when running in Docker:

## Method 1: Using the Helper Script (Easiest)

Use the provided `reset-password.sh` script:

```bash
./reset-password.sh <user_id> <new_password>
```

**Example:**
```bash
./reset-password.sh admin mynewpassword123
```

## Method 2: Using Docker Exec Directly

Execute the reset password script directly in the container:

```bash
docker exec -it owa-web php /var/www/html/reset_password.php <user_id> <new_password>
```

**Example:**
```bash
docker exec -it owa-web php /var/www/html/reset_password.php admin mynewpassword123
```

## Method 3: Reset Admin User (Deletes old admin, creates new)

If you want to completely reset the admin user (deletes all existing admin users and creates a new one):

```bash
docker exec -it owa-web php /var/www/html/reset_admin_user.php <user_id> <password> <email>
```

**Example:**
```bash
docker exec -it owa-web php /var/www/html/reset_admin_user.php admin mynewpassword123 admin@example.com
```

## Method 4: Using Environment Variables (Automatic on Deployment)

If you set the admin user environment variables in `docker-compose.yml`:

```yaml
environment:
  - OWA_ADMIN_USER=admin
  - OWA_ADMIN_PASSWORD=your_new_password
  - OWA_ADMIN_EMAIL=admin@example.com
```

Then restart the container:
```bash
docker-compose down
docker-compose up -d
```

The admin user will be automatically reset/created on each deployment.

## Method 5: List All Users First

To see all users before resetting:

```bash
docker exec -it owa-web php /var/www/html/list_users.php
```

## Troubleshooting

### Container is not running
If you get an error that the container is not running:
```bash
docker-compose up -d
```

### Check container name
If your container has a different name, check with:
```bash
docker ps
```

Then use the correct container name in the commands above.

### Database connection issues
If you get database connection errors, make sure:
1. The database container is running: `docker ps`
2. The database is healthy: `docker-compose ps`
3. Wait a few seconds after starting containers for the database to be ready

## Quick Reference

| Task | Command |
|------|---------|
| Reset password | `./reset-password.sh admin newpass123` |
| List users | `docker exec -it owa-web php /var/www/html/list_users.php` |
| Reset admin user | `docker exec -it owa-web php /var/www/html/reset_admin_user.php admin pass123 admin@example.com` |
| Create new user | `docker exec -it owa-web php /var/www/html/create_user.php user1 pass123 user1@example.com` |

