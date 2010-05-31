<?php
/**
 *
 * This file is part of Open Library System.
 * Copyright © 2009, Dansk Bibliotekscenter a/s,
 * Tempovej 7-11, DK-2750 Ballerup, Denmark. CVR: 15149043
 *
 * Open Library System is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Open Library System is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Open Library System.  If not, see <http://www.gnu.org/licenses/>.
*/

define(DEBUG, FALSE);

require_once("OLS_class_lib/webServiceServer_class.php");
require_once("OLS_class_lib/cql2solr_class.php");
require_once("OLS_class_lib/oci_class.php");
require_once("xsdparse.php");
require_once("OLS_class_lib/memcache_class.php");
//require_once("stat_class.php");

class openFindOrder extends webServiceServer 
{
  //public $stat;

  /** \brief 
      constructor; start watch; call parent's constructor
   */
  public function __construct()
  {
    parent::__construct('openfindorder.ini');
    
    $this->watch->start("openfindorderWS");
  //  $this->stat=new stats();
  }

  /** \brief
      destructor: stop watch; log for statistics     
   */
  public function __destruct()
  {
    $this->watch->stop("openfindorderWS");
    verbose::log(TIMER, $this->watch->dump());
  }

   /** \brief Echos config-settings
   *
   */
  public function show_info() 
  {
    echo "<pre>";
    echo "version             " . $this->config->get_value("version", "setup") . "<br/>";
    echo "log                 " . $this->config->get_value("logfile", "setup") . "<br/>";
    echo "db                  " . $this->config->get_value("connectionstring", "setup") . "<br/>";
    echo "xsd                 " . $this->config->get_value("schema", "setup") . "<br/>";
    echo "wsdl                " . $this->config->get_value("wsdl", "setup") . "<br/>";

    echo "implemented methods:" . "<br/>";
    $methods=$this->config->get_value("soapAction","setup");
    foreach( $methods as $key=>$value )
	echo "    " . $value . "<br/>";	

    echo "</pre>";
    die();
  }

  public function findAllOpenEndUserOrders($param)
  {
   
     if( $error=OFO_agency::authenticate($param->agency->_value) )
       return $this->send_error($error);

    $OFO = new OFO_database("findAllOpenEndUserOrders",$this->config);
    $orders=$OFO->findOrders($param);

    return $this->findOrderResponse($orders);
  }

  /** \brif
      The service request for orders on material not localized to the end user agency.
   */
  public function findNonLocalizedEndUserOrders($param)
  {
    if( $error=OFO_agency::authenticate($param->agency->_value) )
       return $this->send_error($error);

    $OFO = new OFO_database("findNonLocalizedEndUserOrders",$this->config);
    $orders=$OFO->findOrders($param);

    return $this->findOrderResponse($orders);
  }
  
  /** \brief
      The service request for orders on material localized to the end user agency.
   */
  public function findLocalizedEndUserOrders($param)
  {
    if( $error=OFO_agency::authenticate($param->agency->_value) )
       return $this->send_error($error);

    $OFO = new OFO_database("findLocalizedEndUserOrders",$this->config);
    $orders=$OFO->findOrders($param);

    return $this->findOrderResponse($orders);
  }

  /** \brief
      
   */
  public function findClosedIllOrders($param)
  {
    if( $error=OFO_agency::authenticate($param->agency->_value) )
       return $this->send_error($error);

    $OFO = new OFO_database("findClosedIllOrders",$this->config);
    $orders=$OFO->findOrders($param);

    return $this->findOrderResponse($orders);
  }

  /** \brief
   */
  public function findOpenIllOrders($param)
  {
    if( $error=OFO_agency::authenticate($param->agency->_value) )
       return $this->send_error($error);

    $OFO = new OFO_database("findOpenIllOrders",$this->config);
    $orders=$OFO->findOrders($param);

    return $this->findOrderResponse($orders);
  }


  public function findAllIllOrders($param)
  {
     if( $error=OFO_agency::authenticate($param->agency->_value) )
       return $this->send_error($error);

    $OFO = new OFO_database("findAllIllOrders",$this->config);
    $orders=$OFO->findOrders($param);

    return $this->findOrderResponse($orders);
  }

  public function findAllNonIllOrders($param)
  {
    if( $error=OFO_agency::authenticate($param->agency->_value) )
      return $this->send_error($error);

    $OFO = new OFO_database("findAllNonIllOrders",$this->config);
    $orders=$OFO->findOrders($param);

    return $this->findOrderResponse($orders);
  }


 /** \brief
  * The service request for all orders (optionally for a specific order system)
  * @param; request parameters in request-xml object.
  */
  public function findAllOrders($param)
  {
    if( $error=OFO_agency::authenticate($param->agency->_value) )
       return $this->send_error($error);

    $OFO = new OFO_database("findAllOrders",$this->config);
    $orders=$OFO->findOrders($param);

    return $this->findOrderResponse($orders);
  }

  /**\brief
   * The service request for a specific order (orderId)
   * @param; request parameters in request-xml object.
   */
  public function findSpecificOrder($param)
  {   
    // TODO implement
    if( $error=OFO_agency::authenticate($param->agency->_value) )
       return $this->send_error($error);

    $OFO = new OFO_database("findSpecificOrder",$this->config);
    $orders=$OFO->findOrders($param);

    return $this->findOrderResponse($orders);
  }

