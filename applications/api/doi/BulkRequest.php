<?php


namespace ANDS\API\DOI;


use Illuminate\Database\Eloquent\Model;

/**
 * Class BulkRequest
 * Minh Duc Nguyen <minh.nguyen@ardc.edu.au>
 * @package ANDS\API\DOI
 */
class BulkRequest extends Model
{
    protected $table = 'bulk_requests';
    public $timestamps = false;

    /**
     * Return all requests from this BulkRequest
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function requests()
    {
        return $this->hasMany(Bulk::class, 'bulk_id', 'id');
    }

    /**
     * Return the counts attribute
     * accessible by $this->counts
     * @return array
     */
    public function getCountsAttribute()
    {
        $result = [
            'TOTAL' => Bulk::where('bulk_id', $this->id)->count(),
            'PENDING' => $this->getBulkByStatus('PENDING')->count(),
            'COMPLETED' => $this->getBulkByStatus('COMPLETED')->count(),
            'ERROR' => $this->getBulkByStatus('ERROR')->count()
        ];

        $result['PROGRESS'] = 0;
        if ($result['TOTAL'] != 0) {
            $result['PROGRESS'] = round(($result['COMPLETED'] + $result['ERROR']) / $result['TOTAL'] * 100, 2);
        }
        return $result;
    }

    /**
     * Check if the Bulk Request is done
     *
     * @return bool
     */
    public function isDone()
    {
        $pendingCount = $this->getBulkByStatus('PENDING')->count();
        if ($pendingCount === 0) {
            return true;
        }
        return false;
    }

    /**
     * Return all requests by status
     * TODO: refactor into BulkRepository
     * @param $status
     * @return mixed
     */
    public function getBulkByStatus($status)
    {
        return Bulk::where('bulk_id', $this->id)->where('status', $status);
    }

    /**
     * Convert the model instance to an array.
     *
     * @return array
     */
    public function toArray()
    {
        $array = parent::toArray();
        $array['counts'] = $this->counts;
        return $array;
    }
}