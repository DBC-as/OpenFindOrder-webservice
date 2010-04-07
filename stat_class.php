<?php
//require_once("OLS_class_lib/oci_class.php");
require_once("OLS_class_lib/IDatabase_class.php");

define(TABLE,"ofo_stats");
function verbose()
{
  echo "TESTHEST";
  exit;
}

class stats
{
  private $data=array();
  // new bit
  private $_new;
  // action bit
  private $set_data;
  //database
  private $db;
  
  public function __construct($set_data=true)
  {
    // TODO get servicename and version from parameters or config
    $this->timestamp=$this->timestamp();
    $this->servicename="openfindorder";
    $this->version="0.1";
    $this->set_data=$set_data;

    $this->db=new pg_database("host=visoke port=5432 dbname=kvtestbase user=fvs password=fvs");

    if( $set_data )
      {
	if(  $this->load() )
	  $this->_new=false;
	else
	  $this->_new=true;
	
	$this->data['HITS']++;    
	if( $_SERVER['REQUEST_METHOD']=='GET' )
	  $this->data['GET']++;
	elseif( $_SERVER['REQUEST_METHOD']=='POST' )
	  $this->data['POST']++;
	else
	  $this->data['COMMAND']++;
      }
  }
  

  public function __set($name,$value)
  {
    $this->data[strtoupper($name)]=$value;
  }

  public function __get($name)
  {
    return $this->data[strtoupper($name)];
  }

  private function servicename()
  {
    return $this->data['SERVICENAME'];
  }

  private function version()
  {
    return $this->data['VERSION'];
  }

  private function load()
  {        
    $sql="select * from ".TABLE." where servicename='".$this->servicename()."' and version='".$this->version()."' and timestamp='".$this->timestamp()."'";
    $this->execute($sql);
    $row=$this->db->get_row();
    $this->db->close();
    if( $row['data'])
      $this->data=unserialize($row['data']);
    else
      return false;

    return $this->data;
  }

  public function get_table_html()
  {
    $this->load_all();
    $header=null;
    $ret.='<table>';
    $count=0;
    while( $row=$this->db->get_row() )
      {
	$data=unserialize($row['data']);
	if( !$header )
	  {
	    $header=array_keys($data);
	    $ret.= $this->set_header_html($header);
	  }
	$ret.=$this->set_row_html($data,$count);
	$count++;
      }
    $this->db->close();
    $ret.='</table>';
    return $ret;
  }

  private function set_header_html($header)
  {
    $ret.= '<tr class="stat_gray">';
    foreach( $header as $key=>$head )
      $ret.='<td><b>'.$head.'</b></td>';
    $ret.='</tr>';
    return $ret;
  }
  
  private function set_row_html($row,$count)
  {
    $keys=array_keys($row);
    if( $count%2==0 )
      $ret.='<tr class="stat_white">';
    else
      $ret.='<tr class="stat_gray">';
    foreach( $keys as $key=>$val )
      $ret.='<td>'.$row[$val].'</td>';
    $ret.='</tr>';
    return $ret;
  }

 
  private function load_all($year=null,$month=null,$day=null,$hour=null)
  {
    // TODO paging..
    // TODO SELECT with time-interval
    $sql="select * from ".TABLE." where servicename='".$this->servicename()."' and version='".$this->version()."' order by timestamp desc";
    $this->execute($sql);
  }

  private function save()
  {
    if( $this->_new )
      $this->insert();
    else
      $this->update();    
  }

  private function update()
  {   
    $this->db->open();
    $clause=array("servicename"=>$this->servicename(),"version"=>$this->version(),"timestamp"=>$this->timestamp());
    $row=array("servicename"=>$this->servicename(),"version"=>$this->version(),"timestamp"=>$this->timestamp(),"data"=>serialize($this->data));
    $this->db->update(TABLE,$row,$clause);
    $this->db->close();
  }

  private function insert()
  {
    $row=array("servicename"=>$this->servicename(),"version"=>$this->version(),"timestamp"=>$this->timestamp(),"data"=>serialize($this->data));
   
    $this->db->open();
    $this->db->insert(TABLE,$row);
    $this->db->close();
  }

  /**
     return current date to be used as timestamp( YmdH )
   */
  private function timestamp()
  {
    $time=time();
    $date=date('YmdH',$time);
    return $date;
  }

  private function execute($sql)
  {
    $this->db->open();
    $this->db->set_query($sql);
    $this->db->execute();
  }

  public function __destruct()
  {
    if( $this->set_data )  
      $this->save();      
  }
  
}


?>