  /**\brief
   * The service request for orders from a specific user (userId, userName or userMail)
   * @param; request parameters in request-xml object.
   */
  public function findOrdersFromUser($param)
  {
     if( $error=OFO_agency::authenticate($param->agency->_value) )
      return $this->send_error($error);

    $OFO = new OFO_database("findOrdersFromUser",$this->config);
    $orders=$OFO->findOrders($param);
   
    return $this->findOrderResponse($orders);
   }

  /**\brief
   * The service request for orders from unknown users (general)
   * @param; request parameters in request-xml object.
   */
  public function findOrdersFromUnknownUser($param)
  {
    if(  $error=OFO_agency::authenticate($param->agency->_value) )
     return $this->send_error($error);

    $OFO = new OFO_database("findOrdersFromUnknownUser",$this->config);
    $orders=$OFO->findOrders($param);
   
    return $this->findOrderResponse($orders); 
   }

  /**\brief
   * -- not yet defined
   *
   */
  public function findOrdersWithStatus($param)
  {
    if(  $error=OFO_agency::authenticate($param->agency->_value) )
     return $this->send_error($error);

    return $this->send_error("placeholder - request not yet defined");
  }

  /**\brief
   * The service request for reason for auto forward (autoForwardReason)
   * @param; request parameters in request-xml object.
   */
  public function findOrdersWithAutoForwardReason($param)
  {
    if(  $error=OFO_agency::authenticate($param->agency->_value) )
     return $this->send_error($error);

    $OFO = new OFO_database("findOrdersWithAutoForwardReason",$this->config);
    $orders=$OFO->findOrders($param);
   
    return $this->findOrderResponse($orders); 
  }

  /**\brief
   * The service request for automatically forwarded orders (general)
   * @param; request parameters in request-xml object.
   */
  public function findAutomatedOrders($param)
  {
    if(  $error=OFO_agency::authenticate($param->agency->_value) )
     return $this->send_error($error);

    $OFO = new OFO_database("findAutomatedOrders",$this->config);
    $orders=$OFO->findOrders($param);
   
    return $this->findOrderResponse($orders); 
  }

  /**\brief
   * The service request for orders from a specific ill-cooperation (kvik, norfri or articleDirect)
   * @param; request parameters in request-xml object.
   */
  public function findOrderType($param)
  {
     if(  $error=OFO_agency::authenticate($param->agency->_value) )
     return $this->send_error($error);

    $OFO = new OFO_database("findOrderType",$this->config);
    $orders=$OFO->findOrders($param);
   
    return $this->findOrderResponse($orders);  
  }

  /**\brief
   *  The service request for a biblographical search of orders
   * @param; request parameters in request-xml object
   */
  public function bibliographicSearch($param)
  {
    if(  $error=OFO_agency::authenticate($param->agency->_value) )
     return $this->send_error($error);

    $OFO = new OFO_database("bibliographicSearch",$this->config);
    $orders=$OFO->findOrders($param);
   
    return $this->findOrderResponse($orders);   
  }

  /**\brief
   * The service request for the status of an order
   * @param; request parameters in request-xml object
   */
  public function getOrderStatus($param)
  {
    if(  $error=OFO_agency::authenticate($param->agency->_value) )
     return $this->send_error($error);

    $OFO = new OFO_database("getOrderStatus",$this->config);
    $orders=$OFO->findOrders($param);
   
    return $this->findOrderResponse($orders);   
  } 

  /**\brief
   * The service request for non-automatatically forwarded orders (general)
   *  @param; request parameters in request-xml object
   */
  public function findNonAutomatedOrders($param)
  {
    if(  $error=OFO_agency::authenticate($param->agency->_value) )
     return $this->send_error($error);

    $OFO = new OFO_database("findNonAutomatedOrders",$this->config);
    $orders=$OFO->findOrders($param);
   
    return $this->findOrderResponse($orders); 
  }

  /**\brief
   * Generate response-object from given array of orders.
   * @orders; array of orders
   * return; orders as xml-objects
   */
  private function findOrderResponse($orders)
  {
    $response->findOrdersResponse->_namespace='http://oss.dbc.dk/ns/openfindorder';

    // error from OFO_database
    if( $orders===false )
      {
	// TODO log
	return $this->send_error(OFO_database::$error);
      }
    
    // empty result-set
    if( empty($orders) )
      return $this->send_error("no orders found");

//    $this->stat->orders+=count($orders);

    // TODO - this line is for test-purpose only. Remove in production
    $response->findOrdersResponse->_value->pure_sql->_value=OFO_database::$pure_sql;

    $response->findOrdersResponse->_value->result->_namespace='http://oss.dbc.dk/ns/openfindorder';    
    
    $response->findOrdersResponse->_value->result->_value->numberOfOrders->_namespace='http://oss.dbc.dk/ns/openfindorder';
    // $response->findOrdersResponse->_value->result->_value->numberOfOrders->_value=count($orders);
    $response->findOrdersResponse->_value->result->_value->numberOfOrders->_value=OFO_database::$numrows;
   
    foreach( $orders as $order )
      $response->findOrdersResponse->_value->result->_value->order[]=$order;

    return $response;      
  }

  /** \brief
   * send errormessage as xml response-object
   */
  private function send_error($message)
  {
    $response->findOrdersResponse->_namespace='http://oss.dbc.dk/ns/openfindorder';

    // TODO - this line is for test-purpose only. Remove in production
    $response->findOrdersResponse->_value->pure_sql->_value=OFO_database::$pure_sql;

    $error->_namespace='http://oss.dbc.dk/ns/openfindorder';
    $error->_value=$message;
    $response->findOrdersResponse->_value->error=$error;

    return $response;
  }
}

