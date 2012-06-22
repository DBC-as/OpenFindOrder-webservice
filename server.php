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

define(DEBUG_x, FALSE);

require_once('OLS_class_lib/webServiceServer_class.php');
require_once('OLS_class_lib/cql2solr_class.php');
require_once('OLS_class_lib/oci_class.php');
require_once('xsdparse.php');
require_once('OLS_class_lib/memcache_class.php');
//require_once('stat_class.php');

class openFindOrder extends webServiceServer {
  //public $stat;

  /** \brief
      constructor; start watch; call parent's constructor
   */
  public function __construct() {
    parent::__construct('openfindorder.ini');

    define('THIS_NAMESPACE', $this->xmlns['ofo']);
    $this->watch->start('openfindorderWS');
    //  $this->stat = new stats();
  }

  /** \brief
      destructor: stop watch; log for statistics
   */
  public function __destruct() {
    $this->watch->stop('openfindorderWS');
    //verbose::log(TIMER, $this->watch->dump());
  }

  /** \brief Echos config-settings
  *
  */
  public function show_info() {
    echo '<pre>';
    echo 'version             ' . $this->config->get_value('version', 'setup') . '<br/>';
    echo 'log                 ' . $this->config->get_value('logfile', 'setup') . '<br/>';
    echo 'db                  ' . $this->config->get_value('connectionstring', 'setup') . '<br/>';
    echo 'xsd                 ' . $this->config->get_value('schema', 'setup') . '<br/>';
    echo 'wsdl                ' . $this->config->get_value('wsdl', 'setup') . '<br/>';

    echo 'implemented methods:' . '<br/>';
    $methods = $this->config->get_value('soapAction', 'setup');
    foreach ($methods as $key =>$value) {
      echo '    ' . $value . '<br/>';
    }

    echo '</pre>';
    die();
  }

  /** \brif
      The service 
   */
  public function findManuallyFinishedIllOrders($param) {
    if ($error=OFO_agency::authenticate($param->agency->_value))
      return $this->send_error($error);

    $OFO_s = new OFO_solr($this->soap_action, $this->config);
    $orders = $OFO_s->findOrders($param);

    return $this->findOrderResponse($orders, $OFO_s->numrows);
  }

  /** \brif
      The service 
   */
  public function findAllOpenEndUserOrders($param) {
    if ($error = OFO_agency::authenticate($param->agency->_value))
      return $this->send_error($error);

    $OFO_s = new OFO_solr($this->soap_action, $this->config);
    $orders = $OFO_s->findOrders($param);

    return $this->findOrderResponse($orders, $OFO_s->numrows);
  }

  /** \brif
      The service request for orders on material not localized to the end user agency.
   */
  public function findNonLocalizedEndUserOrders($param) {
    if ($error = OFO_agency::authenticate($param->agency->_value))
      return $this->send_error($error);

    $OFO_s = new OFO_solr($this->soap_action, $this->config);
    $orders = $OFO_s->findOrders($param);

    return $this->findOrderResponse($orders, $OFO_s->numrows);
  }

  /** \brief
      The service request for orders on material localized to the end user agency.
   */
  public function findLocalizedEndUserOrders($param) {
    if ($error = OFO_agency::authenticate($param->agency->_value))
      return $this->send_error($error);

    $OFO_s = new OFO_solr($this->soap_action, $this->config);
    $orders = $OFO_s->findOrders($param);

    return $this->findOrderResponse($orders, $OFO_s->numrows);
  }

  /** \brief

   */
  public function findClosedIllOrders($param) {
    if ($error = OFO_agency::authenticate($param->agency->_value))
      return $this->send_error($error);

    $OFO_s = new OFO_solr($this->soap_action, $this->config);
    $orders = $OFO_s->findOrders($param);

    return $this->findOrderResponse($orders, $OFO_s->numrows);
  }

  /** \brief
   */
  public function findOpenIllOrders($param) {
    if ($error = OFO_agency::authenticate($param->agency->_value))
      return $this->send_error($error);

    $OFO_s = new OFO_solr($this->soap_action, $this->config);
    $orders = $OFO_s->findOrders($param);

    return $this->findOrderResponse($orders, $OFO_s->numrows);
  }


  /** \brief
   */
  public function findAllIllOrders($param) {
    if ($error = OFO_agency::authenticate($param->agency->_value))
      return $this->send_error($error);

    $OFO_s = new OFO_solr($this->soap_action, $this->config);
    $orders = $OFO_s->findOrders($param);

    return $this->findOrderResponse($orders, $OFO_s->numrows);
  }

  /** \brief
   */
  public function findAllNonIllOrders($param) {
    if ($error = OFO_agency::authenticate($param->agency->_value))
      return $this->send_error($error);

    $OFO_s = new OFO_solr($this->soap_action, $this->config);
    $orders = $OFO_s->findOrders($param);

    return $this->findOrderResponse($orders, $OFO_s->numrows);
  }


  /** \brief
   * The service request for all orders (optionally for a specific order system)
   * @param; request parameters in request-xml object.
   */
  public function findAllOrders($param) {
    if ($error = OFO_agency::authenticate($param->agency->_value))
      return $this->send_error($error);

    $OFO_s = new OFO_solr($this->soap_action, $this->config);
    $orders = $OFO_s->findOrders($param);

    return $this->findOrderResponse($orders, $OFO_s->numrows);
  }

  /**\brief
   * The service request for a specific order (orderId)
   * @param; request parameters in request-xml object.
   */
  public function findSpecificOrder($param) {
    if ($error = OFO_agency::authenticate($param->agency->_value))
      return $this->send_error($error);

    $OFO_s = new OFO_solr($this->soap_action, $this->config);
    $orders = $OFO_s->findOrders($param);

    return $this->findOrderResponse($orders, $OFO_s->numrows);
  }

  /**\brief
   * The service request for orders from a specific user (userId, userName or userMail)
   * @param; request parameters in request-xml object.
   */
// 2DO - done
  public function findOrdersFromUser($param) {
    if ($error = OFO_agency::authenticate($param->agency->_value))
      return $this->send_error($error);

    $OFO_s = new OFO_solr($this->soap_action, $this->config);
    $orders = $OFO_s->findOrders($param);

    return $this->findOrderResponse($orders, $OFO_s->numrows);
  }

  /**\brief
   * The service request for orders from unknown users (general)
   * @param; request parameters in request-xml object.
   */
// 2DO - done
  public function findOrdersFromUnknownUser($param) {
    if ($error = OFO_agency::authenticate($param->agency->_value))
      return $this->send_error($error);

    $OFO_s = new OFO_solr($this->soap_action, $this->config);
    $orders = $OFO_s->findOrders($param);

    return $this->findOrderResponse($orders, $OFO_s->numrows);
  }

  /**\brief
   * -- not yet defined
   *
   */
  public function findOrdersWithStatus($param) {
    if ($error = OFO_agency::authenticate($param->agency->_value))
      return $this->send_error($error);
/*
    $OFO_s = new OFO_solr($this->soap_action, $this->config);
    $orders = $OFO_s->findOrders($param);

    return $this->findOrderResponse($orders, $OFO_s->numrows);
*/
    return $this->send_error('placeholder - request not yet defined');
  }

  /**\brief
   * The service request for reason for auto forward (autoForwardReason)
   * @param; request parameters in request-xml object.
   */
// 2DO - done
  public function findOrdersWithAutoForwardReason($param) {
    if ($error = OFO_agency::authenticate($param->agency->_value))
      return $this->send_error($error);

    $OFO_s = new OFO_solr($this->soap_action, $this->config);
    $orders = $OFO_s->findOrders($param);

    return $this->findOrderResponse($orders, $OFO_s->numrows);
  }

  /**\brief
   * The service request for automatically forwarded orders (general)
   * @param; request parameters in request-xml object.
   */
  public function findAutomatedOrders($param) {
    if ($error = OFO_agency::authenticate($param->agency->_value))
      return $this->send_error($error);

    $OFO_s = new OFO_solr($this->soap_action, $this->config);
    $orders = $OFO_s->findOrders($param);

    return $this->findOrderResponse($orders, $OFO_s->numrows);
  }

