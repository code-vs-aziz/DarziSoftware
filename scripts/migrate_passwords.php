<?php
/**
 * One-time CLI migration: hash plaintext user passwords in `users.UPASS`.
 *
 * Usage: php scripts/migrate_passwords.php
 */
define('BASEPATH', __DIR__.'/../system/');
define('ENVIRONMENT', getenv('CI_ENV') ?: 'development');

$configFile = __DIR__.'/../application/config/database.php';
if ( ! is_file($configFile))
{
	fwrite(STDERR, "database.php not found\n");
	exit(1);
}

include $configFile;

$mysqli = @new mysqli(
	$db['default']['hostname'],
	$db['default']['username'],
	$db['default']['password'],
	$db['default']['database'],
	isset($db['default']['port']) ? (int) $db['default']['port'] : 3306
);

if ($mysqli->connect_errno)
{
	fwrite(STDERR, 'DB connect failed: '.$mysqli->connect_error.PHP_EOL);
	exit(1);
}

$column = $mysqli->query("SHOW COLUMNS FROM users LIKE 'UPASS'");
if ($column && ($row = $column->fetch_assoc()))
{
	if (preg_match('/varchar\((\d+)\)/i', (string) $row['Type'], $matches) && (int) $matches[1] < 255)
	{
		if ( ! $mysqli->query('ALTER TABLE users MODIFY UPASS VARCHAR(255) NOT NULL'))
		{
			fwrite(STDERR, 'Could not expand UPASS column: '.$mysqli->error.PHP_EOL);
			exit(1);
		}
		echo 'Expanded users.UPASS column to VARCHAR(255)'.PHP_EOL;
	}
}
if ($column)
{
	$column->free();
}

$result = $mysqli->query('SELECT UNAME, UPASS FROM users');
if ( ! $result)
{
	fwrite(STDERR, 'Query failed: '.$mysqli->error.PHP_EOL);
	exit(1);
}

$updated = 0;
$skipped = 0;

while ($row = $result->fetch_assoc())
{
	$username = $row['UNAME'];
	$stored = (string) $row['UPASS'];

	if ($stored === '' || preg_match('/^\$2[ayb]\$.{56}$/', $stored) || strpos($stored, '$argon2') === 0)
	{
		$skipped++;
		continue;
	}

	$hash = password_hash($stored, PASSWORD_DEFAULT);
	$stmt = $mysqli->prepare('UPDATE users SET UPASS = ? WHERE UNAME = ?');
	$stmt->bind_param('ss', $hash, $username);
	$stmt->execute();
	$stmt->close();
	$updated++;
	echo 'Hashed password for: '.$username.PHP_EOL;
}

$result->free();
$mysqli->close();

echo "Done. Updated: {$updated}, skipped: {$skipped}".PHP_EOL;
