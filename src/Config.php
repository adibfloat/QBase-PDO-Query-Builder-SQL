<?php
/**
 * @Package QBase - Query Builder Databse PDO
 * @Author  Afid Arifin
 * @Email   xtgilar@gmail.com
 * @Version v1.0
 */

/**
 * This source code is free for anyone to use and redistribute.
 * Make sure you keep the author credits on this page.
 * and/or preferably you don't change anything other than your database configuration.
 */

/**
 * Please change the following configuration according to your needs.
 */
require_once 'QBase.php';
$db = new QBase([
  'DB_DRIVER'   => 'mysql',
  'DB_HOST'     => 'localhost',
  'DB_NAME'     => '',
  'DB_USER'     => 'root',
  'DB_PASS'     => '',
  'DB_PORT'     => 3306,
  'DB_CHARSET'  => 'utf8mb4',
]);
?>
