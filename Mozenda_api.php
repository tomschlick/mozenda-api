<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Mozenda API Library
 *
 * An open source api interface for the mozenda data scraping service.
 *
 * @author		Tom Schlick - tom@tomschlick.com
 * @copyright	Copyright (c) 2009, TomSchlick & CodeSanity.net
 * @link		http://codesanity.net
 * @version		Version 1.0
 * @date		04-25-2009
 */
 
 /*
  *	 Copyright (c) 2009 Tom Schlick, CodeSanity.net
  *	
  *	Permission is hereby granted, free of charge, to any person
  *	obtaining a copy of this software and associated documentation
  *	files (the "Software"), to deal in the Software without
  *	restriction, including without limitation the rights to use,
  *	copy, modify, merge, publish, distribute, sublicense, and/or sell
  *	copies of the Software, and to permit persons to whom the
  *	Software is furnished to do so, subject to the following
  *	conditions:
  *	
  *	The above copyright notice and this permission notice shall be
  *	included in all copies or substantial portions of the Software.
  *	
  *	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
  *	EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
  *	OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
  *	NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
  *	HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
  *	WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
  *	FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
  *	OTHER DEALINGS IN THE SOFTWARE.
  */

 
class Mozenda_api
{
	var $config;
	var $url;
	var $result_container;
	
	var $output_raw = NULL;
	var $output_json = NULL;
	var $output_array = NULL;
	/*
	+-------------------------------------+
		Name: __construct
		Purpose: starts the whole class out with
		default parameters
		@param return : none
	+-------------------------------------+
	*/
	
	function __construct()
	{
		$this->config = array(
		'output_format' => 'array',
		'api_key' => '',
		);
		
		if(! function_exists('json_decode') && $this->config['output_format'] == 'json')
		{
			$this->config['output_format'] = 'array';
		}
	}
	
