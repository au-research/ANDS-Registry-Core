<?php
namespace ANDS\API\Registry\Handler;
use \Exception as Exception;

/**
 * Handles registry/lookup
 * Lookup a registry object based on anything
 * Used mainly for registry widget
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
class LookupHandler extends Handler
{

    /**
     * Handles registry/lookup
     * @return array
     */
    public function handle()
    {
        $this->ci->load->model('registry/registry_object/registry_objects', 'ro');

        $query = $this->ci->input->get('q');
        $query = urldecode($query);

        $ro = $this->ci->ro->getByID($query);
        if (!$ro) {
            $ro = $this->ci->ro->getBySlug($query);
        }

        if (!$ro) {
            $ro = $this->ci->ro->getPublishedByKey($query);
        }

        if (!$ro) {
            throw new Exception('No Registry Object Found');
        }
        $r['status'] = 0;
        $r['result'] = array(
            'id' => $ro->id,
            'rda_link' => portal_url($ro->slug),
            'key' => $ro->key,
            'slug' => $ro->slug,
            'title' => $ro->title,
            'class' => $ro->class,
            'type' => $ro->type,
            'group' => $ro->group,
        );
        if ($ro->getMetadata('the_description')) {
            $r['result']['description'] = $ro->getMetadata('the_description');
        } else {
            $r['result']['description'] = '';
        }

        return $r;
    }
}
