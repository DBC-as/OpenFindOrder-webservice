<?php
// little program to parse fields from xsd

/*$schema = new xml_schema();
$schema->get_from_file('openfindorder.xsd');

$seq = $schema->get_sequence('order');
foreach ($seq as $element)
  $arr[] = $schema->get_element_attributes($element);

foreach ($arr as $a)
echo $a['name']."=\n";*/
//print_r($arr);

/*foreach($arr as $key => $val)
  // $ret[] = $val['name'];
  echo '"'.$val['name'].'"=>"'.strtoupper($val['name']).'",'."\n";


print_r($ret);
//echo count($arr)."\n";
//print_r($seq);*/


class xml_schema {
  public $xpath;

  public function __construct()
  { }

  public function get_sequence_array($element_name) {
    $seq = $this->get_sequence($element_name);

    foreach ($seq as $element) {
      $arr[] = $this->get_element_attributes($element);
    }

    foreach ($arr as $key => $val) {
      $ret[$val['name']] = strtoupper($val['name']);
    }

    return $ret;
  }

  public function get_from_file($filename) {
    $dom = new DOMDocument();
    if (! @$dom->load($filename))
      die("could not open file: ".$filename."\n" );
    $this->xpath = new DOMXPath($dom);
  }

  public function set_from_xml($xml) {
    $dom = new DOMDocument();
    if (!@$dom->loadXML($xml) )
      die("could not load xml");
    $this->xpath = new DOMXPath($dom);
  }

  public function get_element_attributes($element_name) {
    $element = helpFunc::split($element_name);

    $query = "//*[@name='".$element."']/@*";
    $nodes = $this->xpath->query($query);
    $ret['wsdlname'] = $element_name;
    foreach ($nodes as $node) {
      $ret[$node->nodeName] = $node->nodeValue;
    }

    return $ret;
  }

  public function is_simple_type($element_name) {
    $query = "//*[@name='".$element_name."']/@*";
    $nodes = $this->xpath->query($query);
    foreach ($nodes as $node) {
      if ($node->nodeName == 'type') {
        $type = $node->nodeValue;
        break;
      }
    }

    if (!$type) {
      //check if simpleType is inline
      $query = "//*[@name='".$element_name."']/*[local-name()='simpleType']";
      $nodes = $this->xpath->query($query);
      if ($nodes->length > 0)
        return true;

      return false;
    }

    $typename = helpFunc::split($type);
    $query = "//*[local-name()='simpleType'][@name='".$typename."']";

    $nodes = $this->xpath->query($query);
    if ($nodes->length > 0)
      return true;

    return false;
  }

  public function get_sequence($element_name) {
    $element = helpFunc::split($element_name);

    $query = "//*[@name='".$element."']//*[local-name()='element']/@*";
    $nodes = $this->xpath->query($query);
    foreach ($nodes as $node) {
      if ($node->nodeName == "ref" || $node->nodeName == "type")
        $ret[] = $node->nodeValue;
    }


    return $ret;
  }

  public function namespaces() {
    // get all namespaces
    $query = "//namespace::*";
    $nodelist = $this->xpath->query($query);

    $namespaces = array();
    foreach ($nodelist as $node) {
      // remove 'xlmns:'
      if ($index = strpos($node->nodeName,':'))
        $key = substr($node->nodeName,$index+1);
      else
        $key = $node->nodeName;

      $namespaces[$key] = $node->nodeValue;
    }
    return $namespaces;
  }
}
class helpFunc {
  /** Return true if given path starts with 'http(s)' */
  public static function is_url($path) {
    $elements = parse_url($path);
    //print_r($elements);
    if (strtolower($elements['scheme']) == 'http' ||  strtolower($elements['scheme']) == 'https')
      return true;
    return false;
  }

  public static function split($string) {
    if (strpos($string,':') > 0) {
      $split = explode(':',$string);
      return $split[1];
    }
    return $string;
  }

  public static function get_type($element) {
    if (strpos($element,':') > 0) {
      $split = explode(':',$element);
      return $split[0];
    }
    return false;
  }

  public static function soap_header($namespaces) {
    $ret .= '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope"'."\n";
    foreach ($namespaces as $prefix => $namespace) {
      $ret .= 'xmlns:'.$prefix.'="'.$namespace."\"\n";
    }
    // remove last \n character
    $ret = substr($ret,0,-1);
    $ret .= '>'."\n".'<SOAP-ENV:Body>'."\n";

    return $ret;
  }

  public static function soap_footer() {
    $ret .= '</SOAP-ENV:Body>'."\n";
    $ret .= '</SOAP-ENV:Envelope>'."\n";


    return $ret;
  }
}

?>