/*
 * MAIN
 */

$ws=new openFindOrder();

$ws->handle_request();

/**\brief
 * Class to handle connection to database and correlation to xml-schema
 */
class OFO_database
{
  public static $error;
  public static $numrows;
  public static $pure_sql;
  public static $vip_connect;

  private $xmlfields=array();
  private $action;
  private $fields=array();
  private $sql;
  private $connectionstring;
  

  /**\brief
   * Constructor. 
   */
  public function __construct($action,$config)
  {
    self::$error=null;
    $this->action=$action;    

    // set connectionstring from config
    if( !$this->connectionstring=$config->get_value("connectionstring","setup") )
      die( "no database credentials in config-file" );   

    if( !self::$vip_connect=$config->get_value("vip","setup") )
      die(" no credentials for vip-base ");

    // set actions
    $arr=$config->get_value("action");
    if( empty($arr) )
      die( "no actions set in config-file" );
    foreach($arr[$action] as $key=>$val)
      $this->fields[]=$val;
 
    $this->set_base_sql($config);
    
    // get xml schema
    $schemafile=$config->get_value("schema","setup");
    if( !file_exists($schemafile) )
      die( "xsd not found: ".$schemafile );

    $schema=new xml_schema();
    $schema->get_from_file($schemafile);
    // set xml-fields
    if( $this->action=='getOrderStatus') 
      $this->xmlfields=$schema->get_sequence_array('getOrderStatusResponse');
    else
      $this->xmlfields=$schema->get_sequence_array('order');

  }

  private function set_base_sql($config)
  {
    $this->sql="SELECT ";

    // get fields to select from ini-file 
    $sqlarr=$config->get_section("ors_order");
    if( empty($sqlarr) )
      die( "no table definition in config-file" );     
    
    foreach( $sqlarr as $key=>$val )
      {
	if( $val )
	  $this->sql.=$val."\n,";
	else
	  $this->sql.=$key."\n,";
      }
    // remove trailing ','
    $this->sql=substr($this->sql,0,-1);

    // insert join on ors_order_index here if operations are bibliographic search or OrdersFromUser

      $this->sql.=" FROM ors_order o INNER JOIN ors_order_index oi ON \n
                (oi.requesterid=o.requesterid AND oi.orderid=o.orderid)\n";
                    
    //$this->sql.=" FROM ors_order o WHERE ";
    
  }
  
  /**\brief
   * Get orders from database.
   * @param; request parameters as xml-object
   * return; array of found orders
   */
  public function findOrders($param)
  {
    if(!$oci=$this->execute($param))
      return false;
    
    // TODO - this line is for test-purpose only. Remove in production
    self::$pure_sql=$oci->pure_sql();

    $resultPosition=1;
    while( $data=$oci->fetch_into_assoc() )
      {
	if( $order=$this->get_order($data,$resultPosition) )
	  {
	    $orders[]=$order;
	    $resultPosition++;
	  }
      }
    
    return $orders;
  }

  private function count($param)
  {
    $oci1=new oci($this->connectionstring);

    if( !$clause = $this->set_sql($param,$oci1) )
      return false;

    $sql="SELECT COUNT(*) count FROM(".$this->sql.$clause.")";

    try{$oci1->connect();}
    catch(ociException $e)
      {
	self::$error="could not connect to db";
	return false;
      }
    
    try{$oci1->set_query($sql);}
    catch(ociException $e)
      {
	//	self::$error="query could not be set";
	self::$error=$e->__toString();
	//	die("TSTHEST");
	return false;
      }

    
    try{$row=$oci1->fetch_into_assoc();}
    catch(ociException $e)
      {
	self::$error=$e->__toString();
return false;
      }

    self::$numrows=$row["COUNT"];

    $oci1->disconnect();
  }

  /**\brief
   * Handle óne order.
   * @data; a row of data from database
   * @resultPosition; rownumber of result
   * return; óne order as xml-object
   */
  private function get_order(&$data,$resultPosition)
  {
    $ret->_namespace='http://oss.dbc.dk/ns/openfindorder';
  
    $ret->_value->resultPosition->_value=$resultPosition;;
    $ret->_value->resultPosition->_namespace='http://oss.dbc.dk/ns/openfindorder';
   
    // column-names from database MUST match xml-fields for this loop to work
    // new loop to ensure roworder as defined in xml-schema  
    foreach($this->xmlfields as $key=>$val)
      {
	if( $value= $data[$val] )
	  {	    
	    if( $value && $value!='0001-01-01' && $value!='uninitialized')
	    {
	      if( $key != 'placeOnHold' )
		{
		  if( $value=='yes')
		    $value='true';
		  if( $value=='no' )
		    $value='false';
		  if( $value=='N' )
		    $value='false';
		  if( $value=='Y')
		    $value='true';
		}
	      if( $key=='creationDate' )
		{
		  $value=str_replace(' ','T',$value);
		  $value.='Z';
		}	
	      $ret->_value->$key->_value=utf8_encode($value);
	      $ret->_value->$key->_namespace='http://oss.dbc.dk/ns/openfindorder';
	    }
	  }
      }
    return $ret;
  }

