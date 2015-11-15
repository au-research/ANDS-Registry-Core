<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ElasticSearch {

    private $CI;
    private $elasticSearchUrl;
    private $options;
    private $path;
    private $response;

    function __construct() {
        $this->CI =& get_instance();
        $this->init();
    }

    function init() {
        $this->elasticSearchUrl = 'http://localhost:9200';
        $this->options = array();
        $this->path = '/';
        $this->chunkSize = 5000;
        return $this;
    }

    /**
     * Manually set the option for solr search
     * @param string $field
     * @param string $value
     */
    function setOpt($field, $value){
        if(isset($this->options[$field])){
            if(is_array($this->options[$field])){
                array_push($this->options[$field], $value);
            }else{
                if(in_array($field, $this->multi_valued_fields)){
                    $this->options[$field] = array($this->options[$field], $value);
                }else{
                    $this->options[$field] = $value;
                }
            }
        }else{
           $this->options[$field] = $value;
        }
        return $this;
    }

    /**
     * @param $filters
     */
    function setFilters($filters) {

        //groups
        $groups = [];
        if (isset($filters['groups'])) {
            foreach ($filters['groups'] as $group) {
                $groups[] = ['term' => ['group' => $group]];
            }
        }
//        $this->boolff('must', 'bool', 'should', $groups);
        $this->mustf('bool', 'should', $groups);

        //classes
        $classes = [];
        if (isset($filters['class']) && sizeof($filters['class']) > 0) {
            foreach ($filters['class'] as $class) {
                $classes[] = ['term' => ['class'=>$class]];
                $classes[] = ['term' => ['roclass'=>$class]];
            }
        }
        $this->mustf('bool', 'should', $classes);

        //data source
        $data_source_ids = [];
        if (isset($filters['data_sources']) && sizeof($filters['data_sources']) > 0) {
            foreach ($filters['data_sources'] as $ds_id) {
                $data_source_ids[] = ['term' => ['dsid' => $ds_id]];
            }
        }
        $this->mustf('bool', 'should', $data_source_ids);

        if ((sizeof($groups)==0 || sizeof($classes)==0 || sizeof($data_source_ids)==0) && (!isset($filters['Masterview']))) {
            $this->mustf('term', 'norecord', 'norecord');
        }

    }

    function andf($type, $key, $value) {
        $this->options['query']['filtered']['filter']['and'][] = array(
            $type => array($key=>$value)
        );
        return $this;
    }

    function boolf($cond, $type, $key, $value) {
        $this->options['query']['filtered']['filter']['bool'][$cond][] = array(
            $type => array($key=>$value)
        );
        return $this;
    }

    function boolff($cond, $type, $key, $value) {
        $this->options['filter']['bool'][$cond][] = array(
            $type => array($key=>$value)
        );
        return $this;
    }


    function mustf($type, $key, $value) {
        return $this->boolf('must', $type, $key, $value);
    }

    function shouldf($type, $key, $value) {
        return $this->boolf('should', $type, $key, $value);
    }

    function must_notf($type, $key, $value) {
        return $this->boolf('must_not', $type, $key, $value);
    }

    function setQuery($type, $value) {
        if (!isset($this->options['query'])) $this->options['query'] = array();
        $this->options['query'][$type] = $value;
        return $this;
    }

    function setAggs($type, $value) {
        if (!isset($this->options['aggs'])) $this->options['aggs'] = array();
        $this->options['aggs'][$type] = $value;
        return $this;
    }

    function setFacet($type, $value) {
        if (!isset($this->options['facets'])) $this->options['facets'] = array();
        $this->options['facets'][$type] = $value;
        return $this;
    }

    function search($content = false) {
//        dd($this->options);
        if (!$content) {
            $content = json_encode($this->options);
        }

        return $this->exec('GET', $content);
    }

    function bulk($verb, $content) {
        if ($content && is_array($content) && sizeof($content) > 0) {
            $response = array();
            if (sizeof($content) > $this->chunkSize) {
                $numChunk = ceil(($this->chunkSize < sizeof($content) ? (sizeof($content) / $this->chunkSize) : 1));
                $chunks = array_chunk($content, $this->chunkSize);
                foreach ($chunks as $chunk) {
                    $response[] = $this->execBulk($verb, $chunk);
                }
            } else {
                $response[] = $this->execBulk($verb, $content);
            }
            return $response;
        } else {
            return false;
        }
    }

    private function execBulk($verb, $content) {
        // var_dump(memory_get_usage());
        $data = null;
        $data = "";
        foreach ($content as &$line) {
            $l = json_encode($line);
            if (isset($line['_id'])) {
                $id = $line['_id'];
                unset($line['_id']);
                $l = json_encode($line);
                $data.=
                    "{ \"$verb\":  {\"_id\":\"".$id."\"} } \n".
                    $l."\n";
            } else {
                $data.=
                    "{ \"$verb\":  {} } \n".
                    $l."\n";
            }
        }
        $data.="\n";
        return $this->exec('POST', $data, false);
    }

    /**
     * Execute the Verb
     * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
     * @param $verb
     * @param bool $content
     * @param bool $noresponse
     * @return bool|mixed
     */
    function exec($verb, $content = false, $noresponse = false) {
        $this->response = null;
        $ch = curl_init($this->elasticSearchUrl.$this->path);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $verb);
//        dd($content);
        if ($content) {
            if (is_array($content)) $content = json_encode($content, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
        }
        $this->response = curl_exec($ch);

        if ($noresponse) {
            return true;
        } else {
            $this->response = json_decode($this->response, true);
            return $this->response;
        }
    }

    function getResponse() {return $this->response; }
    function post($content) {return $this->exec('POST', $content); }
    function delete() {return $this->exec('DELETE'); }
    function put($content) {return $this->exec('PUT', $content); }
    function get($content = false) {return $this->exec('GET', $content); }

    /**
     * Manually unsset the option for solr search
     * @param string $field
     * @param string $value
     */
    function clearOpt($field){
        if(isset($this->options[$field])){
           unset($this->options[$field]);
        }
        return $this;
    }

    /**
     * get the existing option
     * @param  string $field
     * @return value
     */
    function getOpt($field){
        if(isset($this->options[$field])){
            return $this->options[$field];
        }else return null;
    }

    /**
     * Return all of the options, mainly for debugging
     * @return array of SOLR options
     */
    public function getOptions(){
        return $this->options;
    }

    /**
     * Manually set the solr url
     * @param string $value http link for solr url, defaults to be the value in the config
     */
    function setUrl($value){
        $this->elasticSearchUrl = $value;
        return $this;
    }

    function setPath($value) {
        $this->path = $value;
        return $this;
    }
}