  /**\brief
   * The service request for orders from a specific ill-cooperation (kvik, norfri or articleDirect)
   * @param; request parameters in request-xml object.
   */
// 2DO - done
  public function findOrderType($param) {
    if ($error = OFO_agency::authenticate($param->agency->_value))
      return $this->send_error($error);

    $OFO_s = new OFO_solr($this->soap_action, $this->config);
    $orders = $OFO_s->findOrders($param);

    return $this->findOrderResponse($orders, $OFO_s->numrows);
  }

  /**\brief
   *  The service request for a biblographical search of orders
   * @param; request parameters in request-xml object
   */
  public function bibliographicSearch($param) {
    if ($error = OFO_agency::authenticate($param->agency->_value))
      return $this->send_error($error);

    $OFO_s = new OFO_solr($this->soap_action, $this->config);
    $orders = $OFO_s->findOrders($param);

    return $this->findOrderResponse($orders, $OFO_s->numrows);
  }

  /**\brief
   * The service request for the status of an order
   * @param; request parameters in request-xml object
   */
// 2DO - done
  public function getOrderStatus($param) {
    if ($error = OFO_agency::authenticate($param->agency->_value))
      return $this->send_error($error);

    $OFO_s = new OFO_solr($this->soap_action, $this->config);
    $orders = $OFO_s->findOrders($param);

    return $this->findOrderResponse($orders, $OFO_s->numrows);
  }

  /**\brief
   * The service request for non-automatatically forwarded orders (general)
   *  @param; request parameters in request-xml object
   */
// 2DO - done
  public function findNonAutomatedOrders($param) {
    if ($error = OFO_agency::authenticate($param->agency->_value))
      return $this->send_error($error);

    $OFO_s = new OFO_solr($this->soap_action, $this->config);
    $orders = $OFO_s->findOrders($param);

    return $this->findOrderResponse($orders, $OFO_s->numrows);
  }

  /**\brief
   * Generate response-object from given array of orders.
   * @orders; array of orders
   * return; orders as xml-objects
   */
  private function findOrderResponse($orders, $number_of_orders = NULL) {
    $response->findOrdersResponse->_namespace = THIS_NAMESPACE;

    // error from OFO_database
    if ($orders === FALSE) {
      // TODO log
      return $this->send_error(OFO_database::$error);
    }

    // empty result-set
    if (empty($orders))
      return $this->send_error('no orders found');

//    $this->stat->orders+ = count($orders);

    // TODO - this line is for test-purpose only. Remove in production
    //$response->findOrdersResponse->_value->pure_sql->_value = OFO_database::$pure_sql;

    $result = &$response->findOrdersResponse->_value->result;
    $result->_namespace = THIS_NAMESPACE;
    $result->_value->numberOfOrders->_namespace = THIS_NAMESPACE;
    // $result->_value->numberOfOrders->_value = count($orders);
    if (is_null($number_of_orders))
      $result->_value->numberOfOrders->_value = OFO_database::$numrows;
    else
      $result->_value->numberOfOrders->_value = $number_of_orders;

    if ($orders->error) {
      $orders->error->_namespace = THIS_NAMESPACE;
      $response->findOrdersResponse->_value = $orders;
    } else
      $result->_value->order = $orders;

    return $response;
  }

  /** \brief
   * send errormessage as xml response-object
   */
  private function send_error($message) {
    $response->findOrdersResponse->_namespace = THIS_NAMESPACE;

    // TODO - this line is for test-purpose only. Remove in production
    //$response->findOrdersResponse->_value->pure_sql->_value = OFO_database::$pure_sql;

    $error->_namespace = THIS_NAMESPACE;
    $error->_value = $message;
    $response->findOrdersResponse->_value->error = $error;

    return $response;
  }
}

/*
 * MAIN
 */

$ws = new openFindOrder();

$ws->handle_request();

/**\brief
 * Class to handle connection to solr and correlation to xml-schema
 */
class OFO_solr {
  public static $error;
  public static $vip_connect;
  public static $numrows;

  private $curl;
  private $xmlfields = array();
  private $action;
  private $fields = array();
  private $solr_url;
  private $agency_url;

  /**\brief
   * load setups and parse xsd for fields to return
   * @param; soap_action and config-object
   */
  public function __construct($action, $config) {
    self::$error = null;
    if (!$this->solr_url = $config->get_value('solr_order_uri', 'setup'))
      die('no url to order-SOLR in config-file');
    if (!$this->agency_url = $config->get_value('openagency_agency_list', 'setup'))
      die('no url to openAgency in config-file');

    // get xml schema
    $schemafile = $config->get_value('schema', 'setup');
    if (!file_exists($schemafile))
      die('xsd not found: ' . $schemafile);

    $schema = new xml_schema();
    $schema->get_from_file($schemafile);

    // set xml-fields
    $this->action = $action;
    if ($this->action == 'getOrderStatus')
      $this->xmlfields = $schema->get_sequence_array('getOrderStatusResponse');
    else
      $this->xmlfields = $schema->get_sequence_array('order');

    $this->curl = new curl();
    $this->curl->set_option(CURLOPT_TIMEOUT, 30);
  }

  public function __destruct() { }

  /**\brief
   * Get orders from database.
   * @param; request parameters as xml-object
   * return; array of found orders
   */
  public function findOrders($param) {
    $consistency = $this->check_agency_consistency($param);
    if ($consistency === TRUE) {
      $solr_query = $this->set_solr_query($param);
      if ($res = $this->do_solr($param, $solr_query)) {
        $this->numrows = (int) $res['response']['numFound'];
        foreach ($res['response']['docs'] as &$doc) {
          $orders[] = $this->extract_fields($doc, ++$start);
        }
      }
      else {
        $orders->error->_value = 'no orders found';
      }
    }
    else {
      $orders->error->_value = $consistency;
    }
    return $orders;
  }

  /**\brief
   * Fetch branches for the agency and check against requesterAgencyId or responderAgencyId 
   * @param; request parameters as xml-object
   * return; FALSE if requesterAgencyId or responderAgencyId contains non-valid agency
   */
  private function check_agency_consistency(&$param) {
    $agency = $this->strip_agency($param->agency->_value);
    $url = sprintf($this->agency_url, $agency);
    $res = unserialize($this->curl->get($url));
    if ($res && $res->pickupAgencyListResponse->_value->library) {
      foreach ($res->pickupAgencyListResponse->_value->library[0]->_value->pickupAgency as $sublib) {
        $libs[] = $sublib->_value->branchId->_value;
      }
      if ($param->requesterAgencyId) 
        return $this->check_in_list($libs, $param->requesterAgencyId, 'requester_not_in_agency');
      else
        return $this->check_in_list($libs, $param->responderAgencyId, 'responder_not_in_agency');
    }
    else {
      $curl_status = $this->curl->get_status();
      verbose::log(ERROR, 'Error getting agency: ' . $url . 
                          ' http: ' . $curl_status['http_code'] . 
                          ' errno: ' . $curl_status['errno'] . 
                          ' error: ' . $curl_status['error']);
      return 'cannot_find_agency';
    }
  }

  private function check_in_list($valid_list, $selected_list, $error_text) {
    if (is_array($selected_list)) {
      foreach ($selected_list as $sel) {
        if ($sel->_value && !in_array($this->strip_agency($sel->_value), $valid_list))
          return $error_text;
      }
    }
    else {
      return in_array($this->strip_agency($selected_list->_value), $valid_list);
    }
    return TRUE;
  }

  /**\brief
   * Handle one order.
   * @data; a row of data from solr
   * @resultPosition; rownumber of result
   * return; one order as xml-object
   */
  private function extract_fields(&$data, $resultPosition) {
    $ret->_namespace = THIS_NAMESPACE;

    $ret->_value->resultPosition->_value = $resultPosition;;
    $ret->_value->resultPosition->_namespace = THIS_NAMESPACE;

    // column-names from database MUST match xml-fields for this loop to work
    // new loop to ensure roworder as defined in xml-schema
    foreach ($this->xmlfields as $key =>$val) {
      if ($value =  $data[strtolower($val)]) {
        if ($value && $value != '0001-01-01' && $value != 'uninitialized') {
          if ($key  !=  'placeOnHold') {
            if ($value == 'yes')
              $value = 'true';
            if ($value == 'no')
              $value = 'false';
            if ($value == 'N')
              $value = 'false';
            if ($value == 'Y')
              $value = 'true';
          }
          if ($key == 'creationDate') {
            $value = str_replace(' ', 'T', $value);
            if (!strpos($value, 'Z')) $value .= 'Z';
          }
          $ret->_value->$key->_value = $value;
          $ret->_value->$key->_namespace = THIS_NAMESPACE;
        }
      }
    }
    return $ret;
  }