  /**\brief
   * Initialize instance of oci-class.
   * @param; request-parameters as xml-object
   * return; instance of oci-class
   */
  private function execute($param)
  {
    try{$oci=new oci($this->connectionstring);}
    catch(ociException $e){
      self::$error="could not connect to db: ".$e->__toString()."\n";
      return false;}
    
    $step=$param->stepValue->_value;
    $start=$param->start->_value;


    /* try{$clause = $this->set_sql($param,$oci);}
    catch(ociException $e){
      self::$error="could not set sql: ".$e->__toString()."\n";
      return false;}*/

    if( !$clause = $this->set_sql($param,$oci) )
      return false;
    
    $sql=$this->sql.$clause;

    //echo $sql;
    //exit;

    $this->count($param);

    if( ($step || $step===0) && ($start || $start===0) )
      $oci->set_pagination($start,($start+$step)-1);

    
    try{ $oci->connect(); }
    catch(ociException $e){
      self::$error="could not connect to db: ".$e->__toString()."\n";
      return false;}

    try{ $oci->set_query($sql); }
    catch(ociException $e){
      self::$error="could not set query: ".$e->__toString()."\n";
      return false;}

    
    /*echo $oci->pure_sql();
  print_r($oci->bind_backup);
  exit;*/

    /* if(!@$oci->set_query($sql))
      {
	self::$error="query could not be set";
	return false;
	}*/
    return $oci;
  }
 

  /**\brief
   * Get sql from OFO_sql class according to action and parameters.
   * Set bind-parameters to given oci-instance
   * @param; request-parameters as xml-objects.
   * @oci; instance of oci-class.
   * return; sql according to action and parameters
  */
  private function set_sql($param,$oci)
  {
    switch( $this->action )
      {
      case "findAllOpenEndUserOrders":
	$ret=OFO_sql::findAllOpenEndUserOrders($param,$oci);
	break;
      case "findAllOrders":
	$ret= OFO_sql::findAllOrders($param,$oci);
	break;
      case "findAllIllOrders":
	$ret= OFO_sql::findAllIllOrders($param,$oci);
	break;
      case "findAllNonIllOrders":
	$ret= OFO_sql::findAllNonIllOrders($param,$oci);
	break;
      case "findSpecificOrder":
	$ret= OFO_sql::findSpecificOrder($param,$oci);
	break;
      case "findOrdersFromUser":
	$ret= OFO_sql::findOrdersFromUser($param,$oci);
	break;
      case "findOrdersFromUnknownUser":
	$ret= OFO_sql::findOrdersFromUnknownUser($param,$oci);
	break;
      case "bibliographicSearch":
	$ret= OFO_sql::bibliographicSearch($param,$oci);
	break;
      case "findOrdersWithStatus":
	$ret=OFO_sql::findOrdersWithStatus($param,$oci);
	break;
      case "findOrdersWithAutoForwardReason":
	$ret=OFO_sql::findOrdersWithAutoForwardReason($param,$oci);
	break;
      case "findAutomatedOrders":
	$ret=OFO_sql::findAutomatedOrders($param,$oci);
	break;
      case "findNonAutomatedOrders":
	$ret=OFO_sql::findNonAutomatedOrders($param,$oci);
	break;
      case "findOrderType":
	$ret=OFO_sql::findOrderType($param,$oci);
	break;
      case "getOrderStatus":
	$ret=OFO_sql::getOrderStatus($param,$oci);
	break;
      case "findOpenIllOrders":
	$ret=OFO_sql::findOpenIllOrders($param,$oci);
	break;
      case "findClosedIllOrders":
	$ret=OFO_sql::findClosedIllOrders($param,$oci);
	break;
      case "findLocalizedEndUserOrders":
	$ret=OFO_sql::findLocalizedEndUserOrders($param,$oci);
	break;
      case "findNonLocalizedEndUserOrders":
	$ret=OFO_sql::findNonLocalizedEndUserOrders($param,$oci);
	break;
      default:
	//$ret= "SELECT * FROM ORS_ORDER WHERE REQUESTERID=716700";
	die( "no or wrong action" );
	break;
      }
    if(! $ret )
      {
	self::$error="sql could not be set for ".$this->action;
	return false;
      }

    return $ret;    
  }


  

}


/** 
\brief
* class to handle lookups in vip-base
*/

class OFO_vip
{
  public static function set_libraries($param)
  {
   
    if( !$agency=$param->agency->_value )
      return false;
    

    $libs=OFO_vip::get_library_list($agency);
    // print_r($libs);
    if( empty($libs) )
      {
	if( $param->requesterAgencyId->_value )
	  $ret=" AND o.requesterid=".$agency;
	elseif( $param->responderAgencyId->_value )
	  $ret.=" AND responderid=".$agency;

      }
    elseif( !empty($libs) )
      {
	if(  $param->requesterAgencyId )
	  $ret=" AND o.requesterid in(";
	//elseif( $param->responderAgencyId )	  
	else
	  $ret=" AND responderid in(";
	//else
	// return false;

	$clause='';
	foreach( $libs as $lib )
	  {
	    if( strlen($clause) )
	      $clause.=',';
	    $clause.=$lib['BIB_NR'];
	  }
	$ret.=$clause;
	$ret.=")\n";
      }
    else
      {
      return false;
 
      }  

    return $ret;        
  }
  