	/*
	+-------------------------------------+
		Name: config
		Purpose: used to set one to many config
		values at one time
		@param return : none
	+-------------------------------------+
	*/
	function config($config = NULL)
	{
		if(is_array($config))
		{
			foreach ($config as $key => $val)
			{
				$this->config[$key] = $val;
			}
		}
		else
		{
			show_error("Could not set configuration values. Config parameter is not an array.");
		}
	}
	/*
	+-------------------------------------+
		Name: clear
		Purpose: clears the url so a new one can be made
		@param return : none
	+-------------------------------------+
	*/
	function clear()
	{
		$this->url = NULL;
	}
	/*
	+-------------------------------------+
		Name: run
		Purpose: run the operation
		@param return : array or FALSE
	+-------------------------------------+
	*/
	function run($param_array = NULL)
	{
		if($param_array == NULL)
		{
			show_error("The Operation cannot proceed because there was not any parameters specified");
		}
		
		$params = "";
		foreach($param_array as $key => $val)
		{
			$params .= "&".$key."=".$val;
		}
		
		if($this->config['api_key'] == '')
		{
			show_error("Missing API KEY!");
		}
		$this->clear();
		$this->url = "https://api.mozenda.com/Rest.aspx?WebServiceKey=".$this->config['api_key']."&Service=Mozenda10".$params;
		
		$return = $this->xml2array($this->url, 0);
		if($this->result_container == '')
		{
			$this->result_container = trim(str_replace('.','', $param_array['Operation'])).'Response';
		}
		$return = $return[$this->result_container];
		if(is_array($return))
		{
			$done = array();
			foreach($return as $key=>$val)
			{
				$done[$key] = $val;
			}
			
			if(isset($done['Result']))
			{
				if($done['Result'] != 'Success')
				{
					show_error("Error Code: ".$done['ErrorCode']." - Message: ".$done['ErrorMessage']);
				}
				if($this->config['output_format'] == 'json')
				{
					return json_encode($done);
				}
				else
				{
					return $done;
				}
			}
			else
			{
				show_error("Nothing was returned. This was the url that was requested - '".$this->url."'");
			}
		}
		return FALSE;
	}
	/*
	+-------------------------------------+
		Name: collection_get_list
		Purpose: gets a list of collections
		@param return : none
	+-------------------------------------+
	*/
	function collection_get_list()
	{
		$params = array(
		"Operation" => "Collection.GetList",
		);
		return $this->run($params);
	}
	/*
	+-------------------------------------+
		Name: collection_get_views
		Purpose: gets a list of views for a specific collection
		@param return : none
	+-------------------------------------+
	*/
	function collection_get_views($collection = NULL)
	{
		if($collection == NULL)
		{
			show_error("Collection ID not specified");
		}
		
		$params = array(
		'Operation' => "Collection.GetViews",
		'CollectionID' => $collection,
		);
		return $this->run($params);
	}
	/*
	+-------------------------------------+
		Name: collection_get_fields
		Purpose: gets the list of fields for a specific collection
		@param return : none
	+-------------------------------------+
	*/
	function collection_get_fields($collection = NULL)
	{
		if($collection == NULL)
		{
			show_error("Collection ID not specified");
		}
		
		$params = array(
		'Operation' => "Collection.GetFields",
		'CollectionID' => $collection,
		);
		return $this->run($params);
	}
	/*
	+-------------------------------------+
		Name: collection_add_item
		Purpose: adds an item to a collection
		@param return : none
	+-------------------------------------+
	*/
	function collection_add_item($collection = NULL, $items = NULL)
	{
		if($collection == NULL || $items == NULL)
		{
			show_error("Collection ID not specified or Items not specified");
		}
		
		$params = array(
		'Operation' => "Collection.AddItem",
		'CollectionID' => $collection,
		);
		
		foreach($items as $key => $val)
		{
			$params['Field.'.$key] = $val;
		}
		
		return $this->run($params);
	}
	/*
	+-------------------------------------+
		Name: collection_update_item
		Purpose: updates an item in a collection
		@param return : none
	+-------------------------------------+
	*/
	function collection_update_item($collection = NULL, $item_id = NULL, $items = NULL)
	{
		if($collection == NULL || $items == NULL || $item_id = NULL)
		{
			show_error("Collection ID, Item ID or Items Array not specified");
		}
		
		$params = array(
		'Operation' => "Collection.UpdateItem",
		'CollectionID' => $collection,
		'ItemID' => $item_id,
		);
		
		foreach($items as $key => $val)
		{
			$params['Field.'.$key] = $val;
		}
		
		return $this->run($params);
	}
	/*
	+-------------------------------------+
		Name: collection_delete_item
		Purpose: deletes an item from the collection
		@param return : none
	+-------------------------------------+
	*/
	function collection_delete_item($collection = NULL, $item_id = NULL)
	{
		if($collection == NULL || $item_id == NULL)
		{
			show_error("Collection ID or Item ID not specified");
		}
		
		$params = array(
		'Operation' => "Collection.DeleteItem",
		'CollectionID' => $collection,
		'ItemID' => $item_id,
		);
		
		return $this->run($params);
	}
	/*
	+-------------------------------------+
		Name: collection_clear
		Purpose: clears a collection of all data
		@param return : none
	+-------------------------------------+
	*/
	function collection_clear($collection = NULL)
	{
		if($collection == NULL)
		{
			show_error("Collection ID not specified");
		}
		
		$params = array(
		'Operation' => "Collection.Clear",
		'CollectionID' => $collection,
		);
		
		return $this->run($params);
	}
	/*
	+-------------------------------------+
		Name: collection_delete
		Purpose: removes a collection
		@param return : none
	+-------------------------------------+
	*/
	function collection_delete($collection = NULL)
	{
		if($collection == NULL)
		{
			show_error("Collection ID not specified");
		}
		
		$params = array(
		'Operation' => "Collection.Delete",
		'CollectionID' => $collection,
		);
		
		return $this->run($params);
	}
	/*
	+-------------------------------------+
		Name: view_get_items
		Purpose: gets all items for a specific view
		@param return : none
	+-------------------------------------+
	*/
	function view_get_items($view = NULL, $page = 1, $count = 100, $extra = NULL)
	{
		if($view == NULL)
		{
			show_error("View ID not specified");
		}
		
		if($count > 1000)
		{
			$count = 1000;
		}
		
		$params = array(
		'Operation' => "View.GetItems",
		'ViewID' => $view,
		'PageNumber' => $page,
		'PageItemCount' => $count,
		);
		
		if($extra != NULL)
		{
			foreach($extra as $key=>$val)
			{
				$params['ViewParameter'.$key] = $val;
			}
		}
		
		return $this->run($params);
	}
	/*
	+-------------------------------------+
		Name: agent_get_list
		Purpose: gets a list of all agents in your account
		@param return : none
	+-------------------------------------+
	*/
	function agent_get_list()
	{
		$params = array(
		"Operation" => "Agent.GetList",
		);
		return $this->run($params);
	}
	/*
	+-------------------------------------+
		Name: agent_get_jobs
		Purpose: gets a list of jobs for an agent
		@param return : none
	+-------------------------------------+
	*/
	function agent_get_jobs($agent = NULL)
	{
		if($agent == NULL)
		{
			show_error("Agent ID parameter is missing.");
		}
		$params = array(
		"Operation" => "Agent.GetJobs",
		"AgentID" => $agent,
		);
		return $this->run($params);
	}
	/*
	+-------------------------------------+
		Name: agent_run
		Purpose: commands an agent to start running
		@param return : none
	+-------------------------------------+
	*/
	function agent_run($agent = NULL)
	{
		if($agent == NULL)
		{
			show_error("Agent ID parameter is missing.");
		}
		$params = array(
		"Operation" => "Agent.Run",
		"AgentID" => $agent,
		);
		return $this->run($params);
	}
	/*
	+-------------------------------------+
		Name: agent_delete
		Purpose: removes an agent from your account
		@param return : none
	+-------------------------------------+
	*/
	function agent_delete($agent = NULL)
	{
		if($agent == NULL)
		{
			show_error("Agent ID parameter is missing.");
		}
		$params = array(
		"Operation" => "Agent.Delete",
		"AgentID" => $agent,
		);
		return $this->run($params);
	}
	/*
	+-------------------------------------+
		Name: job_get
		Purpose: gets the details of a job
		@param return : none
	+-------------------------------------+
	*/
	function job_get($job = NULL)
	{
		if($job == NULL)
		{
			show_error("Job ID parameter is missing.");
		}
		$params = array(
		"Operation" => "Job.Get",
		"JobID" => $job,
		);
		return $this->run($params);
	}
	/*
	+-------------------------------------+
		Name: job_cancel
		Purpose: cancels a running or paused job
		@param return : none
	+-------------------------------------+
	*/
	function job_cancel($job = NULL)
	{
		if($job == NULL)
		{
			show_error("Job ID parameter is missing.");
		}
		$params = array(
		"Operation" => "Job.Cancel",
		"JobID" => $job,
		);
		return $this->run($params);
	}
	/*
	+-------------------------------------+
		Name: job_pause
		Purpose: pauses a job
		@param return : none
	+-------------------------------------+
	*/
	function job_pause($job = NULL)
	{
		if($job == NULL)
		{
			show_error("Job ID parameter is missing.");
		}
		$params = array(
		"Operation" => "Job.Pause",
		"JobID" => $job,
		);
		return $this->run($params);
	}
	/*
	+-------------------------------------+
		Name: job_resume
		Purpose: continues a paused job
		@param return : none
	+-------------------------------------+
	*/
	function job_resume($job = NULL)
	{
		if($job == NULL)
		{
			show_error("Job ID parameter is missing.");
		}
		$params = array(
		"Operation" => "Job.Resume",
		"JobID" => $job,
		);
		return $this->run($params);
	}
	
