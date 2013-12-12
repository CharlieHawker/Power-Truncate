<?php

// Power Truncate by Charlie Hawker (http://www.charliehawker.com)
// Released under Creative Commons Attribution Non-Commercial Share Alike Licence
// Licence Details - http://creativecommons.org/licenses/by-nc-sa/3.0/

$plugin_info = array('pi_name' => 'Power Truncate',
                     'pi_version' => '1.1',
                     'pi_author' => 'Charlie Hawker',
                     'pi_author_url' => 'http://www.charliehawker.com/',
                     'pi_description' => 'Allows powerful truncating of text/html',
                     'pi_usage' => Power_truncate::usage());

class Power_truncate {  
  
  var $return_data = '';
  
  // Constructor
  //
  function Power_truncate() {
    $this->return_data = $this->_truncate();   
  } // END
  
  
  // Do the magic
  //
  function _truncate() {
    global $TMPL;

    $length = $TMPL->fetch_param('length') ? (int)$TMPL->fetch_param('length') : 100;
    $cut_words = $TMPL->fetch_param('cut_words') ? $TMPL->fetch_param('cut_words') : 'n';
    $suffix = $TMPL->fetch_param('suffix') ? $TMPL->fetch_param('suffix') : '';
    $strip_markup = $this->EE->TMPL->fetch_param('strip_markup') ? $this->EE->TMPL->fetch_param('strip_markup') : 'n';
    $contains_html = $this->_contains_html($TMPL->tagdata);

    // Easy peazy if they're truncating something to a longer length
    if (strlen(strip_tags($this->EE->TMPL->tagdata)) <= $length) {
      if ($strip_markup == "no") {
        return trim($this->EE->TMPL->tagdata) . $suffix . $strip_markup;    
      } else {
        return trim(strip_tags($this->EE->TMPL->tagdata)) . $suffix;
      }
    }
    
    // Get string cut off at exactly specified length
    if ($contains_html)
      $truncated = substr(trim(strip_tags($TMPL->tagdata)), 0, $length);
    else
      $truncated = substr(trim($TMPL->tagdata), 0, $length);

    // Cut words/fall back to last word end & apply suffix 
    if (!in_array(strtolower($cut_words), array('y','1','t')))
      $truncated = preg_replace('/\w+$/', '', trim($truncated));
    
    // Add suffix
    $truncated = $truncated . $suffix;

    // Put HTML entities back in if necessary. Leave them out if strip_markup is yes.
    if ($contains_html && $strip_markup == "n")
      $truncated = $this->_replace_tags($truncated, $TMPL->tagdata);

    return $truncated;

  } // END


  // Put HTML entities from orig string into other string & close them
  //
  function _replace_tags($string, $original_string)
  {
    // Find all opening tags
    $tags = $this->_find_tags($original_string);
    
    foreach ($tags as $tag)
    {
      $tag_offset = ($tag['offset'] == 0) ? $tag['offset'] : $tag['offset'] - 1;
      // cope with truncating from start too
      if ($tag['offset'] <= strlen($string)) {
        $string_to_offset = substr($string, 0, $tag_offset);
        $string_after_offset = substr($string, -(strlen($string) - $tag_offset));
        $string = $string_to_offset . $tag['tag'] . $string_after_offset;
      }
    }
    
    // Tidy up...
    $string = $this->_close_unclosed_tags($string);
    
    // This should be done now...
    return $this->_close_unclosed_tags($string);
  } // END


  // Close any unclosed tags
  //
  function _close_unclosed_tags($string)
  {
    $tags = $this->_find_tags($string);
    $to_append = '';
    foreach ($tags as $k1 => $t1)
    {
      if (substr($t1['tag'], 0, 2) != '</' && substr($t1['tag'], -2) != '/>') // ignore self-closing & closing tags
      {
        $closed = false;
        // establish what the closing tag should be
        $parts = explode(' ', str_replace(array('<', '>'), '', $t1['tag']));
        $c = '</' . $parts[0] . '>'; 
        // Loop over the tags again, find the closing tag if we can
        foreach ($tags as $k2 => $t2) {
          // Closing tag must be later in array, and match what we're looking for
          if ($closed == false && $t2['tag'] == $c && $k2 > $k1)
            $closed = true;
        }
        if (!$closed)
          $to_append = $c . $to_append;
      }
    }
    $string .= $to_append;
    return $string;
  }
  
  
  // Find HTML tags in a string
  //
  function _find_tags($string)
  {
    $processed_tags = array();
    preg_match_all('/\<(\/?[^\>]+)\>/', $string, $tags, PREG_OFFSET_CAPTURE);
    foreach ($tags[0] as $tag)
      $processed_tags[] = array('tag' => $tag[0], 'offset' => $tag[1]);
    return $processed_tags;
  }
  
  
  // Check if a string contains HTML
  //
  function _contains_html($str)
  {
    if (strlen($str) > strlen(strip_tags($str)))
      return true;
    else
      return false;
  } // END


  // Provide usage information for the plugin
  //
  static function usage()
  {
    ob_start();
    ?>
The Power Truncate plugin allows you to truncate text or html in your templates.

You can use paramaters to customize the way the truncation takes place:

- length (integer) - The length to truncate to (defaults to 100 character)
- suffix (string) - The suffix to place at the end of the truncated text (defaults to nothing)
- cut_words (1, t, true or y) - Allow words to be cut short rd (backtracks to end of the previous word by default)

Example Usage:
{exp:weblog:entries weblog="news" limit="5"}
  {title}
  {exp:power_truncate limit="150" cut_words="t" suffix="..."}
    {summary}
  {/exp:power_truncate}
{/exp:weblog:entries}
    <?php
    $usage = ob_get_contents();
    ob_end_clean();

    return $usage;
  } // END
}
// End of pi.power_truncate.php
?>