  private function do_solr($param, $solr_query) {
    if (!$start = $param->start->_value)
      $start = 1;
    if (!$rows = $param->stepValue->_value)
      $rows = 10;
    if ($param->sortKey->_value == 'creationDateAscending') {
      $sort = '&sort=creationdate%20asc';
    } 
    elseif ($param->sortKey->_value == 'creationDateDescending') {
      $sort = '&sort=creationdate%20desc'; 
    }
    $url = $this->solr_url . 
             'q=' . urlencode($solr_query) . 
             $sort .
             '&start=' . ($start - 1) . 
             '&rows=' . $rows .
             '&defType=edismax&debugQuery=on&wt=phps';
    verbose::log(DEBUG, 'Trying in solr with: ' . $url);
    $solr_result = $this->curl->get($url);
    if (empty($solr_result)) {
      $curl_status = $this->curl->get_status();
      verbose::log(ERROR, 'Error getting solr: ' . $url . 
                          ' http: ' . $curl_status['http_code'] . 
                          ' errno: ' . $curl_status['errno'] . 
                          ' error: ' . $curl_status['error']);
      return FALSE;
    }
    else {
      return unserialize($solr_result);
    }
  }

  /** \brief build the solr-search corresponding to the user-request
   *         use the solr edismax search handler syntax
   *         OR searches has to be field:(a OR b) and not (field:a OR field:b)
   *  return a solr-query string
   */
  private function set_solr_query($param) {
    switch ($this->action) {
      case "findManuallyFinishedIllOrders":
        $ret = 'ordertype:inter_library_request';
        if (isset($param->requesterOrderState->_value)) {
          $ret .= 'requesterorderstate:' . $param->requesterOrderState->_value;
        } 
        elseif (isset($param->providerOrderState->_value)) {
          $ret .= 'providerorderstate:' . $param->providerOrderState->_value;
        }
        $ret = $this->add_one_par($param->requesterAgencyId, 'requesterid', $ret);
        $ret = $this->add_one_par($param->responderAgencyId, 'responderid', $ret);
        $ret = $this->add_common_pars($param, $ret);
        break;
      case 'findAllOpenEndUserOrders':
        $ret = 'closed:N AND ordertype:(enduser_request OR enduser_illrequest)';
        $ret = $this->add_one_par($param->requesterAgencyId, 'requesterid', $ret);
        $ret = $this->add_one_par($param->responderAgencyId, 'responderid', $ret);
        $ret = $this->add_common_pars($param, $ret);
        break;
      case 'findAllOrders':
        $ret = '';
        $ret = $this->add_one_par($param->requesterAgencyId, 'requesterid', $ret);
        $ret = $this->add_one_par($param->responderAgencyId, 'responderid', $ret);
        $ret = $this->add_common_pars($param, $ret);
        break;
      case 'findAllIllOrders':
        $ret = 'ordertype:inter_library_request';
        $ret = $this->add_one_par($param->requesterAgencyId, 'requesterid', $ret);
        $ret = $this->add_one_par($param->responderAgencyId, 'responderid', $ret);
        $ret = $this->add_common_pars($param, $ret);
        break;
      case 'findAllNonIllOrders':
        $ret = 'ordertype:(enduser_request OR enduser_illrequest)';
        $ret = $this->add_one_par($param->requesterAgencyId, 'requesterid', $ret);
        $ret = $this->add_one_par($param->responderAgencyId, 'responderid', $ret);
        $ret = $this->add_common_pars($param, $ret);
        break;
      case 'findSpecificOrder':
        if ($param->orderType->_value == 'enduser_order') {
          $ret = 'ordertype:(enduser_request OR enduser_illrequest)';
        }
        elseif ($param->orderType->_value == 'inter_library_order') {
          $ret = 'ordertype:inter_library_request';
        }
        $ret = $this->add_one_par($param->orderId, 'orderid', $ret);
        $ret = $this->add_one_par($param->requesterAgencyId, 'requesterid', $ret);
        $ret = $this->add_one_par($param->responderAgencyId, 'responderid', $ret);
        $ret = $this->add_common_pars($param, $ret);
        break;
      case 'findOrdersFromUser':
        if ($param->orderType->_value == 'enduser_order') {
          $ret = 'ordertype:(enduser_request OR enduser_illrequest)';
        }
        elseif ($param->orderType->_value == 'inter_library_order') {
          $ret = 'ordertype:inter_library_request';
        }
        $ret = $this->add_one_par($param->userId, 'userid', $ret);
        $ret = $this->add_one_par($param->userMail, 'usermail', $ret);
        $ret = $this->add_one_par($param->userName, 'userName', $ret);
        $ret = $this->add_one_par($param->requesterAgencyId, 'requesterid', $ret);
        $ret = $this->add_one_par($param->responderAgencyId, 'responderid', $ret);
        $ret = $this->add_common_pars($param, $ret);
// Spooky ... this OR-part below has to be the last and no () around it ????
// apparently the edismax searchHandler parse (a OR b OR c) as some list where all members should be present
// Users are therefore recommended to user userId, userMail and userName instead of userFreeText
        if ($uft = $param->userFreeText->_value) {
          $ret .= ($ret ? ' AND ' : '') . 'userid:"' . $uft . '" OR usermail:"' . $uft . '" OR username:"' . $uft . '"';
        }
        break;
      case 'findOrdersFromUnknownUser':
        $ret = 'useridauthenticated:no';
        $ret = $this->add_one_par($param->requesterAgencyId, 'requesterid', $ret);
        $ret = $this->add_one_par($param->responderAgencyId, 'responderid', $ret);
        $ret = $this->add_common_pars($param, $ret);
        break;
      case 'bibliographicSearch':
        $ret = $this->add_one_par($param->author, 'author', $ret);
        //$ret = $this->add_one_par($param->bibliographicRecordId, 'bibliographicrecordid', $ret);  // not indexed
        //$ret = $this->add_one_par($param->isbn, 'isbn', $ret);  // not indexed
        //$ret = $this->add_one_par($param->issn, 'issn', $ret);  // not indexed
        // $ret = $this->add_one_par($param->mediumType, 'mediumtype', $ret);  // not indexed
        if ($param->bibliographicFreeText) {
          $ret = $this->add_one_par($param->bibliographicFreeText, 'author', $ret, 'AND (');
          $ret = '(' . $this->add_one_par($param->bibliographicFreeText, 'title', $ret, 'OR') . ')';
        }
        //$ret = $this->add_one_par($param->orderType, 'ordertype', $ret);
        $ret = $this->add_one_par($param->title, 'title', $ret);
        $ret = $this->add_common_pars($param, $ret);
        break;
      case 'findOrdersWithStatus':
        $ret = $this->add_common_pars($param, $ret);
        break;
      case 'findOrdersWithAutoForwardReason':
        $ret = $this->add_one_par($param->autoForwardReason, 'autoforwardreason', $ret);
        $ret = $this->add_one_par($param->requesterAgencyId, 'requesterid', $ret);
        $ret = $this->add_one_par($param->responderAgencyId, 'responderid', $ret);
        $ret = $this->add_common_pars($param, $ret);
        break;
      case 'findAutomatedOrders':
        $ret = 'ordertype:inter_library_request AND autoforwardresult:automated';
        $ret = $this->add_one_par($param->requesterAgencyId, 'requesterid', $ret);
        $ret = $this->add_one_par($param->responderAgencyId, 'responderid', $ret);
        $ret = $this->add_common_pars($param, $ret);
        break;
      case 'findNonAutomatedOrders':
        $ret = 'autoforwardreason:non_automated';
        $ret = $this->add_one_par($param->requesterAgencyId, 'requesterid', $ret);
        $ret = $this->add_one_par($param->responderAgencyId, 'responderid', $ret);
        $ret = $this->add_common_pars($param, $ret);
        break;
      case 'findOrderType':
        $ret = $this->add_one_par($param->articleDirect, 'articledirect', $ret);
        $ret = $this->add_one_par($param->kvik, 'kvik', $ret);
        $ret = $this->add_one_par($param->norfri, 'norfri', $ret);
        $ret = $this->add_one_par($param->requesterAgencyId, 'requesterid', $ret);
        $ret = $this->add_one_par($param->responderAgencyId, 'responderid', $ret);
        $ret = $this->add_common_pars($param, $ret);
        break;
      case 'getOrderStatus':
        $ret = $this->add_one_par($param->orderId, 'orderid', $ret);
        $ret = $this->add_one_par($param->requesterAgencyId, 'requesterid', $ret);
        $ret = $this->add_one_par($param->responderAgencyId, 'responderid', $ret);
        $ret = $this->add_common_pars($param, $ret);
        break;
      case 'findOpenIllOrders':
        $ret = 'ordertype:inter_library_request';
        $ret .= ' AND -provideranswer:*';
        if ($param->requesterAgencyId) {
          $ret .= ' AND -requesterorderstate:finished';
        }
        if ($param->responderAgencyId) {
          $ret .= ' AND -providerorderstate:finished';
        }
        $ret = $this->add_one_par($param->requesterAgencyId, 'requesterid', $ret);
        $ret = $this->add_one_par($param->responderAgencyId, 'responderid', $ret);
        $ret = $this->add_common_pars($param, $ret);
        break;
      case 'findClosedIllOrders':
        $ret = 'ordertype:inter_library_request';
        if ($param->orderStatus->_value == 'shipped') {
          $ret .= ' AND isshipped:Y';
        }
        elseif ($param->orderStatus->_value) {
          $ret .= ' AND provideranswer:' . $param->orderStatus->_value;
        }
        else {
          $ret .= ' AND provideranswer:*';
        }
        if ($param->requesterAgencyId) {
          $ret .= ' AND -requesterorderstate:finished';
        }
        if ($param->responderAgencyId) {
          $ret .= ' AND -providerorderstate:finished';
        }
        $ret = $this->add_one_par($param->requesterAgencyId, 'requesterid', $ret);
        $ret = $this->add_one_par($param->responderAgencyId, 'responderid', $ret);
        $ret = $this->add_common_pars($param, $ret);
        break;
      case 'findLocalizedEndUserOrders':
        $ret = 'ordertype:enduser_request';
        $ret .= ' AND closed:' . ($this->xs_boolean($param->closed->_value) ? 'Y' : 'N');
        $ret = $this->add_one_par($param->requesterAgencyId, 'requesterid', $ret);
        $ret = $this->add_one_par($param->responderAgencyId, 'responderid', $ret);
        $ret = $this->add_common_pars($param, $ret);
        break;
      case 'findNonLocalizedEndUserOrders':
        $ret = 'ordertype:enduser_illrequest';
        $ret .= ' AND closed:' . ($this->xs_boolean($param->closed->_value) ? 'Y' : 'N');
        $ret = $this->add_one_par($param->requesterAgencyId, 'requesterid', $ret);
        $ret = $this->add_one_par($param->responderAgencyId, 'responderid', $ret);
        $ret = $this->add_common_pars($param, $ret);
        break;
      default:
        die('no or wrong action');
        break;
    }
    if (! $ret) {
      self::$error = 'query could not be set for ' . $this->action;
      return FALSE;
    }

    return $ret;
  }

