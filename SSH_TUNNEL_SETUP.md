# SSH Tunnel Setup for Database Connection

## Problem
The database server `codd.cs.gsu.edu` might not allow direct connections from your local machine. You may need to create an SSH tunnel.

## Solution: Create SSH Tunnel

### Option 1: SSH Tunnel (Recommended)

Open a new terminal and run:
```bash
ssh -L 3306:codd.cs.gsu.edu:3306 ebinitie1@codd.cs.gsu.edu
```

This creates a tunnel that forwards local port 3306 to the database server.

Then update `backend/db.php`:
```php
$DB_HOST = '127.0.0.1';  // Use localhost through tunnel
$DB_NAME = 'ebinitie1';
$DB_USER = 'ebinitie1';
$DB_PASS = 'your_password';
```

### Option 2: Connect Directly (If Allowed)

If direct connection is allowed, just add your password:
```php
$DB_HOST = 'codd.cs.gsu.edu';
$DB_NAME = 'ebinitie1';
$DB_USER = 'ebinitie1';
$DB_PASS = 'your_password';  // Add your MySQL password here
```

## Testing Connection

After setting up, test with:
```bash
php test_db.php
```