  private static function get_library_list($agency)
  {
    $sql="select v.bib_nr from vip v inner join vip_vsn vs on v.kmd_nr=vs.kmd_nr where vs.bib_nr=".$agency;
    $oci=new oci(OFO_database::$vip_connect);
    
    try{$oci->connect();}
    catch(ociException $e)
      {
	OFO_database::$error="could not connect to db ".OFO_database::$vip_connect;
	return false;
      }
    
    try{ $oci->set_query($sql); }
    catch(ociException $e)
      {
	OFO_database::$error="query could not be set";
	return false;
      }
    
    try{$ret= $oci->fetch_all_into_assoc();}
    catch(ociException $e){ die($e->__toString());}
	
    

    return $ret;	   
  }

  
  
}
/** \brief
    Class to handle sql for each of the methods in webservice
 */
class OFO_sql
{
  public static function get_select()
  {
    // return "SELECT * FROM ORS_ORDER WHERE ";
    return '';
  }  


  public static function findAllOpenEndUserOrders($params,$oci)
  {
     $sql = self::get_select();
    
    if( $add=self::set_ids($params,$ids) )
      $sql.=$add;
    else
      return false; 
   
    $sql.=self::bind_array($ids,$oci);
    
    $oci->bind("ordertype_bind","enduser_request");
    $oci->bind("ordertype1_bind","enduser_illrequest");
    
    $sql.="and (ordertype=:ordertype_bind OR ordertype=:ordertype1_bind)\n";

    $oci->bind("closed_bind","N");
    $sql.="and closed=:closed_bind";
    
    $add = self::setRequestGeneral($params,$oci);

    if( $add !== false )
      $sql.=$add;
    else
      return false;

    return $sql;   
  }

  public static function findAllIllOrders($params,$oci)
  {
    $sql = self::get_select();
    
    if( $add=self::set_ids($params,$ids) )
      $sql.=$add;
    else
      return false; 
   
    $sql.=self::bind_array($ids,$oci);
    
    $oci->bind("ordertype_bind","inter_library_request");
    $sql.="and ordertype=:ordertype_bind\n";

    $add = self::setRequestGeneral($params,$oci);

    if( $add !== false )
      $sql.=$add;
    else
      return false;

    return $sql;   

  }

  public static function findAllNonIllOrders($params,$oci)
  {
    $sql = self::get_select();
    
    if( $add=self::set_ids($params,$ids) )
      $sql.=$add;
    else
      return false; 
   
    $sql.=self::bind_array($ids,$oci);
    
    $oci->bind("enduser_request_bind","enduser_request");
    $oci->bind("enduser_illrequest_bind","enduser_illrequest");
    $sql.="and (ordertype=:enduser_request_bind OR ordertype=:enduser_illrequest_bind)\n";

    $add = self::setRequestGeneral($params,$oci);

    if( $add !== false )
      $sql.=$add;
    else
      return false;

    return $sql;
  }
  
  public static function findNonLocalizedEndUserOrders($params,$oci)
  {
     // TDOO filter on some field in ors_order (orderstatus)
    $sql = self::get_select();
    
    if( $add=self::set_ids($params,$ids) )
      $sql.=$add;
    else
      return false; 
   
    $sql.=self::bind_array($ids,$oci);

    if(  isset($params->closed->_value) )
      {
	$oci->bind( "ordertype_bind","enduser_illrequest");
	$sql.="and ordertype=:ordertype_bind\n";

	$close=$params->closed->_value;
	if( $close=='true' || $close==1 )
	  $oci->bind("closed_bind",'Y');
	else
	  $oci->bind("closed_bind",'N');

	$sql.="and closed=:closed_bind\n";
      }
    else
      return false;

    $add = self::setRequestGeneral($params,$oci);
    if( $add !== false )
      $sql.=$add;
    else
      return false;

    return $sql;   
  }

  public static function findLocalizedEndUserOrders($params,$oci)
  {
    $sql = self::get_select();
    
    if( $add=self::set_ids($params,$ids) )
      $sql.=$add;
    else
      return false; 
   
    $sql.=self::bind_array($ids,$oci);

    /*  // required field; closed
    if( $close=$params->closed->_value || ($close=$params->closed->_value)==0 )
      {
	if( $close=='true' )
	  $oci->bind("closed_bind",'Y');
	else
	  $oci->bind("closed_bind",'N');

	$sql.="and closed=:closed_bind\n";
	}*/
    //required field; closed
    /*if( isset($params->closed->_value) )
      {
	$close=$params->closed->_value;
	if( $close=='true' || $close==1 )
	  $oci->bind("closed_bind",'Y');
	else
	  $oci->bind("closed_bind",'N');

	$sql.="and closed=:closed_bind\n";
	
	}*/
    if( isset($params->closed->_value) )
      {
	$oci->bind( "ordertype_bind","enduser_request");
	$sql.="and ordertype=:ordertype_bind\n";

	$close=$params->closed->_value;
	if( $close=='true' || $close==1 )
	  $oci->bind("closed_bind",'Y');
	else
	  $oci->bind("closed_bind",'N');

	$sql.="and closed=:closed_bind\n";	
      }
    else
      return false;

    $add = self::setRequestGeneral($params,$oci);
    if( $add !== false )
      $sql.=$add;
    else
      return false;

    return $sql;   
  }