	/*
	+-------------------------------------+
		Name: _get_file
		Purpose: connects to the server and 
		retrieves the content for conversion to
		an array
		@param return : none
	+-------------------------------------+
	*/
	function _get_file()
	{
		if (function_exists('curl_init'))
		{
		   // initialize a new curl resource
		   $ch = curl_init();
		
		   // set the url to fetch
		   curl_setopt($ch, CURLOPT_URL, $this->url);
		
		   // don't give me the headers just the content
		   curl_setopt($ch, CURLOPT_HEADER, 0);
		   curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, true);
		   curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		   curl_setopt($ch, CURLOPT_PORT, 443);
		
		   // return the value instead of printing the response to browser
		   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		   
		   curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		   curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		
		   $content = curl_exec($ch);
		
		   // remember to always close the session and free all resources
		   curl_close($ch);
		   return $content;
		} 
		else 
		{
		   show_error("It seems you dont have curl installed. Please install curl to use this library.");
		} 
	}
	
	
	/*
	+-------------------------------------+
		Name: xml2array
		Purpose: takes an xml url and converts it to an array
		Note: I DID NOT WRITE THIS FUNCTION AND TAKE NO CREDIT FOR DOING SO.
		@param return : array of data
	+-------------------------------------+
	*/
	function xml2array($url, $get_attributes = 1, $priority = 'tag')
	{
	    $contents = "";
	    if (!function_exists('xml_parser_create'))
	    {
	        return array ();
	    }
	    $parser = xml_parser_create('');
	    $contents = $this->_get_file();
	    xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
	    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
	    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
	    xml_parse_into_struct($parser, trim($contents), $xml_values);
	    xml_parser_free($parser);
	    if (!$xml_values)
	        return; //Hmm...
	    $xml_array = array ();
	    $parents = array ();
	    $opened_tags = array ();
	    $arr = array ();
	    $current = & $xml_array;
	    $repeated_tag_index = array ();
	    foreach ($xml_values as $data)
	    {
	        unset ($attributes, $value);
	        extract($data);
	        $result = array ();
	        $attributes_data = array ();
	        if (isset ($value))
	        {
	            if ($priority == 'tag')
	                $result = $value;
	            else
	                $result['value'] = $value;
	        }
	        if (isset ($attributes) and $get_attributes)
	        {
	            foreach ($attributes as $attr => $val)
	            {
	                if ($priority == 'tag')
	                    $attributes_data[$attr] = $val;
	                else
	                    $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
	            }
	        }
	        if ($type == "open")
	        {
	            $parent[$level -1] = & $current;
	            if (!is_array($current) or (!in_array($tag, array_keys($current))))
	            {
	                $current[$tag] = $result;
	                if ($attributes_data)
	                    $current[$tag . '_attr'] = $attributes_data;
	                $repeated_tag_index[$tag . '_' . $level] = 1;
	                $current = & $current[$tag];
	            }
	            else
	            {
	                if (isset ($current[$tag][0]))
	                {
	                    $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
	                    $repeated_tag_index[$tag . '_' . $level]++;
	                }
	                else
	                {
	                    $current[$tag] = array (
	                        $current[$tag],
	                        $result
	                    );
	                    $repeated_tag_index[$tag . '_' . $level] = 2;
	                    if (isset ($current[$tag . '_attr']))
	                    {
	                        $current[$tag]['0_attr'] = $current[$tag . '_attr'];
	                        unset ($current[$tag . '_attr']);
	                    }
	                }
	                $last_item_index = $repeated_tag_index[$tag . '_' . $level] - 1;
	                $current = & $current[$tag][$last_item_index];
	            }
	        }
	        elseif ($type == "complete")
	        {
	            if (!isset ($current[$tag]))
	            {
	                $current[$tag] = $result;
	                $repeated_tag_index[$tag . '_' . $level] = 1;
	                if ($priority == 'tag' and $attributes_data)
	                    $current[$tag . '_attr'] = $attributes_data;
	            }
	            else
	            {
	                if (isset ($current[$tag][0]) and is_array($current[$tag]))
	                {
	                    $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
	                    if ($priority == 'tag' and $get_attributes and $attributes_data)
	                    {
	                        $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
	                    }
	                    $repeated_tag_index[$tag . '_' . $level]++;
	                }
	                else
	                {
	                    $current[$tag] = array (
	                        $current[$tag],
	                        $result
	                    );
	                    $repeated_tag_index[$tag . '_' . $level] = 1;
	                    if ($priority == 'tag' and $get_attributes)
	                    {
	                        if (isset ($current[$tag . '_attr']))
	                        {
	                            $current[$tag]['0_attr'] = $current[$tag . '_attr'];
	                            unset ($current[$tag . '_attr']);
	                        }
	                        if ($attributes_data)
	                        {
	                            $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
	                        }
	                    }
	                    $repeated_tag_index[$tag . '_' . $level]++; //0 and 1 index is already taken
	                }
	            }
	        }
	        elseif ($type == 'close')
	        {
	            $current = & $parent[$level -1];
	        }
	    }
	    return ($xml_array);
	}
}
?>