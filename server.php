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

class openFindOrder extends webServiceServer {

  public function __construct(){
    webServiceServer::__construct('openfindorder.ini');
  }

 /** \brief
  *
  */

  public function findAllOrders($param)
  {
    var_dump($param); die();
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
}

/*
 * MAIN
 */

$ws=new openFindOrder();

$ws->handle_request();

/* \brief  wrapper for oci_class
 *  handles database transactions
*/
class db
{
  // member to hold instance of oci_class
  private $oci;
  // constructor
  function db()
  {
    $this->oci = new oci(VIP_US,VIP_PW,VIP_DB);
    $this->oci->connect();
  }

  function bind($name,$value,$type=SQLT_CHR)
  {
    $this->oci->bind($name, $value, -1, $type);
  }

  function query($query)
  {
    $this->oci->set_query($query);
  }

  /** return one row from db */
  function get_row()
  {
    return $this->oci->fetch_into_assoc();
  }

  /** destructor; disconnect from database */
  function __destruct()
  {
    //  if( $this->oci )
      $this->oci->destructor();
  }

  /** get error from oci-class */
  function get_error()
  {
    return $this->oci->get_error_string();
  }
}


?>