  public static function findClosedIllOrders($params,$oci)
  {
    // TDOO filter on some field in ors_order (orderstatus)
    $sql = self::get_select();
    
    if( $add=self::set_ids($params,$ids) )
      $sql.=$add;
    else
      return false; 
   
    $sql.=self::bind_array($ids,$oci);

    $oci->bind("ordertype_bind","inter_library_request");
    $sql.="and ordertype=:ordertype_bind\n";

    // required field; orderStatus
      if( $status=$params->orderStatus->_value )
      {
	if( $status=='shipped' )
	  $sql.="and isshipped='Y'\n";
	else
	  {
	    $oci->bind("orderStatus_bind",$status);
	    $sql.="and provideranswer=:orderStatus_bind\n";
	  }
      }
      else
	$sql.="AND provideranswer IS NOT NULL\n";    
    

    $add = self::setRequestGeneral($params,$oci);
    if( $add !== false )
      $sql.=$add;
    else
      return false;

    return $sql;     
  }

  public static function findOpenIllOrders($params,$oci)
  {
    $sql = self::get_select();
    
    if( $add=self::set_ids($params,$ids) )
      $sql.=$add;
    else
      return false; 
   
    $sql.=self::bind_array($ids,$oci);
   
    $oci->bind("ordertype_bind","inter_library_request");
    $sql.="and ordertype=:ordertype_bind\n";

    $sql.="and provideranswer IS NULL\n";

    //$sql.="and isshipped IS NOT NULL\n";
    
    //    $oci->bind("autoforward_bind","automated");
    //$sql.="and autoforwardresult=:autoforward_bind\n";

    $add = self::setRequestGeneral($params,$oci);
    if( $add !== false )
      $sql.=$add;
    else
      return false;

    return $sql;     

  }

  public static function getOrderStatus($params,$oci)
  {
    $sql = self::get_select();
    
    if( $add=self::set_ids($params,$ids) )
      $sql.=$add;
    else
      return false; 
   
    $sql.=self::bind_array($ids,$oci);

    // required field; orderId
    if( $orderId=$params->orderId->_value )
      {
	$oci->bind("orderId_bind",$orderId);
	$sql.="and orderid=:orderId_bind\n";
      }
    else
      return false;

    $add = self::setRequestGeneral($params,$oci);
    if( $add !== false )
      $sql.=$add;
    else
      return false;

    return $sql;     
  }

  public static function findOrderType($params,$oci)
  {
    $sql = self::get_select();
    
    if( $add=self::set_ids($params,$ids) )
      $sql.=$add;
    else
      return false; 
   
    $sql.=self::bind_array($ids,$oci);

    // required fields articleDirect OR kvik OR norfri
    if( $articleDirect=$params->articleDirect->_value )
      {
	$oci->bind("articleDirect_bind",$articleDirect);
	$sql.="and articledirect=:articleDirect_bind\n";
      }
    elseif( $kvik=$params->kvik->_value )
       {
	$oci->bind("kvik_bind",$kvik);
	$sql.="and kvik=:kvik_bind\n";
      }
    elseif( $norfri=$params->norfri->_value )
      {
	$oci->bind("norfri_bind",$norfri);
	$sql.="and norfri=:norfri_bind\n";
      }
    else
      return false;

    $add = self::setRequestGeneral($params,$oci);
    if( $add !== false )
      $sql.=$add;
    else
      return false;

    return $sql;     
  }

  public static function findNonAutomatedOrders($params,$oci)
  {
    $sql = self::get_select();
    
    if( $add=self::set_ids($params,$ids) )
      $sql.=$add;
    else
      return false; 
   
    $sql.=self::bind_array($ids,$oci);

 
    $add = self::setRequestGeneral($params,$oci);
    if( $add !== false )
      $sql.=$add;
    else
      return false;

    $oci->bind('auto_bind','non_automated');
    $sql.="AND AUTOFORWARDRESULT=:auto_bind\n";

    return $sql;      
    
  }

  public static function findAutomatedOrders($params,$oci)
  {
    $sql = self::get_select();
    
    if( $add=self::set_ids($params,$ids) )
      $sql.=$add;
    else
      return false; 
   
    $sql.=self::bind_array($ids,$oci);
    // TODO restrict selection somehow 

    $oci->bind("ordertype_bind","inter_library_request");
    $sql.="and ordertype=:ordertype_bind\n";
    
    $oci->bind('auto_bind','automated');
    $sql.="AND AUTOFORWARDRESULT=:auto_bind\n";

    $add = self::setRequestGeneral($params,$oci);
    if( $add !== false )
      $sql.=$add;
    else
      return false;

    return $sql;      
  }

  public static function findAllOrders($params,$oci)
  {
    // required fields are requester OR responderAgencyId, agency
   
    $sql = self::get_select();
    
    

    if( $add=self::set_ids($params,$ids) )
      $sql.=$add;
    else
      return false; 
   


    $sql.=self::bind_array($ids,$oci);

    
    // $oci->bind("ordertype_bind","inter_library_request");
    //$sql.="and ordertype=:ordertype_bind\n";

    $add = self::setRequestGeneral($params,$oci);
    if( $add !== false )
      $sql.=$add;
    else
      return false;


    return $sql;      
  }

