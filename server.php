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
require_once "OLS_class_lib/cql2solr_class.php";
require_once "OLS_class_lib/oci_class.php";
require_once("xsdparse.php");

class openFindOrder extends webServiceServer {

  public function __construct(){
    parent::__construct('openfindorder.ini');
  }

 /** \brief
  *
  */

  public function findAllOrders($param)
  {
    if( !OFO_agency::authenticate($param->agency->_value) )
      die( "findAllOrders:not authenticated" );

    $OFO = new OFO_database("findAllOrders",$this->config);
    $orders=$OFO->findAllOrders($param);
   
    return $this->findOrderResponse($orders);
  }

  public function findOrdersFromUser($param)
  {
     var_dump($param); die();
  }

  public function findOrdersFromUnknownUser($param)
  {
     var_dump($param); die();
  }

  public function bibliographicSearch($param)
  {
     var_dump($param); die();
  }

  private function findOrderResponse($orders)
  {
    $response->findOrdersResponse->_namespace='http://oss.dbc.dk/ns/openfindorder';
    // error
    $error->_namespace='http://oss.dbc.dk/ns/openfindorder';
    $error->_value="testhest";

    $response->findOrdersResponse->_value->error=$error;

    foreach( $orders as $order )
      $response->findOrdersResponse->_value->result[]=$order;

    return $response;      
  }
}

/*
 * MAIN
 */

$ws=new openFindOrder();

$ws->handle_request();

class OFO_database
{
  private $xmlfields=array();
  private $action;
  private $fields=array();

  public function __construct($action,$config)
  {
    $this->action=$action;    
    if( $config )
      {
	$arr=$config->get_value("action");
	
	foreach($arr[$action] as $key=>$val)
	  $this->fields[]=$val;

      }

    $schema=new xml_schema();
    $schema->get_from_file('openfindorder.xsd');
    $this->xmlfields=$schema->get_sequence_array('order');
  }
  
  public function findAllOrders($param)
  {
    // get sql for database-call
    if($oci=self::execute($param))
      {
	$resultPosition=1;
	while( $data=$oci->fetch_into_assoc() )
	  {
	    if( $order=$this->get_order($data,$resultPosition) )
	      {
		$orders[]=$order;
		$resultPosition++;
	      }
	  }
      }
    
    return $orders;
  }

  private function get_order($data,$resultPosition)
  {
    $ret->_namespace='http://oss.dbc.dk/ns/openfindorder';
  
    $ret->_value->resultPosition->_value=$resultPosition;;
    $ret->_value->resultPosition->_namespace='http://oss.dbc.dk/ns/openfindorder';
   
    // column-names from database MUST match xml-fields for this loop to work
    foreach( $data as $key=>$val )
      {
	if( $xmlkey=array_search($key,$this->xmlfields) )
	  if( $val )
	    {	    
	      $ret->_value->$xmlkey->_value=$val;
	      $ret->_value->$xmlkey->_namespace='http://oss.dbc.dk/ns/openfindorder';
	    }
      }
    return $ret;
  }

  private static function set_sql($param)
  {
    // TODO implement
    $sql="SELECT * FROM ORS_ORDER WHERE REQUESTERID=716700";
    
    return $sql;
    
  }

  private static function execute($param)
  {
    $connectionstring="ors_test/ors_test@tora1";
    $oci=new oci($connectionstring);

    $oci->connect();
    if( $end=$param->stepValue->_value && $begin=$param->start->_value )
      $oci->set_pagination((int)$begin,(int)$end);

    $sql = self::set_sql($param);
    $oci->set_query($sql);    

    return $oci;
  }

}

class OFO_agency
{
  // WHAT IS THIS and how do i authenticate
  public static function authenticate($agency)
  {
    return true;
  }  
}
?>

