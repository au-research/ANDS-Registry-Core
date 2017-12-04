<?php
namespace ANDS\Registry\API\Controller;


use ANDS\Authenticator\ORCIDAuthenticator;
use ANDS\Registry\API\Middleware\ValidORCIDSessionMiddleware;
use ANDS\Registry\API\Request;
use ANDS\Registry\Providers\ORCID\ORCIDExport;
use ANDS\Registry\Providers\ORCID\ORCIDProvider;
use ANDS\Registry\Providers\ORCID\ORCIDRecord;
use ANDS\Registry\Suggestors\DatasetORCIDSuggestor;
use ANDS\Repository\RegistryObjectsRepository;
use ANDS\Util\ORCIDAPI;

/**
 * Class ORCIDController
 * Routed from orcids_handler.php
 * @package ANDS\Registry\API\Controller
 */
class ORCIDController extends HTTPController {

    /**
     * GET api/registry/orcids/:id/
     * @param null $id
     * @return mixed
     */
    public function show($id = null)
    {
        return ORCIDRecord::find($id);
    }

    /**
     * GET api/registry/orcids/:id/suggested
     * @param null $id
     * @return array
     */
    public function suggestedDatasets($id = null)
    {
        $orcid = ORCIDRecord::find($id);
        $suggestor = new DatasetORCIDSuggestor();
        $suggestions = $suggestor->suggest($orcid);
        return $suggestions;
    }

    /**
     * GET api/registry/orcids/:id/exports
     * @param null $id
     * @return mixed
     */
    public function exports($id = null)
    {
        $orcid = ORCIDRecord::find($id);
        return $orcid->exports;
    }

    /**
     * GET api/registry/orcids/:id/works
     * Return all works
     * including suggested and already imported
     * TODO: refactor business logic to a dedicated class
     *
     * @param null $id
     * @return array
     */
    public function works($id = null)
    {
        $this->middlewares([ValidORCIDSessionMiddleware::class]);

        $orcid = ORCIDRecord::find($id);
        $orcid->load('exports');

        $works = [];

        // get all suggestions, mark them
        $suggestor = new DatasetORCIDSuggestor();
        $suggestions = $suggestor->suggest($orcid);
        $exportIDs = $orcid->exports->pluck('registry_object_id')->toArray();
        foreach ($suggestions as &$suggestion) {
            $suggestion['in_orcid'] = false;
            if (in_array($suggestion['registry_object_id'], $exportIDs)) {
                $suggestion['in_orcid'] = true;
            }
            $suggestion['type'] = 'suggested';
        }

        $works = array_merge($works, $suggestions);

        foreach ($orcid->exports as $export) {
            $export->load('registryObject');
            $works[] = [
                'registry_object_id' => $export->registry_object_id,
                'title' => $export->registryObject->title,
                'slug' => $export->registryObject->slug,
                'key' => $export->registryObject->key,
                'in_orcid' => $export->inOrcid
            ];
        }

        // item url
        foreach ($works as &$work) {
            // id is used globally in the front end
            $work['id'] = $work['registry_object_id'];
            $work['url'] = portal_url($work['slug']. '/'. $work['registry_object_id']);
        }

        return $works;
    }


    /**
     * PUT|POST api/registry/orcids/:id/works
     */
    public function import()
    {
        $this->middlewares([ValidORCIDSessionMiddleware::class]);

        $ids = Request::value('ids', []);
        $orcidID = ORCIDAuthenticator::getOrcidID();

        // TODO: check orcid ID existence
        $orcid = ORCIDRecord::find($orcidID);
        $orcid->load('exports');

        $result = [];
        foreach ($ids as $id) {

            $record = RegistryObjectsRepository::getRecordByID($id);
            if (!$record) {
                continue;
            }

            $xml = ORCIDProvider::getORCIDXML($record, $orcid);

            $existing = ORCIDExport::where('orcid_id', $orcid->orcid_id)
                ->where('registry_object_id', $record->id)
                ->where('orcid_id', $orcid->orcid_id)
                ->first();

            if ($existing) {
                // if we have an existing, update the data, then sync
                $existing->data = $xml;
                $existing->save();
                ORCIDAPI::sync($existing);
                $existing->load('registryObject');
                $result[] = $existing;
            } else {
                // make a new one, then sync
                $export = ORCIDExport::create([
                    'registry_object_id' => $record->id,
                    'orcid_id' => $orcid->orcid_id,
                    'data' => $xml
                ]);
                ORCIDAPI::sync($export);
                $export->load('registryObject');
                $result[] = $export;
            }
        }

        // reload all exports
        return $result;
    }
}