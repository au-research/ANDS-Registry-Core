<?php

namespace ANDS\Registry\API\Controller;

use ANDS\Registry\API\Middleware\IPRestrictionMiddleware;
use ANDS\Registry\API\Request;
use ANDS\Registry\Backup\BackupRepository;

class BackupsAPI extends HTTPController
{

    public function index()
    {
        $backups = BackupRepository::getAllBackups();
        return collect($backups)->map(function($backup) {
            return $backup->toMetaArray();
        })->toArray();
    }

    public function show($id)
    {
        $backup = BackupRepository::getBackupById($id);
        return $backup->toMetaArray();
    }

    public function store()
    {
        $this->middlewares([IPRestrictionMiddleware::class]);
        $this->validate(['id', 'dataSourceIds']);

        $id = Request::get('id');
        $dataSourceIds = explode(',',Request::get('dataSourceIds'));

        $options = [
            'includeGraphs' => Request::get('includeGraphs') != "0",
            'includePortalIndex' => Request::get('includePortalIndex') != "0" ,
            'includeRelationshipsIndex' => Request::get('includeRelationshipsIndex') != "0"
        ];

        return BackupRepository::create($id, $dataSourceIds, $options);
    }

    public function restore($id)
    {
        $this->middlewares([IPRestrictionMiddleware::class]);

        $options = [
            'includeGraphs' => Request::get('includeGraphs') != "0",
            'includePortalIndex' => Request::get('includePortalIndex') != "0" ,
            'includeRelationshipsIndex' => Request::get('includeRelationshipsIndex') != "0"
        ];

        return BackupRepository::restore($id, $options);
    }
}