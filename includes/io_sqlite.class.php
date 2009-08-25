<?php 
//include the generic SQL class
require_once dirname(__FILE__)."/io_sql.class.php";

class IO_sqlite extends IOsql
{
  /**
   * Database resource pointer
   */
  var $db;
  
  /**
   * @param sgConfig pointer to a {@link sgConfig} object representing 
   *   the current script configuration
   */
  function IO_sqlite()
  {
    $this->config =& configuration::getInstance();
    $this->db = sqlite_open($this->config->base_path.$this->config->pathto_data_dir."sqlite.dat");
  }

  function query($query)
  {
    return sqlite_query($this->db, $query);
  }
  
  function escape_string($query)
  {
    return sqlite_escape_string($query);
  }
  
  function fetch_array($res)
  {
    return sqlite_fetch_array($res);
  }
  
  function num_rows($res)
  {
    return sqlite_num_rows($res);
  }

  function error()
  {
    return sqlite_error_string(sqlite_last_error($this->db));
  }

}

?>
