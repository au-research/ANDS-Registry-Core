<?php

/**
 * Class Migrate
 * Doing migration on the Registry
 * Usage: php index.php registry maintenance migrate doMigration <module>
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class Migrate extends MX_Controller
{
    /**
     * Migrate constructor.
     */
    public function __construct()
    {
        parent::__construct();

        set_exception_handler('json_exception_handler');
        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');

        require_once APP_PATH . 'maintenance/models/GenericMigration.php';
        require_once APP_PATH . 'maintenance/models/GenericSolrMigration.php';
    }

    /**
     * Migrate
     * Should run from shell
     * @usage php index.php registry maintenance migrate doMigration registryIndex [up|down] [until="000"]
     * @param $context
     * @param string $method
     * @param bool|false $until
     * @throws Exception
     */
    function doMigration($context, $method = 'up', $until = false)
    {
        //Load required migration file
        $migration_dir = APP_PATH . 'maintenance/migrations/';
        if ($context == 'registryIndex') {
            $migration_dir.='registry_index/';
        } else if ($context == 'vocabIndex') {
            $migration_dir.='vocab_index/';
        } else if ($context == 'relationIndex') {
            $migration_dir.='relation_index/';
        }

        $namespace = 'ANDS';
        $files = array_diff(scandir($migration_dir), array('..', '.'));

        //make sure the files come sorted
        if ($method == 'up') {
            sort($files);
        } else if ($method == 'down') {
            rsort($files);
        }

        //getting the latest migration state
        $latestSuccess = "000";
        if ($migrationStatus = $this->readMigrationStatus()) {
            if (isset($migrationStatus[$context])) {
                $latestSuccess = $migrationStatus[$context];
                echo "Found latest migration for $context at " . $latestSuccess . "\n";
            } else {
                echo "Found migration file but no entry for $context. Starts at " . $latestSuccess . "\n";
            }
        } else {
            echo "No previous migration found. Starts at 000" . "\n";
        }

        //remove files that does not satisfy from latest to until
        foreach ($files as $key => $file) {
            $file_path = $migration_dir . $file;
            $exploded = explode('_', basename($file_path, ".php"));
            $file_state = (int) $exploded[0];
            if ($until) {
                $untilInt = (int) $until;
                if ($file_state > $untilInt && $method == 'up') {
                    unset($files[$key]);
                } else if ($file_state <= $untilInt && $method == 'down') {
                    unset($files[$key]);
                }
            }
            if ($latestSuccess) {
                if ($file_state <= (int)$latestSuccess && $method == 'up') {
                    unset($files[$key]);
                }
            }
        }

        // if there's nothing to do, stop
        if (sizeof($files) == 0) {
            echo "No files found. Nothing to do" . "\n";
            exit();
        }

        // run each migration file separately, in the right order
        foreach ($files as $file) {
            $file_path = $migration_dir . $file;
            $exploded = explode('_', basename($file_path, ".php"));
            $file_state = $exploded[0];
            $file_name = $exploded[1];
            $class_name = $namespace . '\\' . $file_name;

            try {
                require_once $file_path;
                $migration = new $class_name;
                $results = $migration->$method();
                $migrationResults = array();
                if (is_array($results)) {
                    foreach ($results as $result) {
                        if ($result && !is_array($result)) {
                            $migrationResults[] = json_decode($result, true);
                        }
                    }
                } else {
                    $migrationResults[] = json_decode($results, true);
                }

                foreach ($migrationResults as $key=>$migrationResult) {
                    //parse SOLR response and decide if there's any error

                    $status = $migrationResult['responseHeader']['status'];
                    if ($status == "0" && !isset($migrationResult['errors'])) {
                        //success handler, increment the latestSuccess to this file state
                        echo $file . " stage " . ($key + 1) . " " .$method . " Success" . "\n";
                        $latestSuccess = $file_state;
                    } else {
                        //error handler, if the migration is going up, should break away
                        echo $file . " " . $method . " Failed" . "\n";
                        if (isset($migrationResult['errors'])) {
                            foreach ($migrationResult['errors'] as $error) {
                                if (is_array($error['errorMessages'])) {
                                    echo join(' ', $error['errorMessages']);

                                } else {
                                    echo $error['errorMessages'] . "\n";
                                }
                            }
                        } else {
                            var_dump($migrationResult);
                        }
                        break;
                    }
                }


            } catch (Exception $e) {
                //exception in one of the migration file
                echo $file . " " . $method . " Failed with exception" . "\n";
                $latestSuccess = $file_state;

                if ($method == 'down' && !$until) {
                    $latestSuccess = "000";
                } else if ($method == 'down' && $until) {
                    $latestSuccess = $until;
                }

                $migrationStatus['solr'] = $latestSuccess;
                $this->writeMigrationStatus($migrationStatus);

                throw new Exception ($e);
                break;
            }

            if ($method == 'down' && !$until) {
                $latestSuccess = "000";
            } else if ($method == 'down' && $until) {
                $latestSuccess = $until;
            }

            $migrationStatus[$context] = $latestSuccess;
            $this->writeMigrationStatus($migrationStatus);
        }

        echo "Done. latest migration state : " . $latestSuccess . "\n";
    }


    /**
     * Print the migration status on the shell screen
     * Usage: php index.php maintenance migrate printMigrationStatus
     */
    public function printMigrationStatus()
    {
        print_r($this->readMigrationStatus());
    }

    /**
     * Give the current migration status
     * @return array|bool
     */
    private function readMigrationStatus()
    {
        $file = '/tmp/migrationStatus';
        if (file_exists($file)) {
            $fileContents = parse_ini_file($file);
            return $fileContents;
        } else {
            return false;
        }
    }

    /**
     * Write the migration status to file
     * @param $migrationStatus
     */
    private function writeMigrationStatus($migrationStatus)
    {
        $file = '/tmp/migrationStatus';
        $this->write_ini_file($migrationStatus, $file, false);
    }


    /**
     * Helper function to write a PHP array to an ini file
     * @param $assoc_arr
     * @param $path
     * @param bool|FALSE $has_sections
     * @return bool|int
     */
    function write_ini_file($assoc_arr, $path, $has_sections = FALSE)
    {
        $content = "";
        if ($has_sections) {
            foreach ($assoc_arr as $key => $elem) {
                $content .= "[" . $key . "]\n";
                foreach ($elem as $key2 => $elem2) {
                    if (is_array($elem2)) {
                        for ($i = 0; $i < count($elem2); $i++) {
                            $content .= $key2 . "[] = \"" . $elem2[$i] . "\"\n";
                        }
                    } else if ($elem2 == "") $content .= $key2 . " = \n";
                    else $content .= $key2 . " = \"" . $elem2 . "\"\n";
                }
            }
        } else {
            foreach ($assoc_arr as $key => $elem) {
                if (is_array($elem)) {
                    for ($i = 0; $i < count($elem); $i++) {
                        $content .= $key . "[] = \"" . $elem[$i] . "\"\n";
                    }
                } else if ($elem == "") $content .= $key . " = \n";
                else $content .= $key . " = \"" . $elem . "\"\n";
            }
        }

        if (!$handle = fopen($path, 'w')) {
            return false;
        }

        $success = fwrite($handle, $content);
        fclose($handle);

        return $success;
    }



}