  public static function findOrdersFromUser($params,$oci)
  {
    $sql = self::get_select();
    
    if( $add=self::set_ids($params,$ids) )
      $sql.=$add;
    else
      return false;
   
    $sql.=self::bind_array($ids,$oci);

    // user;required fields: userId OR userMail OR userName (choice)
    if( $userId=$params->userId->_value )
      {
	$oci->bind("userId_bind",$userId);
	$sql.="and userid=:userId_bind\n";
      }
    elseif( $userMail=$params->userMail->_value )
      {
	$oci->bind("userMail_bind",$userMail.'%');
	$sql.="and usermail like :userMail_bind\n";
      }
    elseif( $userName=$params->userName->_value )
      {
	$oci->bind("userName_bind",'%'.$userName.'%');
	//$sql.="and username like :userName_bind\n";
	$sql.="and contains( oi.username,:userName_bind, 1 ) > 0";
      }
    elseif( $ftext=$params->userFreeText->_value )
      {
	$oci->bind("ftxt_bind",$ftext.'%');
	$sql.="and (userName like :ftxt_bind OR userMail like :ftxt_bind OR userId like :ftxt_bind)\n";
      }
    else
      return false;

    $add = self::setRequestGeneral($params,$oci);
    if( $add !== false )
      $sql.=$add;
    else
      return false;
    
    return $sql;    
  }
  
  public static function findOrdersFromUnknownUser($params,$oci)
  {
    $sql = self::get_select();
    
    if( $add=self::set_ids($params,$ids) )
      $sql.=$add;
    else
      return false;
    
    $sql.=self::bind_array($ids,$oci);

    $oci->bind('userIdAuthenticated_bind','no');
    $sql.="AND USERIDAUTHENTICATED=:userIdAuthenticated_bind";
   
    $add = self::setRequestGeneral($params,$oci);
    if( $add !== false )
      $sql.=$add;
    else
      return false;       
   
    
    return $sql;    
  }  

  public static function bibliographicSearch($params,$oci)
  {
    $sql = self::get_select();
    
    if( $add=self::set_ids($params,$ids) )
       $sql.=$add;
    else
      return false;

     $sql.=self::bind_array($ids,$oci);
    
    // optional parameters;author,bibliographicRecordId,isbn,issn,mediumType,title,bibliographicFreeText
    if( $author=$params->author->_value )
      {
	$oci->bind("author_bind","%".$author."%");
	//	echo "'".$author."%'";
	//$sql.=" and author like :author_bind\n";
	$sql.="and contains( oi.author,:author_bind, 1 ) > 0";
      }
    if( $bibliographicRecordId=$params->bibliographicRecordId->_value )
      {
	$oci->bind("bibRec_bind",$bibliographicRecordId);
	$sql.="and bibliographicrecordid=:bibRec_bind\n";
      }
    if( $isbn=$params->isbn->_value )
      {
	$oci->bind("isbn_bind",$isbn);
	$sql.="and isbn=:isbn_bind\n";
      }

    if( $issn=$params->issn->_value )
      {
	$oci->bind("issn_bind",$issn);
	$sql.="and issn=:issn_bind\n";
      }

    if( $mediumType=$params->mediumType->_value )
      {
	$oci->bind("mediumType_bind",$mediumType);
	$sql.="and mediumtype=:mediumType_bind\n";
      }

    if( $title=$params->title->_value )
      {
	$oci->bind("title_bind","%".$title."%");
	$sql.="and contains( oi.title,:title_bind, 2 ) > 0";

	//	$sql.="and title like:title_bind\n";
      }

    if( $ftxt=$params->bibliographicFreeText->_value )
      {
	$oci->bind("ftext_bind","%".$ftxt.'%');
       
	//$sql.="and (o.title like :ftext_bind OR o.author like :ftext_bind)\n";
	$sql.="and ((contains( oi.title,:ftext_bind, 3 ) > 0) OR (contains( oi.author,:ftext_bind, 4 ) > 0))\n";
      }
     
    /* echo $sql;
       exit;*/

    $add = self::setRequestGeneral($params,$oci);
    if( $add !== false )
      $sql.=$add;
    else
      return false;
       
    return $sql;    
  } 

  public static function findSpecificOrder($params,$oci)
  {
    // required fields requesterAgencyId OR responderAgencyId, orderId, agency
     $sql = self::get_select();
    
      if( $add=self::set_ids($params,$ids) )
      $sql.=$add;
    else
      return false;
   
      $sql.=self::bind_array($ids,$oci);

    // required field orderId
    if( is_array($params->orderId ) )
      {
	$sql.=" and o.orderid in(";
	$sql.=self::bind_array($params->orderId,$oci,"orderId");

	//	echo $sql;
	//exit; 
      }    
    elseif( $orderId=$params->orderId->_value )
      {
	$oci->bind("orderId_bind",$orderId);
	$sql.=" and o.orderid=:orderId_bind\n";
      }
    else
      return false;

    /*    $add = self::setRequestGeneral($params,$oci);
    if( $add !== false )
      $sql.=$add;
    else
    return false;*/
   
    return $sql;
  }

  public static function findOrdersWithStatus($params,$oci)
  {
    
  }

  public static function findOrdersWithAutoForwardReason($params,$oci)
  {
     $sql = self::get_select();
    
     if( $add=self::set_ids($params,$ids) )
      $sql.=$add;
    else
      return false;
   
    $sql.=self::bind_array($ids,$oci);
    
    // autoForwardReason ;required field;
    /*
      valid fields
       <xs:enumeration value="error"/>
       <xs:enumeration value="new_for_requester"/>
       <xs:enumeration value="new_for_responder"/>
       <xs:enumeration value="no_delivery_date"/>
       <xs:enumeration value="no_provider"/>
       <xs:enumeration value="not_for_loan"/>
       <xs:enumeration value="not_on_shelf"/>
       <xs:enumeration value="not_possible"/>
       <xs:enumeration value="test"/>
       <xs:enumeration value="user_date_exceeded"/>       
     */

    if( $reason=$params->autoForwardReason->_value )
      {
	$oci->bind("reason_bind",$reason);
	$sql.="and autoforwardreason=:reason_bind\n";
      }
    else
      return false;

    $add = self::setRequestGeneral($params,$oci);
    if( $add !== false )
      $sql.=$add;
    else
      return false;
    
    return $sql;     
  }

