<?php
if (!defined('API_ROOT'))
	exit;

// Enable error reporting
error_reporting(E_ALL);

// Try to connect to the database
try
{
	$dbh = new PDO('mysql:host=Bestdayever.db.10129095.hostedresource.com;dbname=Bestdayever', 'Bestdayever', 'Bestdayever11!');
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch (PDOException $e)
{
	print "Error!: " . $e->getMessage() . "<br/>";
	exit;
}

?>