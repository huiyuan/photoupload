<?php
define('API_ROOT', './');
require API_ROOT . 'global.php';

// Execute the schema file
try
{
	$queries = explode(";\n", trim(file_get_contents(API_ROOT . 'schema.sql')));
	foreach ($queries as $cur_query)
		$dbh->exec($cur_query);
}
catch (PDOException $e)
{
	//$tpl->assign('error_message', 'Error!: ' . $e->getMessage());
	//$tpl->render('error');
	exit;
}

echo 'Done!';