  /**\brief
   * handles general parms: agency, orderSystem, fromDate, toDate
   */
  private function add_common_pars($param, $ret = '') {
    // dropped orderSyetm in request since it's not used
    //$ret = $this->add_one_par($param->orderSystem, 'ordersystem', $ret);  

    // solr intervals as
    // creationdate:[2012-03-06T00:00:00Z TO 2076-03-06T23:59:59Z]
    // creationdate:[2012-03-06T00:00:00Z TO *]
    // creationdate:[* TO 2012-03-06T00:00:00Z]
    if ($param->fromDate->_value || $param->toDate->_value) {
      $from = $to = '*';
      if ($param->fromDate->_value) {
        $from = date('c', strtotime($param->fromDate->_value)) . 'Z';
      }
      if ($param->toDate->_value) {
        $to = date('c', strtotime($param->toDate->_value . '+23 hours 59 minutes 59 seconds')) . 'Z';
      }
      if ($ret) $ret .= ' AND ';
      $ret .= 'creationdate:[' . $from . ' TO ' . $to . ']';
    }

    return $ret;
  }

  /**\brief
   * handles one parameter 
   */
  private function add_one_par($par, $search_field, $ret = '', $op = 'AND') {
    if (is_array($par)) {
      foreach ($par as $val) {
        if ($val->_value)
          $help .= ($help ? ' OR ' : '') . $this->norm_agency($val->_value, $search_field);
      }
      if ($help)
        $ret .= ($ret ? " $op " : '') . $search_field . ':(' . $help . ')';
    }
    else {
      if ($par->_value)
        $ret .= ($ret ? " $op " : '') . $search_field . ':' . $this->norm_agency($par->_value, $search_field);
    }
    return $ret;
  }

  /** \brief
   *  return normalized agency for selected fields
   */
  private function norm_agency($agency, $field) {
    if ($field == 'requesterid' || $field == 'responderid')
      $agency = $this->strip_agency($agency);
    return $agency;
  }

  /** \brief
   *  return true if xs:boolean is so
   */
  private function xs_boolean($str) {
    return (strtolower($str) == 'true' || $str == 1);
  }

  /** \brief
   *  return only digits, so something like DK-710100 returns 710100
   */
  private function strip_agency($id) {
    return preg_replace('/\D/', '', $id);
  }

}

/**\brief
 * Class to handle connection to database and correlation to xml-schema
 */
class OFO_database {
  public static $error;
  public static $numrows;
  public static $pure_sql;
  public static $vip_connect;

  private $xmlfields = array();
  private $action;
  private $fields = array();
  private $sql;
  private $connectionstring;


  /**\brief
   * Constructor.
   */
  public function __construct($action, $config) {
    self::$error = null;
    $this->action = $action;

    // set connectionstring from config
    if (!$this->connectionstring = $config->get_value('connectionstring', 'setup'))
      die('no database credentials in config-file');

    if (!self::$vip_connect = $config->get_value('vip', 'setup'))
      die(' no credentials for vip-base ');

    // set actions
    $arr = $config->get_value('action');
    if (empty($arr))
      die('no actions set in config-file');
    foreach ($arr[$action] as $key =>$val) {
      $this->fields[] = $val;
    }

    $this->set_base_sql($config);

    // get xml schema
    $schemafile = $config->get_value('schema', 'setup');
    if (!file_exists($schemafile))
      die('xsd not found: ' . $schemafile);

    $schema = new xml_schema();
    $schema->get_from_file($schemafile);
    // set xml-fields
    if ($this->action == 'getOrderStatus')
      $this->xmlfields = $schema->get_sequence_array('getOrderStatusResponse');
    else
      $this->xmlfields = $schema->get_sequence_array('order');

  }

  private function set_base_sql($config) {
    $this->sql = 'SELECT ';

    // get fields to select from ini-file
    $sqlarr = $config->get_section('ors_order');
    if (empty($sqlarr))
      die('no table definition in config-file');

    foreach ($sqlarr as $key => $val) {
      if ($val)
        $this->sql .= $val . "\n,";
      else
        $this->sql .= $key . "\n,";
    }
    // remove trailing ','
    $this->sql = substr($this->sql, 0, -1);

    // insert join on ors_order_index here if operations are bibliographic search or OrdersFromUser

    $this->sql .= " FROM ors_order o INNER JOIN ors_order_index oi ON \n
                  (oi.requesterid = o.requesterid AND oi.orderid = o.orderid)\n";

    //$this->sql .= ' FROM ors_order o WHERE ';

  }




