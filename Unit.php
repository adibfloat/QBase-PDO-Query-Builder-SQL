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

require_once 'src/QBase.php';
require_once 'src/Config.php';

$data = $db->table('mahasiswa')->select(['nama', 'alamar'])->get();

echo '<pre>';
print_r($data);
echo '</pre>';
?>
