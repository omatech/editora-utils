<?php
namespace Omatech\Editora\Utils;


class TicTac 
{
		var $enabled=true;

		function __construct($enabled=true)
		{
				$this->enabled=$enabled;			
		}

		function tic($id) 
		{// Keeps the start time in a global variable named after the id
			if ($this->enabled) 
			{
				$GLOBALS['omatech_editora__utils_timings_start_'.$id]=microtime(true);
			}
		}

		//////////////////////////////////////////////////////////////////////////////////////////
		function tac($id) { // Keeps the end time in a global variable named after the id
			if ($this->enabled) 
			{
				$GLOBALS['omatech_editora_utils_timings_end_'.$id]=microtime(true);
			}
		}

		//////////////////////////////////////////////////////////////////////////////////////////
		function get_time($id) {
			if ($this->enabled) 
			{
				if (isset($GLOBALS['omatech_editora__utils_timings_start_'.$id]) && isset($GLOBALS['omatech_editora_utils_timings_end_'.$id])) {
					$start_microtime=$GLOBALS['omatech_editora_utils_timings_start_'.$id];
					$end_microtime=$GLOBALS['omatech_editora_utils_timings_end_'.$id];
					$total_time=round(($end_microtime-$start_microtime)*1000, 4);
					return $total_time;
				}
			}  
		}

		//////////////////////////////////////////////////////////////////////////////////////////
		function get_full_stats() {
			$ret="\n********************TIMINGS********************\n";
			$anterior=array();
			$global_array=array();
			$i=0;
			print_r($GLOBALS);
			
			foreach ($GLOBALS as $key=>$value) { // per cada variable global comprovem si es de timing, si es aixi l'afegim a la sortida
				if (stripos($key, 'omatech_editora_utils_timings_start')!==false) {
					$global_array[$i]=substr($key,14);
					$i++;
				}
			}

			for ($x=0;$x<$i;$x++) {
				$ret.=$global_array[$x]."\n";
				$ret.='Start: '.$GLOBALS['omatech_editora_utils_timings_end_'.$global_array[$x]].'  End: '.$GLOBALS['omatech_editora_utils_timings_end_'.$global_array[$x]].' Time: '.round(($GLOBALS['omatech_editora_utils_timings_end_'.$global_array[$x]]-$GLOBALS['omatech_editora_utils_timings_start_'.$global_array[$x]])*1000,4)."ms (".round(($GLOBALS['omatech_editora_utils_timings_end_'.$global_array[$x]]-$GLOBALS['omatech_editora_utils_timings_start_'.$global_array[$x]]),4)." s)\n\n";
			}
			$ret.="********************END TIMINGS********************\n";

			return $ret;
		}

}
