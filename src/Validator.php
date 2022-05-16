<?php
namespace TymFrontiers;
class Validator{

  public $errors = [];
  # How error is pushed to this array..
  # key => [ // key can be method name/instannce where error occured
  #   [
  #     int view_rank, // who can see this error. 0 = anyone, 1 >= author, 2 >= editor, 3 >= manager, 4 >= admin, 5 super-admin
  #     int error_type, // php error type
  #     string error_message, // Error itself
  #     string file, // where error ocured. e.g __FILE__
  #     string line // where error ocured. e.g __LINE__
  #   ]
  # ]
  public $validate_type = [
    "name" => "Human name",
    "username" => "Unique ID or code",
    "option" => "Set of option(s)",
    "text" => "Plain text",
    "ip" => "IP: Internet Protocol",
    "html" => "HTML text script",
    "markdown" => "Markdown script (Plain text)",
    "mixed" => "Mixed value",
    "script" => "Variable script",
    "pattern" => "Regular expression/pattern",
    "email" => "Email address",
    "tel" => "Phone number (including country code)",
    "url" => "URL/URI link",
    "password" => "Strong password",
    "boolean" => "Boolean value",
    "date" => "Given date",
    "time" => "Given time",
    "datetime" => "Date and time",
    "int" => "Integer value",
    "float" => "Floating point/decimal value"
  ];

  public function validate($val,array $options){
    if( \count($options) < 2 ){
      $this->errors['validator'][] = [3,256,"[{$options[0]}]: Validation options must be array with minimum of 2 elements."];
      return false;
    }
    $type = \strtolower($options[1]);
    switch($type){
      case "boolean"  : return $this->boolean($val,$options);   break;
      case "password" : return $this->password($val,$options);  break;
      case "name"     : return $this->name($val,$options);      break;
      case "username" : return $this->username($val,$options);  break;
      case "tel"      : return $this->tel($val,$options);       break;
      case "email"    : return $this->email($val,$options);     break;
      case "date"     : return $this->date($val,$options);      break;
      case "datetym"  : return $this->datetym($val,$options);   break;
      case "tym"      : return $this->tym($val,$options);       break;
      case "option"   : return $this->option($val,$options);    break;
      case "text"     : return $this->text($val,$options);      break;
      case "html"     : return $this->html($val,$options);      break;
      case "script"   : return $this->script($val,$options);    break;
      case "ip"       : return $this->ip($val,$options);        break;
      case "url"      : return $this->url($val,$options);       break;
      case "int"      : return $this->int($val,$options);       break;
      case "float"    : return $this->float($val,$options);     break;
      case "pattern"  : return $this->pattern($val,$options);   break;
      default: return false;
    }
  }

