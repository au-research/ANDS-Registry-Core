<?php
namespace ANDS\Registry\API\Controller;


use ANDS\Authenticator\ORCIDAuthenticator;
use ANDS\Registry\API\Middleware\ValidORCIDSessionMiddleware;
use ANDS\Registry\API\Request;
use ANDS\Registry\Providers\ORCID\ORCIDExport;
use ANDS\Registry\Providers\ORCID\ORCIDExportRepository;
use ANDS\Registry\Providers\ORCID\ORCIDProvider;
use ANDS\Registry\Providers\ORCID\ORCIDRecord;
use ANDS\Registry\Suggestors\DatasetORCIDSuggestor;
use ANDS\Repository\RegistryObjectsRepository;

class ORCIDController extends HTTPController {

    public function show($id = null)
    {
        return ORCIDRecord::find($id);
    }

    public function suggestedDatasets($id = null)
    {
        $orcid = ORCIDRecord::find($id);
        $suggestor = new DatasetORCIDSuggestor();
        $suggestions = $suggestor->suggest($orcid);
        return $suggestions;
    }

    public function exports($id = null)
    {
        $orcid = ORCIDRecord::find($id);
        return $orcid->exports;
    }

    /**
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

        // get all suggestions
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

        foreach ($ids as $id) {
            $record = RegistryObjectsRepository::getRecordByID($id);
            if (!$record) {
                return;
            }
            $xml = ORCIDProvider::getORCIDXML($record, $orcid);

            // if we have an existing, update the data
            $existing = ORCIDExport::where('orcid_id', $orcid->orcid_id)->where('registry_object_id', $record->id)->first();
            if (!$existing) {
                ORCIDExport::create([
                    'registry_object_id' => $record->id,
                    'orcid_id' => $orcid->orcid_id,
                    'data' => $xml
                ]);
            } else {
                $existing->save();
            }
        }

        // reload all exports
        $orcid->load('exports');
        return $orcid->exports;
    }
}