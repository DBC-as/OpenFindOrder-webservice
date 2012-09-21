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
  private function findOrderResponse($orders, $number_of_orders = 0) {
    $response->findOrdersResponse->_namespace = THIS_NAMESPACE;

    if ($orders === FALSE) {
      return $this->send_error('no orders found');
    }

    // empty result-set
    if (empty($orders))
      return $this->send_error('no orders found');

    $result = &$response->findOrdersResponse->_value->result;
    $result->_namespace = THIS_NAMESPACE;
    $result->_value->numberOfOrders->_namespace = THIS_NAMESPACE;
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
      $value = $data[strtolower($val)];
      if (is_array($value))
        $value = $value[0];
      if ($value && $value != '0001-01-01' && $value != 'uninitialized') {
        if ($key !=  'placeOnHold') {
          if ($value == 'yes' || $value == 'Y')
            $value = 'true';
          if ($value == 'no' || $value == 'N')
            $value = 'false';
        }
        if ($key == 'creationDate') {
          $value = str_replace(' ', 'T', $value);
          if (!strpos($value, 'Z')) $value .= 'Z';
        }
        $ret->_value->$key->_value = $value;
        $ret->_value->$key->_namespace = THIS_NAMESPACE;
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
          $ret .= 'AND requesterorderstate:' . $param->requesterOrderState->_value;
        } 
        elseif (isset($param->providerOrderState->_value)) {
          $ret .= 'AND providerorderstate:' . $param->providerOrderState->_value;
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
        $ret = $this->add_one_par($param->userName, 'username', $ret);
        $ret = $this->add_one_par($param->requesterAgencyId, 'requesterid', $ret);
        $ret = $this->add_one_par($param->responderAgencyId, 'responderid', $ret);
        $ret = $this->add_common_pars($param, $ret);
// Spooky ... this OR-part below has to be the last and no () around it ????
// apparently the edismax searchHandler parse (a OR b OR c) as some list where all members should be present
// Users are therefore encouraged/recommended to user userId, userMail and userName instead of userFreeText
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
          $help .= ($help ? ' OR ' : '') . $this->normalize($val->_value, $search_field);
      }
      if ($help)
        $ret .= ($ret ? " $op " : '') . $search_field . ':(' . $help . ')';
    }
    else {
      if ($par->_value)
        $ret .= ($ret ? " $op " : '') . $search_field . ':' . $this->normalize($par->_value, $search_field);
    }
    return $ret;
  }

  /** \brief
   *  return normalized agency for selected fields and escape solr meta-chars
   */
  private function normalize($agency, $field) {
    static $solr_e_from = array('+', '-', ':', '!');
    static $solr_e_to = array();
    if (empty($solr_e_to)) {
      foreach ($solr_e_from as $ch) $solr_e_to[] = '\\' . $ch;
    }
    if ($field == 'requesterid' || $field == 'responderid')
      $agency = $this->strip_agency($agency);
    return str_replace($solr_e_from, $solr_e_to, $agency);
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