  public function name($val,array $opt){
    // options
    // 0: string fieldname
    // 1: string validation option
    // 2: array/string restriction
    if( \count($opt) < 2 ){
      $this->errors['name'][] = [3,256,"[name]: Validation options must be array with minimum of 2 elements.",__FILE__,__LINE__];
      return false;
    }

    $val = \trim($val);
    $match = \preg_match("/^[a-zA-Z'-]+$/", trim($val)) ? true : false;
    $restricted_names = [];
    $res_xtra = !empty($opt[2]) ? ( !\is_array($opt[2]) ? \explode(",",$opt[2]) : $opt[2] ) : null;
    if( !empty($res_xtra) ){
      for($i=0;$i < \count($res_xtra);++$i){  $restricted_names[] = $res_xtra[$i];   }
    }
    $msg = '';
    if( !$match ){
      $msg .= "[{$opt[0]}]: does not meet expectation, provide a single name.";
    } if( !empty( $restricted_names ) ){
      $has_ban = false;
      foreach ($restricted_names as $res) {
        if( \stripos($val, $res) !== false ) $has_ban = true;
      }
      if( $has_ban ) $msg .= "[{$opt[0]}]: contains restricted value/string. If you must have chosen name, please contact Admin.";
    } if( \strlen($val) <2 || \strlen($val) >35){
      $msg .= "[{$opt[0]}]: has invalid character length for a human name.";
    }
    if(!empty($msg)){
      $this->errors['name'][] = [0,256,$msg,__FILE__,__LINE__];
      return false;
    }else{ return \ucfirst($val); }
  }
  public function username(string $val,array $opt){
    // options
    // 0: string fieldname
    // 1: string validation option
    // 2: int minlength
    // 3: int maxlength
    // 4: array/string restriction
    // 5: case: LOWERCASE,UPPERCASE = UPPERCASE (default)
    // 6: special characters: array
    if( \count($opt) < 4 ){
      $this->errors['username'][] = [3,256,"[username]: Validation options must be array with minimum of 3 elements.",__FILE__,__LINE__];
      return false;
    }

    $val = \trim($val);
    $regex = "/^[a-zA-Z0-9";
    if( !empty($opt[6]) && \is_array($opt[6]) ){
      foreach($opt[6] as $char){
        $regex .= "\\{$char}";
      }
    }
    $regex .= "]+$/";
    $match = \preg_match($regex, \trim($val)) ? true : false;
    $restricted_names = [];
    $res_xtra = !empty($opt[4]) ? ( !\is_array($opt[4]) ? \explode(",",$opt[4]) : $opt[4] ) : null;
    if( !empty($res_xtra) ){
      for($i=0;$i < \count($res_xtra);++$i){  $restricted_names[] = $res_xtra[$i];   }
    }
    $msg = '';
    if( !$match ){
      $pre_msg = "[{$opt[0]}]: does not meet expectation, provide alphanumric character set";
      if( !empty($opt[6]) ){
        $pre_msg .= ", accepted special characters include: ";
        $pre_msg .= \implode(",",$opt[6]);
      }
      $pre_msg .= ". \r\n";
      $msg .= $pre_msg;
    } if( !empty( $restricted_names ) ){
      $has_ban = false;
      foreach ($restricted_names as $res) {
        if( \stripos($val, $res) !== false ) $has_ban = true;
      }
      if( $has_ban ) $msg .= "[{$opt[0]}]: contains restricted value/string. If you must have chosen username, please contact Admin.";
    } if( \strlen($val) < $opt[2] || \strlen($val) > $opt[3] ){
      $msg .= "[{$opt[0]}]: has invalid character length for a unique name.\r\n ";
    }
    if(!empty($msg)){
      $this->errors['username'][] = [0,256,$msg,__FILE__,__LINE__];
      return false;
    }else{
      $cast = !empty($opt[5]) ? \strtoupper($opt[5]) : "UPPERCASE";
      return \in_array($cast,["UPPERCASE","UPPER"])
              ? \strtoupper($val)
              : (
                \in_array($cast,["LOWERCASE","LOWER"])
                  ? \strtolower($val)
                  : $val
              );
    }
  }
  public function option($val,array $opt){
    $val = \trim($val);
    $array = \is_array($opt[2]) ? $opt[2] : \explode(',',$opt[2]);
    if(! \in_array($val,$array,true)){
      $this->errors['option'][] = [0,256,"[{$val}]: does not fall in accepted options such as ".\implode(", ",$array),__FILE__,__LINE__];
      return false;
    }
    return $val;
  }
  public function email($email,$opt=''){
    $email = \trim($email);
    if( !$email = \filter_var($email, FILTER_VALIDATE_EMAIL) ){
      $this->errors['email'][] = [0,256,"[{$opt[0]}]: isn't a valid receiving email.",__FILE__,__LINE__];
      return false;
    }
    return $email;
  }
  public function text($text, array $opt=[]){
      $text = \trim($text);
      $text = \filter_var($text, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
      $min = (int)$opt[2]; $max = (int)$opt[3];
      $errors='';
      if(!$text){
        $errors .= "[{$opt[0]}]: Invalid format for expected plain text.\r\n";
      } if( $min > 0 && \strlen($text) < $min ){
        $errors .= "[{$opt[0]}]: Input text length is lower than expected minimum of {$min}.\r\n";
      } if( $max > 0 && \strlen($text) > $max){
        $errors .= "[{$opt[0]}]: Input text length is higher than expected maximum of {$max}.\r\n";
      }
      if( !empty($errors) ){
        $this->errors['text'][] = [0,256,$errors,__FILE__,__LINE__];
        return false;
      }
      return $text;
  }
  public function html($html, array $opt=[]){
    $is_valid = $this->isHTML($html);
    $min = (int)$opt[2]; $max = (int)$opt[3];
    $errors='';
    if(!$is_valid){
      $errors .= "[{$opt[0]}]: Invalid HTML script.\r\n";
    }
    if( $min > 0 && \strlen($html) < $min ){
      $errors .= "[{$opt[0]}]: Input HTML length is lower than expected minimum of {$min}.\r\n";
    } if( $max > 0 && \strlen($html) > $max){
      $errors .= "[{$opt[0]}]: Input HTML length is higher than expected maximum of {$max}.\r\n";
    }
    if( !empty($errors) ){
      $this->errors['html'][] = [0,256,$errors,__FILE__,__LINE__];
      return false;
    }
    return $html;
  }
  public function markdown($script, array $opt=[]){
    $min = (int)$opt[2]; $max = (int)$opt[3];
    $errors='';
    // if(!$is_valid){
    //   $errors .= "[{$opt[0]}]: Invalid HTML script.\r\n";
    // }
    if( $min > 0 && \strlen($script) < $min ){
      $errors .= "[{$opt[0]}]: Input Script length is lower than expected minimum of {$min}.\r\n";
    } if( $max > 0 && \strlen($script) > $max){
      $errors .= "[{$opt[0]}]: Input Script length is higher than expected maximum of {$max}.\r\n";
    }
    if( !empty($errors) ){
      $this->errors['markdown'][] = [0,256,$errors,__FILE__,__LINE__];
      return false;
    }
    return $script;
  }
  public function mixed($script, array $opt=[]){
    $min = (int)$opt[2]; $max = (int)$opt[3];
    $errors='';
    // if(!$is_valid){
    //   $errors .= "[{$opt[0]}]: Invalid HTML script.\r\n";
    // }
    if( $min > 0 && \strlen($script) < $min ){
      $errors .= "[{$opt[0]}]: Input Script length is lower than expected minimum of {$min}.\r\n";
    } if( $max > 0 && \strlen($script) > $max){
      $errors .= "[{$opt[0]}]: Input Script length is higher than expected maximum of {$max}.\r\n";
    }
    if( !empty($errors) ){
      $this->errors['mixed'][] = [0,256,$errors,__FILE__,__LINE__];
      return false;
    }
    return $script;
  }
  public function script($script, array $opt=[]){
    $min = (int)$opt[2]; $max = (int)$opt[3];
    $errors='';
    if( $min > 0 && \strlen($script) < $min ){
      $errors .= "[{$opt[0]}]: Input script length is lower than expected minimum of {$min}.\r\n";
    } if( $max > 0 && \strlen($script) > $max){
      $errors .= "[{$opt[0]}]: Input script length is higher than expected maximum of {$max}.\r\n";
    }
    if( !empty($errors) ){
      $this->errors['script'][] = [0,256,$errors,__FILE__,__LINE__];
      return false;
    }
    return \filter_var($script,FILTER_SANITIZE_FULL_SPECIAL_CHARS);
  }
  public function tel($tel, array $opt){
    $tel = \str_replace(' ', '', \trim($tel));
    $regex = '^\+[1-9]\d{5,14}$^';
    // return preg_match($regex, $tel) >0 ? trim($tel) : false;
    if( \preg_match($regex, $tel) >0 ){
      return $tel;
    }else{
      $this->errors['tel'][] = [0,256,"[{$opt[0]}]: Invalid telephone number format. A valid one should follow E.164 telephone format e.g [+][country code][subscriber number including area code where applicable.]",__FILE__,__LINE__];
      return false;
    }
  }
  public function url($url, array $opt){
    $url = \trim($url);
    if( $url = \filter_var($url, FILTER_VALIDATE_URL) ){
      return $url;
    }else{
      $this->errors['url'][] = [0,256,"[{$opt[0]}]: is not a valid URL try adding http://, https://, ftp:// etc. as prefix",__FILE__,__LINE__];
      return false;
    }
  }
  public function boolean($bool, array $opt){
    return \filter_var($bool, FILTER_VALIDATE_BOOLEAN) ? (bool)$bool : false;
  }
  public function date($date, array $opt){
    $return = false;
    $str2num = [
      'january'   => 1,
      'february'  => 2,
      'march'     => 3,
      'april'     => 4,
      'may'       => 5,
      'june'      => 6,
      'july'      => 7,
      'august'    => 8,
      'september' => 9,
      'october'   => 10,
      'november'  => 11,
      'december'  => 12,
      'jan'       => 1,
      'feb'       => 2,
      'mar'       => 3,
      'apr'       => 4,
      'jun'       => 6,
      'jul'       => 7,
      'aug'       => 8,
      'sep'       => 9,
      'oct'       => 10,
      'nov'       => 11,
      'dec'       => 12
    ]; // Number representation of long and short month name
    $msg='';
    if (empty($date)) $msg .= "Invalid date string given.\r\n";
    $date = \strtolower(\trim($date));
    $date = \substr_count($date, ',') === 1 ? \str_replace(',','',$date) : $date;
    $date = \str_replace([' of','th','nd','st','rd'],'',$date);
    $date = \str_replace(['/',' ','.','-',',',':','_'],'-',$date);
    $date_split = \explode('-',$date);
    // convert month name to month number
    if( (int)$date_split[0] === 0 ){ @$date_split[0] = $str2num[\strtolower($date_split[0])]; }
    if( (int)$date_split[1] === 0 ){ @$date_split[1] = $str2num[\strtolower($date_split[1])]; }
    // build date with space seperator
    $date = @"{$date_split[0]} $date_split[1] $date_split[2]";
    /*find all number out of date string*/
    \preg_match_all('!\d+!', $date, $matches);
    $date_split = $matches[0]; // new array of date
    // add leading zero if not available;
    @$date_split[0] = \strlen($date_split[0]) === 1 ? "0{$date_split[0]}" : $date_split[0];
    @$date_split[1] = \strlen($date_split[1]) === 1 ? "0{$date_split[1]}" : $date_split[1];
    @$date_split[2] = \strlen($date_split[2]) === 1 ? "0{$date_split[2]}" : $date_split[2];
    $date = "{$date_split[0]}-{$date_split[1]}-{$date_split[2]}";
    if(\strlen(\str_replace('-','',$date)) === 8 ){
      $split = \explode('-',$date);
      $return = \checkdate($split[1],$split[0],$split[2]) ? "{$split[2]}-{$split[1]}-{$split[0]}":false;
      if( \strlen($split[0]) === 4 && \checkdate($split[1],$split[2],$split[0]) ){ // if yr comes first
        $return = "{$split[0]}-{$split[1]}-{$split[2]}";
      }  if( \strlen($split[2]) === 4 && \checkdate($split[0],$split[1],$split[2]) ){ // if yr comes last
        $return = "{$split[2]}-{$split[0]}-{$split[1]}";
      }
    }

    $min_date = !empty($opt[2]) ? \strtotime( $opt[2] ) : false;
    $max_date = !empty($opt[2]) ? \strtotime( $opt[3] ) : false;
    $return_date = $return ? \strtotime($return) : 0;
    if( !$return ){
      $msg .= "[{$opt[0]}]: Invalid date given.\r\n";
    }else{
      if( $min_date && $return_date < $min_date ){
        $msg .= "[{$opt[0]}]: Given date ({$return}) is earlier than expected ({$opt[2]}).\r\n";
      } if( $max_date && $return_date > $max_date ){
        $msg .= "[{$opt[0]}]: Given date ({$return}) is later than expected ({$opt[3]}).\r\n";
      }
    }
    if( empty($msg) ){ return $return; }else{
      $this->errors['date'][] = [0,256,$msg,__FILE__,__LINE__];
      return false;
    }
  }
  public function tym($tym, array $opt){
    // $opt = [
    //   "fieldname", // name of field (same will be used as array_key if error occures)
    //   "type", // field_type = "tym"
    //   "min_tym", // minimum tym e.g 14:25:00 for 2:25 pm
    //   "max_tym" // maximum tym e.g. 21:00:00 for 9:00 pm
    // ];
    \preg_match_all('!\d+!', $tym, $matches);
    //  $new_tym = implode(':',$matches[0]);
    $return = false;
    $tym_split = $matches[0];
    $tym_split[2] = !empty($tym_split[2]) ? $tym_split[2] : 00;
    if( (int)$tym_split[0] < 24 && (int)$tym_split[1] < 60 && (int)$tym_split[2] <60 ){
      foreach($tym_split as $key=>$val){ if( \strlen($tym_split[$key]) === 1 ){ $tym_split[$key] = "0{$tym_split[$key]}"; } }
      if( \substr_count(\strtolower($tym),'pm') >0 ){
        switch($tym_split[0]){
          case '1': $tym_split[0] = '13'; break;
          case '2': $tym_split[0] = '14'; break;
          case '3': $tym_split[0] = '15'; break;
          case '4': $tym_split[0] = '16'; break;
          case '5': $tym_split[0] = '17'; break;
          case '6': $tym_split[0] = '18'; break;
          case '7': $tym_split[0] = '19'; break;
          case '8': $tym_split[0] = '20'; break;
          case '9': $tym_split[0] = '21'; break;
          case '10': $tym_split[0] = '22'; break;
          case '11': $tym_split[0] = '23'; break;
          case '12': $tym_split[0] = '00'; break;

        }
      }
      // var_dump($tym_split);
      switch(\count($tym_split)){
        case 1 : $tym_split[1] = "00"; $tym_split[2] = "00"; $return = \str_replace('::',':',\implode(':',$tym_split)); break;
        case 2 : $tym_split[2] = "00"; $return = \str_replace('::',':',\implode(':',$tym_split)); break;
        case 3 : $return = \str_replace('::',':',\implode(':',$tym_split)); break;
        default : $return = false;
      }
    }
    if( !$return ){ $this->errors[$opt[0]] = "(1). Invalid tym given.\r\n"; return false; }else{
      $msg = '';
      $opt[2] = \count(\explode(':',$opt[2])) >1 ? $opt[2] : "0";
      $opt[3] = \count(\explode(':',$opt[3])) >1 ? $opt[3] : "0";
      $min_tym = \strtotime($opt[2]);
      $max_tym = \strtotime($opt[3]);
      $ret_tym = \strtotime($return);
      // echo "min: ".$min_tym,'<br>',"ret: ".$ret_tym,'<br>';
      if( $min_tym && $ret_tym < $min_tym  ){ $msg .= "[{$opt[0]}]: Given tym ({$return}) is earlier than expected ({$opt[2]})\r\n";  }
      if($max_tym &&  $ret_tym > $max_tym ){ $msg .= "[{$opt[0]}]: Given tym ({$return}) is later than expected ({$opt[3]})\r\n";  }
      if(empty($msg)){
        return $return;
      }else{
        $this->errors['tym'][] = [0,256,$msg,__FILE__,__LINE__];
        return false;
      }
    }
  }
  public function datetym($datetym, array $opt){
    $old_datetym = $datetym;
    $str2num = [
      'january'   => 1,
      'february'  => 2,
      'march'     => 3,
      'april'     => 4,
      'may'       => 5,
      'june'      => 6,
      'july'      => 7,
      'august'    => 8,
      'september' => 9,
      'october'   => 10,
      'november'  => 11,
      'december'  => 12,
      'jan'       => 1,
      'feb'       => 2,
      'mar'       => 3,
      'apr'       => 4,
      'jun'       => 6,
      'jul'       => 7,
      'aug'       => 8,
      'sep'       => 9,
      'oct'       => 10,
      'nov'       => 11,
      'dec'       => 12
    ]; // Number representation of long and short month name
    $datetym = substr_count($datetym, ',') === 1 ? str_replace(',','',$datetym) : $datetym;
    $datetym = strtolower(trim($datetym));
    $datetym = str_replace([' of','th','nd','st','rd'],'',$datetym);
    $datetym = str_replace(['/',' ','.','-',',',':','_'],'-',$datetym);
    $datetym_split = explode('-',$datetym);
    // convert month name to month number
    if( (int)$datetym_split[0] === 0 ){ @$datetym_split[0] = $str2num[strtolower($datetym_split[0])]; }
    if( @(int)$datetym_split[1] === 0 ){ @$datetym_split[1] = $str2num[strtolower($datetym_split[1])]; }
    $datetym = implode(' ',$datetym_split);
    preg_match_all('!\d+!', $datetym, $matches);
    $datetym_split = $matches[0];
    $date = []; $tym = [];
    for($i=0;$i<3;$i++){  $date[] = $datetym_split[$i];   }
    for($i=0;$i<count($datetym_split);$i++){ if($i >2){ $tym[] = $datetym_split[$i]; }   }
    $pm = substr_count(strtolower($old_datetym),'pm') >0 ? 'pm' : '';
    return $this->date( implode(' ',$date),$opt ) && $this->tym( implode(' ',$tym).$pm,$opt ) ? $this->date(implode('-',$date),$opt).' '.$this->tym(implode(':',$tym).$pm,$opt ) : false;
  }
  public function ip($IP, array $opt){
    $IP = trim($IP);
    if( !$return = filter_var($IP, FILTER_VALIDATE_IP)) {
      $this->errors['ip'][] = [0,256,"[{$opt[0]}]: Given IP is not valid. Provide valid IPv4 or IPv6",__FILE__,__LINE__];
    }
    return $return;
  }
  public function int($number, array $opt){
    $number = \trim($number);
    if( !$return = \filter_var($number, FILTER_VALIDATE_INT) ){
      $this->errors['int'][] = [0,256,"[{$opt[0]}]: Invalid numeric data given.",__FILE__,__LINE__];
    }
    return $return;
  }
  public function float($float, array $opt){
    $float = \trim($float);
    if( !$return = \filter_var($float, FILTER_VALIDATE_FLOAT) ){
      $this->errors['float'][] = [0,256,"[{$opt[0]}]: Invalid floating point figure given.",__FILE__,__LINE__];
    }
    return $return;
  }
  public function password($password, array $opt){
    $regex = '/^.*(?=.{8,32})((?=.*[!@#$%^&*()\/\-_=+{}\]\[;:,<.>]){1})(?=.*\d)((?=.*[a-z]){1})((?=.*[A-Z]){1}).*$/m';

    \preg_match_all($regex, $password, $matches, PREG_SET_ORDER, 0);
    $return = $matches ? $password : false;
    if( !$return ){
      $this->errors['password'][] = [0,256,"[{$opt[0]}]: Password is too weak. Choose a stronger password of minimum character length:8, maximum character length: 32, it should contain at least one lower and upper case letter, a numeric and a special character such as $@$!%*?&",__FILE__,__LINE__];
    }
    return $return;
  }
  public function isHTML($string){
    $start =\strpos($string, '<');
    $end  =\strrpos($string, '>',$start);
    if ($end !== false) {
      $string = \substr($string, $start);
    } else {
      $string = \substr($string, $start, \strlen($string)-$start);
    }
    \libxml_use_internal_errors(true);
    \libxml_clear_errors();
    $xml = \simplexml_load_string($string);
    return \count(\libxml_get_errors())==0;
  }
  public function pattern($val, array $opt){
    $val = \trim($val);
    #fieldname, #pattern
    if( \count($opt) < 3 ){
      $this->errors["pattern"][] = [3,256,"Invalid supplied options.",__FILE__,__LINE__];
      return false;
    }
    // check if regex is valid.
    ini_set('track_errors', 'on');
    $php_errormsg = '';
    @ \preg_match($opt[2], '');
    if( $php_errormsg ) {
      $this->errors["pattern"][] = [3,256,"Invalid regular expression supplied.",__FILE__,__LINE__];
      return false;
    }
    $match = \preg_match_all($opt[2], $val);
    if( !$match ){
      $this->errors["pattern"][] = [0,256,"[{$opt[0]}]: Provided value does not match required pattern.",__FILE__,__LINE__];
      return false;
    }
    return $val;
  }
  public function array($val, array $opt=[]){
    // return \is_array($val)
  }

}