  private static function set_ids($params,&$ids)
  {
    $sql.=" WHERE ";
    if( $ids = $params->requesterAgencyId )
      // $sql.="requesterid in(";
    $sql.="pickUpAgencyId in(";
    elseif( $ids = $params->responderAgencyId )
      $sql.="RESPONDERID in(";
    else
      return false;

    return $sql;
  }

  /**
     Set parameters that are common(general) for (allmost) all requests
   */
  private static function setRequestGeneral($params,$oci)
  {
     // ordersystem
    if( $orderSystem = $params->orderSystem->_value )
      {
	$oci->bind("system_bind",$orderSystem);
	$sql.="and ordersystem=:system_bind\n";
      }

    // fromDate
    if( $fromDate = $params->fromDate->_value )
      {
	//if( !$fdate=self::check_date_time($fromDate) )
	if( !$fdate=self::check_date($fromDate) )
	  return false;

	$oci->bind("fromDate_bind",$fdate);
	//	$sql.=" and to_char(creationdate,'YYYY-MM-DD HH24:MI:SS') >=:fromDate_bind\n";
	//$sql.=" and to_char(creationdate,'YYYY-MM-DD') >=:fromDate_bind\n";
	// jgn's suggestion to avoid string-comparison
	$sql.=" and creationdate >=to_date(:fromDate_bind,'YYYY-MM-DD')\n";
      }

     // toDate
    if( $toDate = $params->toDate->_value )
      {
	//if( !$tdate=self::check_date_time($toDate) )
	if( !$tdate=self::check_date($toDate) )
	  return false;
	$oci->bind("toDate_bind",$tdate);
	//	$sql.=" and to_char(creationdate,'YYYY-MM-DD HH24:MI:SS') <=:toDate_bind\n";
	//$sql.=" and to_char(creationdate,'YYYY-MM-DD') <=:toDate_bind\n";
	// jgn's suggestion to avoid string-comparison
	$sql.=" and creationdate<to_date(:toDate_bind,'YYYY-MM-DD')\n";
      }     

  
    if( $moresql=OFO_vip::set_libraries($params) )
      $sql.=$moresql;
    else
      {	
	//	self::$error="no libraries found";
	return false;
      }  

    if( $sort = $params->sortKey->_value )
      {
	if( $sort=='creationDateAscending' )
	  $sql.=" ORDER BY creationdate asc\n";
	elseif( $sort == 'creationDateDescending' )
	  $sql.=" ORDER BY creationdate desc\n";
	else
	  return false;
      }
    // agency ??? 
   

    return $sql;
  }

  /**
     check if given string can be converted to date; 
     returns date('Ymd') or false
   */
  private static function check_date($date)
  {
    /* if( $time=strtotime($date) )
      {
	$date=date('Ymd H:i',$time );
	//	echo $date;
	//exit;
	return $date;
      }
      return false;*/

    $reg='/([0-9]{4})-([0-9]{2})-([0-9]{2})/';
    if( preg_match($reg,$date,$matches) )
      {
	$time=strtotime($date);
	//	$date=date('Y-m-d H:i:s',$time );
	$date=date('Y-m-d',$time );

	return $date;
      }    
    return false;
  }

  //check xml dateTime
  private function check_date_time($dateTime)
  {
    $reg='/([0-9]{4})-([0-9]{2})-([0-9]{2})([T]|[ ])([0-1][0-9]|[2][0-3]):([0-5][0-9]):([0-5][0-9])/';
    //$reg='/([0-9]{4})-([0-9]{2})-([0-9]{2})T([0-1][0-9]|[2][0-3]):([0-5][0-9])/';
    if( preg_match($reg,$dateTime,$matches) )
      {
	if( strpos($dateTime,'T') )
	  return str_replace('T',' ',$dateTime);
	return $dateTime;
      }

    return false;    
  }

  /**
     Run through given array ($key=>$val). Bind variables to given instance of oci-class.
     Return sql.
   */
  private static function bind_array($ids,$oci,$prefix="")
  {
    if( is_array($ids) )
      {
	$count=1;
	// make an array
	foreach( $ids as $key=>$val )
	  $idarr[$prefix."bind".$count++]=$val->_value;
	
	//iterate array; generate sql
	foreach( $idarr as $key=>$val )
	  {
	    $oci->bind($key,$idarr[$key],-1,SQLT_INT);
	    //$oci->bind($key,$idarr[$key]);
	    $sql.=":".$key.",";	
	  }
	
	// remove trailing ','
	$sql=substr($sql,0,-1);
	$sql.=")\n";
      }
    else
      {
	$oci->bind($prefix."bind_ID",$ids->_value,-1,SQLT_INT);
	//$oci->bind($prefix."bind_ID",$ids->_value);
	$sql.=":bind_ID)\n";
      }

    return $sql;
  }
}

class OFO_agency
{
  // WHAT IS THIS and how do i authenticate
  public static function authenticate($agency)
  {
    // return self::error();
    return;
  }

  public static function error()
  {
    return "orssearch order service not available";
  }

}

  
?>

