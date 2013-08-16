<?php
/**
 * This class contains core methods used by other classes.
 *
 * @author Alberto Miranda <alberto.php@gmail.com>
 */
class Framework {
    private $debugMode = 'normal'; //[off|minimal|normal|full]

    public function getResponse($type, $description, $data, $caller){
        $response['type']           = $type;
        $response['description']    = $description;
        $response['data']           = $data;
        $response['caller']         = $caller;

        return $response;
    }

    public function debug($message, $debugMode=2){
        $debugModeNumericalValue = array(
            'off'       => 0,
            'minimal'   => 1,
            'normal'    => 2,
            'full'      => 3
        );
        $actualDebugModeNumericalValue      = $debugModeNumericalValue[$debugMode];
        $selectedDebugModeNumericalValue    = $debugModeNumericalValue[$this->debugMode];
        if($actualDebugModeNumericalValue > $selectedDebugModeNumericalValue) return false;
        
        echo "DEBUG: $message\n";
    }

    public function getMemoryUsage(){
        return memory_get_usage(true)/1024/1024 . 'M';
    }

    public function writeFile($file, $string){
        $fp = fopen($file, 'a');
        fwrite($fp, $string);
        fclose($fp);
    }
}