  /**\brief
   * Get orders from database.
   * @param; request parameters as xml-object
   * return; array of found orders
   */
  public function findOrders($param) {
    if (!$oci = $this->execute($param))
      return FALSE;

    // TODO - this line is for test-purpose only. Remove in production
    self::$pure_sql = $oci->pure_sql();

    $resultPosition = 1;
    while ($data = $oci->fetch_into_assoc()) {
      if ($order = $this->get_order($data, $resultPosition)) {
        $orders[] = $order;
        $resultPosition++;
      }
    }

    return $orders;
  }

  private function count($param) {
    $oci1 = new oci($this->connectionstring);

    if (!$clause = $this->set_sql($param, $oci1))
      return FALSE;

    $sql = 'SELECT COUNT(*) count FROM(' . $this->sql . $clause . ')';

    try {
      $oci1->connect();
    }
    catch (ociException $e) {
      self::$error = 'could not connect to db';
      return FALSE;
    }

    try {
      $oci1->set_query($sql);
    }
    catch (ociException $e) {
      //	self::$error = 'query could not be set';
      self::$error = $e->__toString();
      //	die('TSTHEST');
      return FALSE;
    }


    try {
      $row = $oci1->fetch_into_assoc();
    }
    catch (ociException $e) {
      self::$error = $e->__toString();
      return FALSE;
    }

    self::$numrows = $row['COUNT'];

    $oci1->disconnect();
  }

