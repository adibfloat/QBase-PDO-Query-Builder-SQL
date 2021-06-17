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

try {
  class QBase {
    /**
     * Properties of your operator.
     */
    private $op = ['like', '=', '!=', '<', '>', '<=', '>=', '<>'];

    /**
     * Properties of logical AND.
     */
    private $state = 'AND';

    /**
     * Properties of notation.
     */
    private $not;

    /**
     * Properties of your table.
     */
    private $table;

    /**
     * Properties of your selected field on table.
     */
    private $select = '*';

    /**
     * Properties of your join table.
     */
    private $join;

    /**
     * Properties of your conditional table.
     */
    private $where;

    /**
     * Properties of your query table.
     */
    private $query;

    /**
     * Method to clear the above property.
     */
    private function reset() {
      $this->table = null;
      $this->select = null;
      $this->join = null;
      $this->where = null;
      $this->query = null;
    }

    /**
     * Method for extracting table conditions.
     */
    private function setExtract() {
      $sql = '';
      $sql .= $this->join;
      $sql .= !empty($this->where) ? 'WHERE '.$this->where : '';
      $sql .= $this->query;
      $this->reset();
      return $sql;
    }

    /**
     * Prefix table.
     */
    private $prefix;

    /**
     * Check table.
     */
    private function isTable($field) {
      if(is_array($field)) {
        $string = '';
        foreach($field as $key => $value) {
          $string .= $this->prefix."{$value},";
        }
        return substr($string, 0, -2);
      }
      return $this->prefix.$field;
    }

    /**
     * Check text.
     */
    private function isText($val) {
      return is_int($val) ? $val : "'{$val}'";
    }

    /**
     * Check join.
     */
    private function isIm($field) {
      return is_array($field) ? implode(', ', $field) : $field;
    }

    /**
     * Check selection.
     */
    private function isSelect($sql, $field, $name) {
      return "{$sql}({$field})".(!is_null($name) ? " AS {$name}" : '');
    }

    /**
      * @$MySQL_Driver
      * Properties used to set up your SQL driver.
      * The default driver used is MySQL or MariaDB.
    */
    private $MySQL_Driver;

    /**
      * @$MySQL_Host
      * Properties used to set up your SQL host.
      * The default driver used is MySQL or MariaDB.
    */
    private $MySQL_Host;

    /**
      * @$MySQL_User
      * Properties used to set up your SQL user.
      * The default user is root.
    */
    private $MySQL_User;

    /**
      * @$MySQL_Pass
      * Properties used to set up your SQL password.
      * The default user is empty.
    */
    private $MySQL_Pass;

    /**
      * @$MySQL_Name
      * Properties used to set up your SQL database name.
      * Please customize the name of the database that you have.
    */
    private $MySQL_Name;

    /**
      * @$MySQL_Port
      * Properties used to set up your SQL port.
      * Please customize the port of the database that you have.
    */
    private $MySQL_Port;

    /**
      * @$MySQL_Charset
      * Properties used to set up your SQL character set.
      * Please customize the character set of the database that you have.
    */
    private $MySQL_Charset;

    /**
      * @$MySQL_Connect
      * Properties used to set up your SQL connection.
    */
    private $MySQL_Connect;

    /**
     * The first part to run if the configuration has been set properly and correctly.
     */
    public function __construct($config) {
      /**
       * The database configuration must be an array.
       * The configuration must be entered into the class constructor.
       */
      if(!is_array($config)) {
        echo '<b>Warning!</b> Your database configuration must be in an array.';
        exit();
      }

      /**
       * Fill value for server configuration.
       * @MySQL_Driver  = mysql;
       * @MySQL_Host    = localhost/127.0.0.1;
       * @MySQL_User    = root;
       * @MySQL_Pass    = blank;
       * @MySQL_Name    = db;
       * @MySQL_Port    = 3306;
       * @MySQL_Charset = utf8mb4;
       */
      $this->MySQL_Driver = isset($config['DB_DRIVER']) ? $config['DB_DRIVER'] : 'mysql';
      $this->MySQL_Host = isset($config['DB_HOST']) ? $config['DB_HOST'] : 'localhost';
      $this->MySQL_User = isset($config['DB_USER']) ? $config['DB_USER'] : 'root';
      $this->MySQL_Pass = isset($config['DB_PASS']) ? $config['DB_PASS'] : '';
      $this->MySQL_Name = isset($config['DB_NAME']) ? $config['DB_NAME'] : '';
      $this->MySQL_Port = isset($config['DB_PORT']) ? $config['DB_PORT'] : 3306;
      $this->MySQL_Charset = isset($config['DB_CHARSET']) ? $config['DB_CHARSET'] : 'utf8mb4';
      
      $server = $this->MySQL_Driver.":host=".str_replace(':'.$this->MySQL_Port, '', $this->MySQL_Host).($this->MySQL_Port != '' ? ';port='.$this->MySQL_Port : '').";dbname=".$this->MySQL_Name.";charset=".$this->MySQL_Charset;

      // Connect to database.
      $this->MySQL_Connect = new PDO($server, $this->MySQL_User, $this->MySQL_Pass);
      $this->MySQL_Connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $this->MySQL_Connect->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
      $this->MySQL_Connect->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    }

    /**
     * This method is useful for clearing memory that is running on the server.
     * This aims to make your server load a little lighter and cost-effective.
     */
    public function __destruct() {
      $this->MySQL_Connect = null;
    }

    /**
     * This method is used to join tables.
     * Example: $db->table('test')->join('check', 'test.id', 'check.test_id')->get();
     * SQL: "SELECT * FROM test JOIN check ON test.id = check.test_id";
     */
    public function join($table, $key = null, $op = '', $foreign = null, $sql = '') {
      $setForeign = !is_null($foreign) ? $foreign : $op;
      $setOp = !is_null($foreign) ? $op : '=';
      $key = strpos($this->table, 'AS') ? $key : $this->isTable($key);
      $setForeign = strpos($table, 'AS') ? $setForeign : $this->isTable($setForeign);
      $this->join .= " {$sql} JOIN {$this->isTable($table)} ON {$key} {$setOp} {$setForeign} ";
      return $this;
    }

    /**
     * This method is used to inner join tables.
     * Example: $db->table('test')->innerJoin('check', 'test.id', 'check.test_id')->get();
     * SQL: "SELECT * FROM test INNER JOIN check ON test.id = check.test_id";
     */
    public function innerJoin($table, $key, $op, $foreign = null) {
      $this->join($table, $key, $op, $foreign, 'INNER');
      return $this;
    }

    /**
     * This method is used to left join tables.
     * Example: $db->table('test')->leftJoin('check', 'test.id', 'check.test_id')->get();
     * SQL: "SELECT * FROM test LEFT JOIN check ON test.id = check.test_id";
     */
    public function leftJoin($table, $key, $op, $foreign = null) {
      $this->join($table, $key, $op, $foreign, 'LEFT');
      return $this;
    }

    /**
     * This method is used to right join tables.
     * Example: $db->table('test')->rightJoin('check', 'test.id', 'check.test_id')->get();
     * SQL: "SELECT * FROM test RIGHT JOIN check ON test.id = check.test_id";
     */
    public function rightJoin($table, $key, $op, $foreign = null) {
      $this->join($table, $key, $op, $foreign, 'RIGHT');
      return $this;
    }
    
    /**
     * This method is used to left outer join tables.
     * Example: $db->table('test')->leftOuterJoin('check', 'test.id', 'check.test_id')->get();
     * SQL: "SELECT * FROM test LEFT OUTER JOIN check ON test.id = check.test_id";
     */
    public function leftOuterJoin($table, $key, $op, $foreign = null) {
      $this->join($table, $key, $op, $foreign, 'LEFT OUTER');
      return $this;
    }
    
    /**
     * This method is used to right outer join tables.
     * Example: $db->table('test')->rightOuterJoin('check', 'test.id', 'check.test_id')->get();
     * SQL: "SELECT * FROM test RIGHT OUTER JOIN check ON test.id = check.test_id";
     */
    public function rightOuterJoin($table, $key, $op, $foreign = null) {
      $this->join($table, $key, $op, $foreign, 'RIGHT OUTER');
      return $this;
    }
    
    /**
     * This method is used to full outer join tables.
     * Example: $db->table('test')->fullOuterJoin('check', 'test.id', '=', 'check.test_id')->get();
     * SQL: "SELECT * FROM test FULL OUTER JOIN check ON test.id = check.test_id";
     */
    public function fullOuterJoin($table, $key, $op, $foreign = null) {
      $this->join($table, $key, $op, $foreign, 'FULL OUTER');
      return $this;
    }

    /**
     * Method for selecting table.
     * Example: $db->table(['users', 'roles'])->get();
     */
    public function table($table, $as = null) {
      $as = !is_null($as) && !is_array($table) ? " AS {$as}" : '';
      $this->table = $this->isTable($table).$as;
      return $this;
    }

    /**
     * Find the smallest value in a particular field.
     * Example: $db->table('users')->min('follows')->get();
     * SQL: "SELECT MIN(follows) FROM users";
     */
    public function min($field, $name = null) {
      $this->select = $this->isSelect('MIN', $field, $name);
      return $this;
    }

    /**
     * Find the biggest value in a particular field.
     * Example: $db->table('users')->max('follows')->get();
     * SQL: "SELECT MAX(follows) FROM users";
     */
    public function max($field, $name = null) {
      $this->select = $this->isSelect('MAX', $field, $name);
      return $this;
    }

    /**
     * Calculates a specific value in a field.
     * Example: $db->table('users')->count('follows')->get();
     * SQL: "SELECT COUNT(follows) FROM users";
     */
    public function count($field, $name = null) {
      $this->select = $this->isSelect('COUNT', $field, $name);
      return $this;
    }

    /**
     * Calculates average a specific value in a field.
     * Example: $db->table('users')->avg('follows')->get();
     * SQL: "SELECT AVG(follows) FROM users";
     */
    public function avg($field, $name = null) {
      $this->select = $this->isSelect('AVG', $field, $name);
      return $this;
    }

    /**
     * Count all values from field.
     * Example: $db->table('users')->sum('follows')->get();
     * SQL: "SELECT SUM(follows) FROM users";
     */
    public function sum($field, $name = null) {
      $this->select = $this->isSelect('SUM', $field, $name);
      return $this;
    }

    /**
     * Selecting a specific field.
     * Example: $db->table('users')->select(['name', 'email'])->get();
     * SQL: "SELECT name, email FROM users";
     */
    public function select($field) {
      $this->select = $this->isIm($field);
      return $this;
    }

    /**
     * This method is used to set or capture WHERE conditions in the table.
     */
    private function setWhere($column, $op, $value, $sql = '') {
      $_where = '';
      if(is_array($column)) {
          $op = is_null($op) ? 'AND' : $op;
          foreach($column as $keys => $value) {
            if(is_array($value)) {
              $_where .= $this->setWhere(
                $value[0],
                isset($value[1]) ? (is_int($value[1]) ? $value[1] : (in_array($value[1], $this->op) ? $value[1] : "'{$value[1]}'")) : '',
                isset($value[2]) ? "'{$value[2]}'" : ''
              ).$op;
            } else {
              echo '<b>Warning!</b> Something went wrong.';
              exit();
            }
          }
        $_where = substr($_where, 0, -strlen($op));
      } else {
        if(empty($op) && empty($value)) {
          $_where = " {$sql} id = {$column} ";
        } elseif (empty($value)) {
          $_where = " {$sql} {$column} = ".$this->isText($op);
        } else {
          $_where = " {$sql} {$column} {$op} ".$this->isText($value);
        }
      }
      $this->where .= $this->isState($_where);
    }

    /**
     * This method is used to filter and extract data from a table.
     * Example: $db->table('users')->where([['id', 1], ['status', 1]], 'OR')->get();
     * SQL: "SELECT * FROM users WHERE id = 1 OR status = 1";
     */
    public function where($column, $op = null, $value = null) {
      $this->setWhere($column, $op, $value);
      return $this;
    }

    /**
     * This method is used to combine several conditions in the table.
     */
    public function orWhere($column, $op = null, $value = null) {
      $this->state = 'OR';
      $this->where($column, $op, $value);
      return $this;
    }

    /**
     * This method is used to combine several conditions that do not exist in the table
     */
    public function notWhere($column, $op = null, $value = null) {
      $this->setWhere($column, $op, $value, 'NOT');
      return $this;
    }

    /**
     * This method is used to combine several conditions that do not exist or exist in the table
     */
    public function orNotWhere($column, $op = null, $value = null) {
      $this->state = 'OR';
      $this->notWhere($column, $op, $value);
      return $this;
    }

    /**
     * This method is used for conditions that do not exist in the table.
     */
    public function whereNull($column) {
      $this->where .= $this->isState("{$column} IS NULL");
      return $this;
    }

    /**
     * This method is used for some conditions that do not exist in the table.
     */
    public function orWhereNull($column) {
      $this->state = 'OR';
      $this->whereNull($column);
      return $this;
    }

    /**
     * This method is used for some conditions that do not exist or exist in the table.
     */
    public function whereNotNull($column) {
      $this->where .= $this->isState("{$column} IS NOT NULL");
      return $this;
    }

    /**
     * This method is used for several conditions that do not exist or exist in the table
     */
    public function orWhereNotNull($column) {
      $this->state = 'OR';
      $this->whereNotNull($column);
      return $this;
    }

    /**
     * This method is used to filter and extract data from a table.
     * Example: $db->table('test')->whereIn('id', [1, 2, 3])->get();
     * SQL: "SELECT * FROM test WHERE id IN (1, 2, 3)";
     */
    public function whereIn($column, array $fields) {
      $array = '';
      foreach($fields as $key => $value) {
        $array .= $this->isText($value).',';
      }
      $array = substr($array, 0, -1);
      $_where = "{$column} IN ({$array})";
      $this->where .= $this->isState($_where);
      return $this;
    }

    /**
     * This method is used to filter and extract data from a table.
     */
    public function orWhereIn($column, array $value) {
      $this->state = 'OR';
      $this->whereIn($column, $value);
      return $this;
    }

    /**
     * This method is used to filter and extract data from a table.
     * Example: $db->table('test')->whereNotIn('id', [1, 2, 3])->get();
     * SQL: "SELECT * FROM test WHERE id NOT IN (1, 2, 3)";
     */
    public function whereNotIn($column, array $value) {
      $value = implode(', ', $value);
      $_where = "{$column} NOT IN ({$value})";
      $this->where .= $this->isState($_where);
      return $this;
    }

    /**
     * This method is used to filter and extract data from a table.
     */
    public function orWhereNotIn($column, array $value) {
      $this->state = 'OR';
      $this->whereNotIn($column, $value);
      return $this;
    }

    /**
     * This method is used to display data according to the initial data limit and the data end limit in the table
     */
    public function between($column, $val1, $val2 = null) {
      $param = is_array($val1) ? $this->isText($val1[0]).' AND '.$this->isText($val1[1]) : $this->isText($val1).' AND '.$this->isText($val2);      
      $_between = "{$column} {$this->not} BETWEEN {$param}";
      $this->not = '';
      $this->where .= $this->isState($_between);
      return $this;
    }

    /**
     * This method is used to display data according to the initial data limit and the data end limit in the table
     */
    public function orBetween($column, $val1, $val2 = null) {
      $this->state = 'OR';
      $this->between($column, $val1, $val2);
      return $this;
    }

    /**
     * This method is used to display data according to the initial data limit and the data end limit in the table
     */
    public function notBetween($column, $val1, $val2 = null) {
      $this->not = 'NOT';
      $this->between($column, $val1, $val2);
      return $this;
    }

    /**
     * This method is used to display data according to the initial data limit and the data end limit in the table
     */
    public function orNotBetween($column, $val1, $val2 = null) {
      $this->state = 'OR';
      $this->not = 'NOT';
      $this->between($column, $val1, $val2);
      return $this;
    }

    /**
     * This method is used in conjunction with the SELECT command, and is usually used for searching data.
     * Example: $db->table('test')->like('name', '%example%')->get();
     * SQL: "SELECT * FROM test WHERE name LIKE %example%";
     */
    public function like($column, $search) {
      $this->where .= $this->isState("{$column} {$this->not} LIKE {$search}");
      $this->not = '';
      return $this;
    }

    /**
     * This method is used in conjunction with the SELECT command, and is usually used for searching data
     */
    public function orLike($column, $search) {
      $this->state = 'OR';
      $this->like($column, $search);
      return $this;
    }

    /**
     * This method is used in conjunction with the SELECT command, and is usually used for searching data.
     * Example: $db->table('test')->notLike('name', '%example%')->get();
     * SQL: "SELECT * FROM test WHERE name NOT LIKE %example%";
     */
    public function notLike($column, $search) {
      $this->not = 'NOT';
      $this->like($column, $search);
      return $this;
    }

    /**
     * This method is used in conjunction with the SELECT command, and is usually used for searching data.
     */
    public function orNotLike($column, $search) {
      $this->state = 'OR';
      $this->not = 'NOT';
      $this->like($column, $search);
      return $this;
    }

    /**
     * This method is used to group data in a column designated in the table.
     * Example: $db->table('test')->groupBy(['id', 'user_id'])->get();
     * SQL: "SELECT * FROM test GROUP BY id, user_id";
     */
    public function groupBy($values) {
      $this->query .= " GROUP BY {$this->isIm($values)}";
      return $this;
    }

    /**
     * This method is used when the key filter is an alias in the table.
     * Example: $db->table('test')->having('COUNT(user_id) > ?', [2])->get();
     * SQL: "SELECT * FROM test HAVING COUNT(user_id) >= 5";
     */
    public function having($field, $op = null, $value = null) {
      $_having = ' HAVING ';
      if(is_array($op)) {
        $q = explode('?', $field);
        foreach($op as $key => $val) {
          $_having .= $q[$key] . $val;
        }
      } elseif(empty($value)) {
        $_having .= "{$field} > {$op}";
      } else {
        $_having .= "{$field} {$op} {$value}";
      }
      $this->query .= $_having;
      return $this;
    }

    /**
     * This method is used to sort the result-set in ascending or descending order in the table.
     * Example: $db->table('test')->orderBy('name', 'DESC')->get();
     * SQL: "SELECT * FROM test ORDER BY name DESC";
     */
    public function orderBy($column, $sort = 'ASC') {
      $this->query .= " ORDER BY {$column} {$sort}";
      return $this;
    }

    /**
     * This method is used to limit the number of rows taken, with an initial limit and the number specified with parameters in the table.
     * Example: $db->table('test')->limit(10, 20)->get();
     * SQL: "SELECT * FROM test LIMIT 10, 20";
     */
    public function limit($field1, $field2 = null) {
      $this->query .= " LIMIT {$field1} ".(!is_null($field2) ? ", {$field2}" : '');
      return $this;
    }

    /**
     * This method uses a clause that is useful for determining the number of rows that we will skip, before we start to display the next row in the table again.
     * Example: $db->table('test')->offset(10)->get();
     * SQL: "SELECT * FROM test OFFSET 10";
     */
    public function offset($field) {
      $this->query .= " OFFSET {$field}";
      return $this;
    }

    /**
     * Example: $db->table('test')->pagination(10, 2)->get();
     * SQL:  "SELECT * FROM test LIMIT 10 OFFSET 10";
     */
    public function pagination($perPage, $page = 1) {
      $this->limit($perPage);
      $page = (($page > 0 ? $page : 1) - 1) * $perPage;
      $this->offset($page);
      return $this;
    }

    /**
     * This method is used to retrieve the first data from a table.
     * Example: $db->table('users')->first();
     * SQL: "SELECT * FROM users LIMIT 1";
     */
    public function first($select = null) {
      $this->select .= !empty($select) ? ', '.$this->isIm($select) : '';
      $sql = sprintf("SELECT %s FROM %s %s LIMIT 1", $this->select, $this->table, $this->setExtract());
      $result = $this->MySQL_Connect->prepare($sql);
      $result->execute();
      return $result->fetchAll();
    }

    /**
     * This method is used. The statement is used to add a new row of data to the table in the database in the table.
     * Example: $db->table('users')->insert($data);
     * SQL: "INSERT INTO users(username, status) VALUES('febrihidayan', 1)";
     */
    public function insert(Array $fields) {
      $columns = implode(',', array_keys($fields));
      $values = '';
      foreach($fields as $key => $val) {
        $values .= (is_int($val) ? $val : "'{$val}'").",";
      } 
      $sql = sprintf("INSERT INTO %s (%s) VALUES(%s)", $this->table, $columns, substr($values, 0, -1));
      return $this->runQuery($sql);
    }

    /**
     * This method is used. The statement is used to update a row of data to the table in the database in the table.
     * Example: $db->table('users')->where(1)->update($data);
     * SQL: "UPDATE users SET username = 'febrihidayan', status = 1 WHERE id = 1";
     */
    public function update(Array $fields) {
      $_fields = '';
      foreach($fields as $key => $value) {
        $_fields .= "{$key} = ".$this->istext($value).", ";
      }
      $_fields = substr($_fields, 0, -2);
      $sql = sprintf("UPDATE %s SET %s WHERE %s", $this->table, $_fields, $this->where);
      return $this->runQuery($sql);
    }

    /**
     * This method is used. The statement is used to remove a new row of data to the table in the database in the table.
     * Example: $db->table('users')->where(1)->delete();
     * SQL: "DELETE FROM users WHERE id = 1";
     */
    public function delete() {
      $sql = sprintf("DELETE FROM %s WHERE %s", $this->table, $this->where);
      return $this->runQuery($sql);
    }

    /**
     * This method is used to run the query command on the table.
     */
    private function runQuery($sql) {
      $query = $this->MySQL_Connect->prepare($sql);
      if($query->execute()) {
        return true;
      } else {
        return false;
      }
    }

    /**
     * This method is used to run the query command on the table.
     */
    private function isState($sql) {
      $sql = empty($this->where) ? $sql : " {$this->state} ".$sql;
      $this->state = 'AND';
      return $sql;
    }

    /**
     * Method to get data from the selected table.
     * Example: $db->table('users')->get();
     * SQL: "SELECT * FROM users";
     */
    public function get($select = null) {
      $this->select .= !empty($select) ? ', '.$this->isIm($select) : '';
      $sql = sprintf("SELECT %s FROM %s %s", $this->select, $this->table, $this->setExtract());
      $result = $this->MySQL_Connect->prepare($sql);
      $result->execute();
      return $result->fetchAll();
    }
  }
} catch(PDOException $e) {
  echo '<b>Database Error</b>: '.$e->getMessage().'; (<b>Trace</b>: '.$e->getCode().')';
  exit();
}
?>
