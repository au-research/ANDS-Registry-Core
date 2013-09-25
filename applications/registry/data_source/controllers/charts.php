<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Data Source Charts controller
 * 
 * 
 * @author Ben Greenwood <ben.greenwood@ands.org.au>
 * @see ands/datasource/_data_source
 * @package ands/datasource
 * 
 */
class Charts extends MX_Controller {

	private $max_chart_rows = 10;

	/**
	 * 
	 * 
	 * 
	 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @param 
	 * @return [JSON] output
	 */
	public function getRegistryObjectProgression($id)
	{
		$chart_data = array();

		$this->load->model("data_sources","ds");
		// xxx: ACL
		$dataSource = $this->ds->getByID($id);//get everything

		if ($dataSource)
		{
			/* Iteratively get chart data, first try  */ 
			// Assume daily entries, if too many, expand to monthly 
			$date_format = "%b"; // i.e. 4-Jan (formats: http://www.jqplot.com/docs/files/plugins/jqplot-dateAxisRenderer-js.html)
			$query =  $this->aggregateRecordCreatedProgression($dataSource, ONE_DAY);
			// Condense the chart if too many individual values...
			if ($query->num_rows() > $this->max_chart_rows)
			{
				$date_format = "%b-%y"; // i.e. Jan-13
				$query = $this->aggregateRecordCreatedProgression($dataSource, ONE_MONTH);
			}

			// First entry when the DS was created (val = 0)
			if($dataSource->created)
			{
				$chart_data[] = array(date(DATE_RFC822, $dataSource->created), 0);
			}

			// Loop through the individual results
			$cumulative_count = 0;
			foreach ($query->result_array() AS $result)
			{
				$cumulative_count += (int)$result['count'];
				$chart_data[] = array(date(DATE_RFC822, $result['datestamp']), $cumulative_count );
			}

			// If we (still) have too many rows, just start the chart 
			// from the height max_chart_rows away from now
			if (count($chart_data) > $this->max_chart_rows+1)
			{
				$chart_data = array_slice($chart_data, -$this->max_chart_rows);
			}

			echo json_encode(array("table"=>$chart_data, "formatString" => $date_format));

		}
		else
		{
			throw new Exception ("Invalid data source ID.");
		}
	}

	