  /**\brief
   * Handle one order.
   * @data; a row of data from database
   * @resultPosition; rownumber of result
   * return; one order as xml-object
   */
  private function get_order(&$data, $resultPosition) {
    $ret->_namespace = THIS_NAMESPACE;

    $ret->_value->resultPosition->_value = $resultPosition;;
    $ret->_value->resultPosition->_namespace = THIS_NAMESPACE;

    // column-names from database MUST match xml-fields for this loop to work
    // new loop to ensure roworder as defined in xml-schema
    foreach ($this->xmlfields as $key =>$val) {
      if ($value =  $data[$val]) {
        if ($value && $value != '0001-01-01' && $value != 'uninitialized') {
          if ($key  !=  'placeOnHold') {
            if ($value == 'yes')
              $value = 'true';
            if ($value == 'no')
              $value = 'false';
            if ($value == 'N')
              $value = 'false';
            if ($value == 'Y')
              $value = 'true';
          }
          if ($key == 'creationDate') {
            $value = str_replace(' ', 'T', $value);
            $value .= 'Z';
          }
          $ret->_value->$key->_value = utf8_encode($value);
          $ret->_value->$key->_namespace = THIS_NAMESPACE;
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
  private function execute($param) {
    try {
      $oci = new oci($this->connectionstring);
    }
    catch (ociException $e) {
      self::$error = 'could not connect to db: ' . $e->__toString() . "\n";
      return FALSE;
    }

    $step = $param->stepValue->_value;
    $start = $param->start->_value;


    /* try{$clause = $this->set_sql($param, $oci);}
    catch(ociException $e){
      self::$error = 'could not set sql: ' . $e->__toString() . "\n";
      return FALSE;}*/

    if (!$clause = $this->set_sql($param, $oci))
      return FALSE;

    $sql = $this->sql . $clause;

    //echo $sql;
    //exit;

    $this->count($param);

    if (($step || $step === 0) && ($start || $start === 0))
      $oci->set_pagination($start,($start+$step)-1);


    try {
      $oci->connect();
    }
    catch (ociException $e) {
      self::$error = 'could not connect to db: ' . $e->__toString() . "\n";
      return FALSE;
    }

    try {
      $oci->set_query($sql);
    }
    catch (ociException $e) {
      self::$error = 'could not set query: ' . $e->__toString() . "\n";
      return FALSE;
    }


    /*echo $oci->pure_sql();
      print_r($oci->bind_backup);
      exit;*/

    /* if (!@$oci->set_query($sql))
      {
    	self::$error = 'query could not be set';
    	return FALSE;
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
  private function set_sql($param, $oci) {
    switch ($this->action) {
      case "findManuallyFinishedIllOrders":
        $ret = OFO_sql::findManuallyFinishedIllOrders($param, $oci);
        break;
      case 'findAllOpenEndUserOrders':
        $ret = OFO_sql::findAllOpenEndUserOrders($param, $oci);
        break;
      case 'findAllOrders':
        $ret =  OFO_sql::findAllOrders($param, $oci);
        break;
      case 'findAllIllOrders':
        $ret =  OFO_sql::findAllIllOrders($param, $oci);
        break;
      case 'findAllNonIllOrders':
        $ret =  OFO_sql::findAllNonIllOrders($param, $oci);
        break;
      case 'findSpecificOrder':
        $ret =  OFO_sql::findSpecificOrder($param, $oci);
        break;
      case 'findOrdersFromUser':
        $ret =  OFO_sql::findOrdersFromUser($param, $oci);
        break;
      case 'findOrdersFromUnknownUser':
        $ret =  OFO_sql::findOrdersFromUnknownUser($param, $oci);
        break;
      case 'bibliographicSearch':
        $ret =  OFO_sql::bibliographicSearch($param, $oci);
        break;
      case 'findOrdersWithStatus':
        $ret = OFO_sql::findOrdersWithStatus($param, $oci);
        break;
      case 'findOrdersWithAutoForwardReason':
        $ret = OFO_sql::findOrdersWithAutoForwardReason($param, $oci);
        break;
      case 'findAutomatedOrders':
        $ret = OFO_sql::findAutomatedOrders($param, $oci);
        break;
      case 'findNonAutomatedOrders':
        $ret = OFO_sql::findNonAutomatedOrders($param, $oci);
        break;
      case 'findOrderType':
        $ret = OFO_sql::findOrderType($param, $oci);
        break;
      case 'getOrderStatus':
        $ret = OFO_sql::getOrderStatus($param, $oci);
        break;
      case 'findOpenIllOrders':
        $ret = OFO_sql::findOpenIllOrders($param, $oci);
        break;
      case 'findClosedIllOrders':
        $ret = OFO_sql::findClosedIllOrders($param, $oci);
        break;
      case 'findLocalizedEndUserOrders':
        $ret = OFO_sql::findLocalizedEndUserOrders($param, $oci);
        break;
      case 'findNonLocalizedEndUserOrders':
        $ret = OFO_sql::findNonLocalizedEndUserOrders($param, $oci);
        break;
      default:
        //$ret =  'SELECT * FROM ORS_ORDER WHERE REQUESTERID = 716700';
        die('no or wrong action');
        break;
    }
    if (! $ret) {
      self::$error = 'sql could not be set for ' . $this->action;
      return FALSE;
    }

    return $ret;
  }




}


/**
\brief
* class to handle lookups in vip-base
*/

class OFO_vip {
  public static function set_libraries($param) {

    if (!$agency = $param->agency->_value)
      return FALSE;


    $libs = OFO_vip::get_library_list($agency);
    // print_r($libs);
    if (empty($libs)) {
      if ($param->requesterAgencyId->_value) {
        $ret = ' AND o.requesterid = ' . $agency;
      }
      elseif ($param->responderAgencyId->_value) {
        $ret .= ' AND responderid = ' . $agency;
      }
    }
    elseif (!empty($libs)) {
      if ($param->requesterAgencyId)
        $ret = ' AND o.requesterid in(';
      //elseif ($param->responderAgencyId)
      else
        $ret = ' AND responderid in(';
      //else
      // return FALSE;

      foreach ($libs as $lib) {
        $ids[] = $lib['BIB_NR'];
      }

      $clause = '';
      foreach ($libs as $lib) {
        if (strlen($clause))
          $clause .= ',';
        $clause .= $lib['BIB_NR'];
      }
      $ret .= $clause;
      $ret .= ")\n";
    }
    else {
      return FALSE;

    }

    return $ret;
  }

  public static function get_library_list($agency) {
    $sql = 'SELECT v.bib_nr 
             FROM vip v INNER JOIN vip_vsn vs ON v.kmd_nr = vs.kmd_nr 
            WHERE vs.bib_nr = '.$agency;
    $oci = new oci(OFO_database::$vip_connect);

    try {
      $oci->connect();
    }
    catch (ociException $e) {
      OFO_database::$error = 'could not connect to db ' . OFO_database::$vip_connect;
      return FALSE;
    }

    try {
      $oci->set_query($sql);
    }
    catch (ociException $e) {
      OFO_database::$error = 'query could not be set';
      return FALSE;
    }

    try {
      $ret =  $oci->fetch_all_into_assoc();
    }
    catch (ociException $e) {
      die($e->__toString());
    }



    return $ret;
  }



}
/** \brief
    Class to handle sql for each of the methods in webservice
 */
class OFO_sql {
  public static function get_select() {
    // return 'SELECT * FROM ORS_ORDER WHERE ';
    return '';
  }

  public static function findManuallyFinishedIllOrders($params, $oci) {
    $sql = self::get_select();

    if ($add = self::set_ids($params, $ids) )
      $sql .= $add;
    else
      return FALSE;

    $sql .= self::bind_array($ids, $oci);

    // make sure these are ill-orders
    $oci->bind('ordertype_bind', 'inter_library_request');
    $sql .= "and ordertype=:ordertype_bind\n";

    //TODO implement rest
    $oci->bind('orderstate_bind', 'finished');
    if (isset($params->requesterOrderState->_value))
      $sql .= "and (requesterorderstate=:orderstate_bind OR requesterorderstate IS NOT NULL)\n";
    elseif (isset($params->providerOrderState->_value))
      $sql .= "and (providerorderstate=:orderstate_bind OR providerorderstate IS NOT NULL)\n";

    $add = self::setRequestGeneral($params, $oci);

    if ($add !== FALSE)
      $sql .= $add;
    else
      return FALSE;

    return $sql;
  }


  public static function findAllOpenEndUserOrders($params, $oci) {
    $sql = self::get_select();

    if ($add = self::set_ids($params, $ids))
      $sql .= $add;
    else
      return FALSE;

    $sql .= self::bind_array($ids, $oci);

    $oci->bind('ordertype_bind', 'enduser_request');
    $oci->bind('ordertype1_bind', 'enduser_illrequest');

    $sql .= "and (ordertype = :ordertype_bind OR ordertype = :ordertype1_bind)\n";

    $oci->bind('closed_bind', 'N');
    $sql .= 'and closed = :closed_bind';

    $add = self::setRequestGeneral($params, $oci);

    if ($add !==  FALSE)
      $sql .= $add;
    else
      return FALSE;

    return $sql;
  }

  public static function findAllIllOrders($params, $oci) {
    $sql = self::get_select();

    if ($add = self::set_ids($params, $ids))
      $sql .= $add;
    else
      return FALSE;

    $sql .= self::bind_array($ids, $oci);

    $oci->bind('ordertype_bind', 'inter_library_request');
    $sql .= "and ordertype = :ordertype_bind\n";

    $add = self::setRequestGeneral($params, $oci);

    if ($add !==  FALSE)
      $sql .= $add;
    else
      return FALSE;

    return $sql;

  }

  public static function findAllNonIllOrders($params, $oci) {
    $sql = self::get_select();

    if ($add = self::set_ids($params, $ids))
      $sql .= $add;
    else
      return FALSE;

    $sql .= self::bind_array($ids, $oci);

    $oci->bind('enduser_request_bind', 'enduser_request');
    $oci->bind('enduser_illrequest_bind', 'enduser_illrequest');
    $sql .= "and (ordertype = :enduser_request_bind OR ordertype = :enduser_illrequest_bind)\n";

    $add = self::setRequestGeneral($params, $oci);

    if ($add !==  FALSE)
      $sql .= $add;
    else
      return FALSE;

    return $sql;
  }

  public static function findNonLocalizedEndUserOrders($params, $oci) {
    // TDOO filter on some field in ors_order (orderstatus)
    $sql = self::get_select();

    if ($add = self::set_ids($params, $ids))
      $sql .= $add;
    else
      return FALSE;

    $sql .= self::bind_array($ids, $oci);

    if (isset($params->closed->_value)) {
      $oci->bind('ordertype_bind', 'enduser_illrequest');
      $sql .= "and ordertype = :ordertype_bind\n";

      $close = $params->closed->_value;
      if ($close == 'true' || $close == 1)
        $oci->bind('closed_bind', 'Y');
      else
        $oci->bind('closed_bind', 'N');

      $sql .= "and closed = :closed_bind\n";
    }
    else
      return FALSE;

    $add = self::setRequestGeneral($params, $oci);
    if ($add !==  FALSE)
      $sql .= $add;
    else
      return FALSE;

    return $sql;
  }

  public static function findLocalizedEndUserOrders($params, $oci) {
    $sql = self::get_select();

    if ($add = self::set_ids($params, $ids))
      $sql .= $add;
    else
      return FALSE;

    $sql .= self::bind_array($ids, $oci);

    /*  // required field; closed
    if ($close = $params->closed->_value || ($close = $params->closed->_value) == 0)
      {
    	if ($close == 'true')
    $oci->bind('closed_bind', 'Y');
    	else
    $oci->bind('closed_bind', 'N');

    	$sql .= "and closed = :closed_bind\n";
    	}*/
    //required field; closed
    /*if (isset($params->closed->_value))
      {
    	$close = $params->closed->_value;
    	if ($close == 'true' || $close == 1)
    $oci->bind('closed_bind', 'Y');
    	else
    $oci->bind('closed_bind', 'N');

    	$sql .= "and closed = :closed_bind\n";

    	}*/
    if (isset($params->closed->_value)) {
      $oci->bind('ordertype_bind', 'enduser_request');
      $sql .= "and ordertype = :ordertype_bind\n";

      $close = $params->closed->_value;
      if ($close == 'true' || $close == 1)
        $oci->bind('closed_bind', 'Y');
      else
        $oci->bind('closed_bind', 'N');

      $sql .= "and closed = :closed_bind\n";
    }
    else
      return FALSE;

    $add = self::setRequestGeneral($params, $oci);
    if ($add !==  FALSE)
      $sql .= $add;
    else
      return FALSE;

    return $sql;
  }

  public static function findClosedIllOrders($params, $oci) {
    // TDOO filter on some field in ors_order (orderstatus)
    $sql = self::get_select();

    if ($add = self::set_ids($params, $ids))
      $sql .= $add;
    else
      return FALSE;

    $sql .= self::bind_array($ids, $oci);

    $oci->bind('ordertype_bind', 'inter_library_request');
    $sql .= "and ordertype = :ordertype_bind\n";

    // required field; orderStatus
    if ($status = $params->orderStatus->_value) {
      if ($status == 'shipped')
        $sql .= "and isshipped = 'Y'\n";
      else {
        $oci->bind('orderStatus_bind', $status);
        $sql .= "and provideranswer = :orderStatus_bind\n";
      }
    }
    else
      $sql .= "AND provideranswer IS NOT NULL\n";


    $add = self::setRequestGeneral($params, $oci);
    if ($add !==  FALSE)
      $sql .= $add;
    else
      return FALSE;

    return $sql;
  }

  public static function findOpenIllOrders($params, $oci) {
    $sql = self::get_select();

    if ($add = self::set_ids($params, $ids))
      $sql .= $add;
    else
      return FALSE;

    $sql .= self::bind_array($ids, $oci);

    $oci->bind('ordertype_bind', 'inter_library_request');
    $sql .= "and ordertype = :ordertype_bind\n";

    $sql .= "and provideranswer IS NULL\n";

    //$sql .= "and isshipped IS NOT NULL\n";

    //    $oci->bind('autoforward_bind', 'automated');
    //$sql .= "and autoforwardresult = :autoforward_bind\n";

    $add = self::setRequestGeneral($params, $oci);
    if ($add !==  FALSE)
      $sql .= $add;
    else
      return FALSE;

    return $sql;

  }

  public static function getOrderStatus($params, $oci) {
    $sql = self::get_select();

    if ($add = self::set_ids($params, $ids))
      $sql .= $add;
    else
      return FALSE;

    $sql .= self::bind_array($ids, $oci);

    // required field; orderId
    if ($orderId = $params->orderId->_value) {
      $oci->bind('orderId_bind', $orderId);
      $sql .= "and orderid = :orderId_bind\n";
    }
    else
      return FALSE;

    $add = self::setRequestGeneral($params, $oci);
    if ($add !==  FALSE)
      $sql .= $add;
    else
      return FALSE;

    return $sql;
  }

  public static function findOrderType($params, $oci) {
    $sql = self::get_select();

    if ($add = self::set_ids($params, $ids))
      $sql .= $add;
    else
      return FALSE;

    $sql .= self::bind_array($ids, $oci);

    // required fields articleDirect OR kvik OR norfri
    if ($articleDirect = $params->articleDirect->_value) {
      $oci->bind('articleDirect_bind', $articleDirect);
      $sql .= "and articledirect = :articleDirect_bind\n";
    }
    elseif ($kvik = $params->kvik->_value) {
      $oci->bind('kvik_bind', $kvik);
      $sql .= "and kvik = :kvik_bind\n";
    }
    elseif ($norfri = $params->norfri->_value) {
      $oci->bind('norfri_bind', $norfri);
      $sql .= "and norfri = :norfri_bind\n";
    }
    else
      return FALSE;

    $add = self::setRequestGeneral($params, $oci);
    if ($add !==  FALSE)
      $sql .= $add;
    else
      return FALSE;

    return $sql;
  }

  public static function findNonAutomatedOrders($params, $oci) {
    $sql = self::get_select();

    if ($add = self::set_ids($params, $ids))
      $sql .= $add;
    else
      return FALSE;

    $sql .= self::bind_array($ids, $oci);


    $add = self::setRequestGeneral($params, $oci);
    if ($add !==  FALSE)
      $sql .= $add;
    else
      return FALSE;

    $oci->bind('auto_bind', 'non_automated');
    $sql .= "AND AUTOFORWARDRESULT = :auto_bind\n";

    return $sql;

  }

  public static function findAutomatedOrders($params, $oci) {
    $sql = self::get_select();

    if ($add = self::set_ids($params, $ids))
      $sql .= $add;
    else
      return FALSE;

    $sql .= self::bind_array($ids, $oci);
    // TODO restrict selection somehow

    $oci->bind('ordertype_bind', 'inter_library_request');
    $sql .= "and ordertype = :ordertype_bind\n";

    $oci->bind('auto_bind', 'automated');
    $sql .= "AND AUTOFORWARDRESULT = :auto_bind\n";

    $add = self::setRequestGeneral($params, $oci);
    if ($add !==  FALSE)
      $sql .= $add;
    else
      return FALSE;

    return $sql;
  }

  public static function findAllOrders($params, $oci) {
    // required fields are requester OR responderAgencyId, agency

    $sql = self::get_select();



    if ($add = self::set_ids($params, $ids))
      $sql .= $add;
    else
      return FALSE;



    $sql .= self::bind_array($ids, $oci);


    // $oci->bind('ordertype_bind', 'inter_library_request');
    //$sql .= "and ordertype = :ordertype_bind\n";

    $add = self::setRequestGeneral($params, $oci);
    if ($add !==  FALSE)
      $sql .= $add;
    else
      return FALSE;


    return $sql;
  }

  public static function findOrdersFromUser($params, $oci) {
    $sql = self::get_select();

    if ($add = self::set_ids($params, $ids))
      $sql .= $add;
    else
      return FALSE;

    $sql .= self::bind_array($ids, $oci);

    // user;required fields: userId OR userMail OR userName (choice)
    if ($userId = $params->userId->_value) {
      $oci->bind('userId_bind', $userId);
      $sql .= "and userid = :userId_bind\n";
    }
    elseif ($userMail = $params->userMail->_value) {
      $oci->bind('userMail_bind', $userMail . '%');
      $sql .= "and usermail like :userMail_bind\n";
    }
    elseif ($userName = $params->userName->_value) {
      $oci->bind('userName_bind', '%' . $userName . '%');
      //$sql .= "and username like :userName_bind\n";
      $sql .= "and contains(oi.username,:userName_bind, 1) > 0\n";
    }
    elseif ($ftext = $params->userFreeText->_value) {
      $oci->bind('ftxt_bind', $ftext . '%');
      $sql .= "and (o.userName like :ftxt_bind OR o.userMail like :ftxt_bind OR o.userId like :ftxt_bind)\n";
    }
    else
      return FALSE;

    $add = self::setRequestGeneral($params, $oci);
    if ($add !==  FALSE)
      $sql .= $add;
    else
      return FALSE;

    return $sql;
  }

  public static function findOrdersFromUnknownUser($params, $oci) {
    $sql = self::get_select();

    if ($add = self::set_ids($params, $ids))
      $sql .= $add;
    else
      return FALSE;

    $sql .= self::bind_array($ids, $oci);

    $oci->bind('userIdAuthenticated_bind', 'no');
    $sql .= 'AND USERIDAUTHENTICATED = :userIdAuthenticated_bind';

    $add = self::setRequestGeneral($params, $oci);
    if ($add !==  FALSE)
      $sql .= $add;
    else
      return FALSE;


    return $sql;
  }

  public static function bibliographicSearch($params, $oci) {
    $sql = self::get_select();

    if ($add = self::set_ids($params, $ids))
      $sql .= $add;
    else
      return FALSE;

    $sql .= self::bind_array($ids, $oci);

    // optional parameters;author,bibliographicRecordId,isbn,issn,mediumType,title,bibliographicFreeText
    if ($author = $params->author->_value) {
      $oci->bind('author_bind', '%' . $author . '%');
      //	echo "'" . $author . "%'";
      //$sql .= " and author like :author_bind\n";
      $sql .= 'and contains(oi.author,:author_bind, 1) > 0';
    }
    if ($bibliographicRecordId = $params->bibliographicRecordId->_value) {
      $oci->bind('bibRec_bind', $bibliographicRecordId);
      $sql .= "and bibliographicrecordid = :bibRec_bind\n";
    }
    if ($isbn = $params->isbn->_value) {
      $oci->bind('isbn_bind', $isbn);
      $sql .= "and isbn = :isbn_bind\n";
    }

    if ($issn = $params->issn->_value) {
      $oci->bind('issn_bind', $issn);
      $sql .= "and issn = :issn_bind\n";
    }

    if ($mediumType = $params->mediumType->_value) {
      $oci->bind('mediumType_bind', $mediumType);
      $sql .= "and mediumtype = :mediumType_bind\n";
    }

    if ($title = $params->title->_value) {
      $oci->bind('title_bind', '%' . $title . '%');
      $sql .= 'and contains(oi.title,:title_bind, 2) > 0';

      //	$sql .= "and title like:title_bind\n";
    }

    if ($ftxt = $params->bibliographicFreeText->_value) {
      $oci->bind('ftext_bind', '%' . $ftxt . '%');

      //$sql .= "and (o.title like :ftext_bind OR o.author like :ftext_bind)\n";
      $sql .= "and ((contains(oi.title,:ftext_bind, 3) > 0) OR (contains(oi.author,:ftext_bind, 4) > 0))\n";
    }

    /* echo $sql;
       exit;*/

    $add = self::setRequestGeneral($params, $oci);
    if ($add !==  FALSE)
      $sql .= $add;
    else
      return FALSE;

    return $sql;
  }

  public static function findSpecificOrder($params, $oci) {
    // required fields requesterAgencyId OR responderAgencyId, orderId, agency
    $sql = self::get_select();

    if ($add = self::set_ids($params, $ids))
      $sql .= $add;
    else
      return FALSE;

    $sql .= self::bind_array($ids, $oci);

    // required field orderId
    if (is_array($params->orderId)) {
      $sql .= ' and o.orderid in(';
      $sql .= self::bind_array($params->orderId, $oci, 'orderId');

      //	echo $sql;
      //exit;
    }
    elseif ($orderId = $params->orderId->_value) {
      $oci->bind('orderId_bind', $orderId);
      $sql .= " and o.orderid = :orderId_bind\n";
    }
    else
      return FALSE;

    if ($orderType = $params->orderType->_value) {
      // request does not correspond to database; - map to correct values
      if ($orderType == 'enduser_order') {
        $mapType1 = 'enduser_illrequest';
        $mapType2 = 'enduser_request';
        $oci->bind('orderType_bind1', $mapType1);
        $oci->bind('orderType_bind2', $mapType2);
        $sql .= " and (o.ordertype = :orderType_bind1 OR o.ordertype = :orderType_bind2)\n";
      }
      elseif ($orderType == 'inter_library_order') {
        $mapType = 'inter_library_request';
        $oci->bind('orderType_bind', $mapType);
        $sql .= " and o.ordertype = :orderType_bind\n";
      }
      else
        return FALSE;
    }

    /*    $add = self::setRequestGeneral($params, $oci);
    if ($add !==  FALSE)
      $sql .= $add;
    else
    return FALSE;*/

    return $sql;
  }

  public static function findOrdersWithStatus($params, $oci) {

  }

  public static function findOrdersWithAutoForwardReason($params, $oci) {
    $sql = self::get_select();

    if ($add = self::set_ids($params, $ids))
      $sql .= $add;
    else
      return FALSE;

    $sql .= self::bind_array($ids, $oci);

    // autoForwardReason ;required field;
    /*
      valid fields
       <xs:enumeration value = "error"/>
       <xs:enumeration value = "new_for_requester"/>
       <xs:enumeration value = "new_for_responder"/>
       <xs:enumeration value = "no_delivery_date"/>
       <xs:enumeration value = "no_provider"/>
       <xs:enumeration value = "not_for_loan"/>
       <xs:enumeration value = "not_on_shelf"/>
       <xs:enumeration value = "not_possible"/>
       <xs:enumeration value = "test"/>
       <xs:enumeration value = "user_date_exceeded"/>
     */

    if ($reason = $params->autoForwardReason->_value) {
      $oci->bind('reason_bind', $reason);
      $sql .= "AND autoforwardreason = :reason_bind\n";
    }
    else
      return FALSE;

    $add = self::setRequestGeneral($params, $oci);
    if ($add !==  FALSE)
      $sql .= $add;
    else
      return FALSE;

    return $sql;
  }

  private static function set_ids($params,&$ids) {
    $sql .= ' WHERE ';
    if ($ids = $params->requesterAgencyId) {
      // $sql .= 'requesterid in(';
      $sql .= 'pickupagencyid IN(';
    } elseif ($ids = $params->responderAgencyId) {
      $sql .= 'responderid IN(';
    } else {
      return FALSE;
    }

    return $sql;
  }

  /**
     Set parameters that are common(general) for (allmost) all requests
   */
  private static function setRequestGeneral($params, $oci) {
    // ordersystem
    if ($orderSystem = $params->orderSystem->_value) {
      $oci->bind('system_bind', $orderSystem);
      $sql .= "AND ordersystem = :system_bind\n";
    }

    // fromDate
    if ($fromDate = $params->fromDate->_value) {
      //if (!$fdate = self::check_date_time($fromDate))
      if (!$fdate = self::check_date($fromDate))
        return FALSE;

      $oci->bind('fromDate_bind', $fdate);
      //	$sql .= " and to_char(creationdate, 'YYYY-MM-DD HH24:MI:SS') > = :fromDate_bind\n";
      //$sql .= " and to_char(creationdate, 'YYYY-MM-DD') > = :fromDate_bind\n";
      // jgn's suggestion to avoid string-comparison
      $sql .= " and creationdate > = to_date(:fromDate_bind, 'YYYY-MM-DD')\n";
    }

    // toDate
    if ($toDate = $params->toDate->_value) {
      //if (!$tdate = self::check_date_time($toDate))
      if (!$tdate = self::check_date($toDate))
        return FALSE;
      $oci->bind('toDate_bind', $tdate . ' 23:59:59');
      //	$sql .= " and to_char(creationdate, 'YYYY-MM-DD HH24:MI:SS') < = :toDate_bind\n";
      //$sql .= " and to_char(creationdate, 'YYYY-MM-DD') < = :toDate_bind\n";
      // jgn's suggestion to avoid string-comparison
      $sql .= " and creationdate< = to_date(:toDate_bind, 'YYYY-MM-DD HH24:MI:SS')\n";
    }

    if ($more = self::set_libs($params, $oci))
      $sql .= $more;
    else
      return FALSE;

    /*    if ($moresql = OFO_vip::set_libraries($params))
      $sql .= $moresql;
    else
      {
    	//	self::$error = 'no libraries found';
    	return FALSE;
    	}  */

    if ($sort = $params->sortKey->_value) {
      if ($sort == 'creationDateAscending')
        $sql .= " ORDER BY creationdate asc\n";
      elseif ($sort  ==  'creationDateDescending')
      $sql .= " ORDER BY creationdate desc\n";
      else
        return FALSE;
    }
    // agency ???


    return $sql;
  }

  private static function set_libs($params, $oci) {

    if (!$agency = $params->agency->_value)
      return FALSE;

    $libs = OFO_vip::get_library_list($params->agency->_value);

    //print_r($libs);


    if (empty($libs)) {
      if ($params->requesterAgencyId)
        $ret .= 'AND o.requesterid = ' . $agency;
      elseif ($params->responderAgencyId)
      $ret .= 'AND responderid = ' . $agency;

      return $ret;
    }
    elseif (!empty($libs)) {
      // make a 'xml_object' from array
      foreach ($libs as $lib) {
        $lib_obj->_value = $lib['BIB_NR'];
        //array_push($lib_ids, $lib['BIB_NR']);
        $lib_ids[] = $lib_obj;
        $lib_obj = null;
      }
    }

    if ( $params->requesterAgencyId)
      $ret .= ' AND o.requesterid in(';
    elseif ($params->responderAgencyId)
    $ret .= 'AND responderid in(';
    else
      return FALSE;

    //  print_r($lib_ids);

    // exit;

    $ret .= self::bind_array($lib_ids, $oci, 'lib');
    return $ret;

  }

  /**
     check if given string can be converted to date;
     returns date('Ymd') or FALSE
   */
  private static function check_date($date) {
    /* if ($time = strtotime($date))
      {
    	$date = date('Ymd H:i', $time);
    	//	echo $date;
    	//exit;
    	return $date;
      }
      return FALSE;*/

    $reg = '/([0-9]{4})-([0-9]{2})-([0-9]{2})/';
    if (preg_match($reg, $date, $matches)) {
      $time = strtotime($matches[0]);
      //	$date = date('Y-m-d H:i:s', $time);
      $date = date('Y-m-d', $time);

      return $date;
    }
    return FALSE;
  }

  //check xml dateTime
  private function check_date_time($dateTime) {
    $reg = '/([0-9]{4})-([0-9]{2})-([0-9]{2})([T]|[ ])([0-1][0-9]|[2][0-3]):([0-5][0-9]):([0-5][0-9])/';
    //$reg = '/([0-9]{4})-([0-9]{2})-([0-9]{2})T([0-1][0-9]|[2][0-3]):([0-5][0-9])/';
    if (preg_match($reg, $dateTime, $matches)) {
      if (strpos($dateTime, 'T'))
        return str_replace('T', ' ', $dateTime);
      return $dateTime;
    }

    return FALSE;
  }

  /**
     Run through given array ($key =>$val). Bind variables to given instance of oci-class.
     Return sql.
   */
  private static function bind_array($ids, $oci, $prefix = '') {
    if (is_array($ids)) {

      $count = 1;
      // make an array
      foreach ($ids as $key =>$val) {
        $idarr[$prefix . 'bind' . $count++] = $val->_value;
      }

      //iterate array; generate sql
      foreach ($idarr as $key =>$val) {
        //$oci->bind($key, $idarr[$key],-1, SQLT_INT);
        $oci->bind($key, $idarr[$key]);
        $sql .= ':' . $key . ',';
      }

      // remove trailing ','
      $sql = substr($sql,0,-1);
      $sql .= ")\n";
    }
    else {
      //$oci->bind($prefix . 'bind_ID', $ids->_value,-1, SQLT_INT);
      $oci->bind($prefix . 'bind_ID', $ids->_value);
      $sql .= ":bind_ID)\n";
    }

    return $sql;
  }
}

class OFO_agency {
  // WHAT IS THIS and how do i authenticate
  public static function authenticate($agency) {
    // return self::error();
    return;
  }

  public static function error() {
    return 'orssearch order service not available';
  }

}


?>

