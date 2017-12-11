<?php
namespace ANDS\Registry\API\Controller;


use ANDS\API\Task\ImportSubTask\ProcessRelationships;
use ANDS\Authenticator\ORCIDAuthenticator;
use ANDS\Registry\API\Middleware\ValidORCIDSessionMiddleware;
use ANDS\Registry\API\Request;
use ANDS\Registry\Providers\ORCID\ORCIDExport;
use ANDS\Registry\Providers\ORCID\ORCIDProvider;
use ANDS\Registry\Providers\ORCID\ORCIDRecord;
use ANDS\Registry\Suggestors\DatasetORCIDSuggestor;
use ANDS\Repository\RegistryObjectsRepository;
use ANDS\Util\Config;
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
        $orcid = ORCIDRecord::find($id);
        $orcid->record_data = json_decode($orcid->record_data, true);
        return $orcid;
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

        // sanity check for syncing
        ORCIDAPI::syncRecord($orcid);

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
        $suggestedIDs = collect($suggestions)->pluck('registry_object_id')->toArray();

        $works = array_merge($works, $suggestions);

        foreach ($orcid->exports as $export) {
            if (in_array($export->registry_object_id, $suggestedIDs)) {
                continue;
            }
            $export->load('registryObject');
            $works[] = [
                'registry_object_id' => $export->registry_object_id,
                'title' => $export->registryObject->title,
                'slug' => $export->registryObject->slug,
                'key' => $export->registryObject->key,
                'in_orcid' => $export->inOrcid
            ];
        }

        // fixed value for front end
        $works = collect($works)->map(function($work) {
            $work['id'] = $work['registry_object_id'];
            $work['url'] = portal_url($work['slug']. '/'. $work['registry_object_id']);
            return $work;
        })->toArray();

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

            $export = ORCIDExport::where('orcid_id', $orcid->orcid_id)
                ->where('registry_object_id', $record->id)
                ->where('orcid_id', $orcid->orcid_id)
                ->first();

            if (!$export) {
                $export = ORCIDExport::create([
                    'registry_object_id' => $record->id,
                    'orcid_id' => $orcid->orcid_id
                ]);
            }

            try {
                $xml = ORCIDProvider::getORCIDXML($record, $orcid);
                $export->data = $xml;

                $export->save();
                ORCIDAPI::sync($export);
                $export->load('registryObject');
                $result[] = $export;
            } catch (\Exception $e) {
                $export->response = json_encode([
                    'error' => 'internal',
                    'error_description' => get_exception_msg($e),
                    'user-message' => "An error has occured while linking record {$record->title}($record->id)"
                ], true);
                $export->save();
                $result[] = $export;
            }

        }

        // reload all exports
        return $result;
    }

    public function destroyWorks($orcidID, $registryObjectID)
    {
        $this->middlewares([ValidORCIDSessionMiddleware::class]);

        // sanity check
        $orcid = ORCIDRecord::find($orcidID);
        if (!$orcid) {
            throw new \Exception("ORCID {$orcidID} not found");
        }

        $export = ORCIDExport::where('registry_object_id', $registryObjectID)
            ->where('orcid_id', $orcidID)
            ->first();
        if (!$export) {
            throw new \Exception("ORCID Work for record {$registryObjectID} not found");
        }

        // delete from ORCID
        if ($export->inOrcid) {
            ORCIDAPI::delete($orcid, $export);
        }


        // then delete locally
        $export->delete();

        // should be a good response
        return ['deleted' => true];
    }

    public function sync($orcidID)
    {
        $orcid = ORCIDRecord::find($orcidID);
        ORCIDAPI::syncRecord($orcid);

        return ['synced' => true];
    }
}