	private function aggregateRecordCreatedProgression($_data_source, $interval)
	{
	
		return $query = $this->db->query("SELECT COUNT(*) AS count, FLOOR(value/".$interval.")*".$interval." AS datestamp 
										FROM registry_objects NATURAL JOIN registry_object_attributes 
										WHERE data_source_id = ".$_data_source->id." AND attribute='created' GROUP BY datestamp;");
	}


	public function getDataSourceQualityChart($data_source_id, $status = 'ALL', $as_csv = false)
	{
		$chart_result = array();

		$constraints = array($data_source_id);
		if ($status != 'ALL') { $constraints[] = $status; }

		$query = $this->db->query("SELECT count(*) AS count, `value`, `class`
							FROM registry_objects ro 
							JOIN registry_object_attributes ra ON ra.registry_object_id = ro.registry_object_id 
							WHERE data_source_id=? AND ra.attribute = 'quality_level' " . ($status != 'ALL' ? "AND ro.status = ? " : '') . "
							GROUP BY `value`, `class`;", $constraints);

		$this->load->model("registry_object/registry_objects");
		$chart_result['All Records'] = array();
		foreach (Registry_objects::$classes AS $c => $c_label)
		{
			$chart_result[$c_label] = array();

			foreach (Registry_objects::$quality_levels AS $q => $q_label)
			{
				if(!isset($chart_result['All'][$q_label])){
				$chart_result['All Records'][$q_label] = 0;	
				} 			
				if ($q_label == "Gold Standard Record")
				{
					$gs_query = $this->db->query("SELECT count(*) AS count
							FROM registry_objects ro 
							JOIN registry_object_attributes ra ON ra.registry_object_id = ro.registry_object_id 
							WHERE 
							class = ? 
							AND data_source_id=? 
							AND ra.attribute = 'gold_status_flag' 
							AND ra.value = 't' " . 
							($status != 'ALL' ? "AND ro.status = ? " : '') . ";", array_merge(array($c), $constraints));
					
					$gs_query = array_pop($gs_query->result_array());
					$chart_result[$c_label][$q_label] = (int) $gs_query['count'];
					$chart_result['All Records'][$q_label] = $chart_result['All Records'][$q_label] + (int) $gs_query['count'];
				}
				else
				{
					$chart_result[$c_label][$q_label] = 0;
				}
			}
		}


		foreach ($query->result() AS $row)
		{
			if (isset($chart_result[Registry_objects::$classes[$row->class]]) && isset(Registry_objects::$quality_levels[$row->value]))
			{
				$chart_result[Registry_objects::$classes[$row->class]][Registry_objects::$quality_levels[$row->value]] = (int) $row->count;
				$chart_result['All Records'][Registry_objects::$quality_levels[$row->value]] = $chart_result['All Records'][Registry_objects::$quality_levels[$row->value]] + (int) $row->count;
			}
		}

		// Default, deliver as JSON
		if (!$as_csv)
		{
			echo json_encode($chart_result);
		}
		else
		{
			$header_row = array("");
			foreach (Registry_objects::$quality_levels AS $q => $q_label)
			{
				array_push($header_row, $q_label);
			}
			$rows = array(array(readable($status) . " Records"), $header_row);
			foreach ($chart_result AS $class => $results)
			{
				array_unshift($results, $class);
				$rows[] = $results;
			}

			// We'll be outputting a CSV
			header('Content-type: application/ms-excel');
			header('Content-Disposition: attachment; filename="datasource_quality_'.$data_source_id.'.xls"');
			$data = array_merge($rows);
			echo array_to_TABCSV($data);
		}

	}


	public function getDataSourceStatusChart($data_source_id, $as_csv = false)
	{
		$chart_result = array();

		$query = $this->db->query("SELECT count(*) AS count, `status`, `class`
							FROM registry_objects ro 
							WHERE data_source_id=?
							GROUP BY `status`, `class`;", array($data_source_id));


		$this->load->model("registry_object/registry_objects");
		foreach (Registry_objects::$classes AS $c => $c_label)
		{
			$chart_result[$c_label] = array();
			$chart_result[$c_label][] = array('Status','Number of Records');
			foreach (Registry_objects::$statuses AS $class)
			{
				$chart_result[$c_label][] = array($class, 0);
			}
		}

		foreach ($query->result() AS $row)
		{
			if (isset($chart_result[Registry_objects::$classes[$row->class]]))
			{
				foreach ($chart_result[Registry_objects::$classes[$row->class]] AS &$entry)
				{
					if ($entry[0] == Registry_objects::$statuses[$row->status])
						$entry[1] = (int) $row->count;
				}

			}
		}
		
		// Default: JSON response
		if (!$as_csv)
		{
			$clean_result = array();
			foreach($chart_result AS $class => $result)
			{
				$show = false;
				foreach ($result AS $label => $value)
				{
					if ($value[1] > 0)
					{
						$show = true;
					}
				}
				if ($show)
				{
					$clean_result[$class] = $result;
				}
			}
			echo json_encode($clean_result);
		}
		else
		{
			/* Map back to a CSV file for excel dump */
			$rows = array();
			$columns = array('');
			foreach ($chart_result AS $class => $values)
			{
				$row = array($class);
				foreach ($values AS $value)
				{
					$status = $value[0];
					$value = $value[1];
					if (is_integer($value))
					{
						if (!isset($columns[$status]))
						{
							$columns[$status] = $status;
						}

						$row[] = $value;
					}

				}

				$rows[] = $row;
			}

			// We'll be outputting a CSV
			header('Content-type: application/ms-excel');
			header('Content-Disposition: attachment; filename="datasource_status_'.$data_source_id.'.xls"');
			$data = array_merge(array(array_values($columns)), $rows);
			echo array_to_TABCSV($data);

		}
	}



	/**
	 * Get a list of data sources
	 * 
	 * 
	 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
	 * @param [INT] page
	 * @todo ACL on which data source you have access to, error handling
	 * @return [JSON] results of the search
	 */
	public function getDataSources($page=1){
		//$this->output->enable_profiler(TRUE);
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-type: application/json');

		$jsonData = array();
		$jsonData['status'] = 'OK';

		$this->load->model("data_sources","ds");

		//Limit and Offset calculated based on the page
		$limit = 16;
		$offset = ($page-1) * $limit;

		$dataSources = $this->ds->getAll($limit, $offset);

		$this->load->model("registry_object/registry_objects", "ro");

		$items = array();
		foreach($dataSources as $ds){
			$item = array();
			$item['title'] = $ds->title;
			$item['id'] = $ds->id;

			$item['counts'] = array();
			foreach ($this->ro->valid_status AS $status){
				if($ds->getAttribute("count_$status")>0){
					array_push($item['counts'], array('status' => $status, 'count' =>$ds->getAttribute("count_$status")));
				}
			}
			array_push($items, $item);
		}
		
		$jsonData['items'] = $items;
		$jsonData = json_encode($jsonData);
		echo $jsonData;
	}

	
	/**
	 * @ignore
	 */
	public function __construct()
	{
		parent::__construct();
	}
	
}
