<?php

namespace ANDS\DOI\Model;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'client_name',
        'client_contact_name',
        'ip_address',
        'app_id',
        'client_contact_email',
        'datacite_symbol',
        'shared_secret',
        'test_app_id',
        'test_shared_secret',
        'mode',
        'status',
        'repository_symbol',
        'in_production'
    ];

    public $timestamps = false;

    /**
     * The table of the model
     * @var string
     */
    protected $table = "doi_client";

    /**
     * The primary key of the model,
     * used for DataciteClient::find() method
     *
     * @var string
     */
    protected $primaryKey = "client_id";

    /**
     * Returns all the domain owned by this client
     */

    public function domains()
    {
        return $this->hasMany(
            ClientDomain::class, "client_id", "client_id"
        );
    }
    

    public function addDomains($domains){
        $domArray = explode(",", $domains);
        foreach ($domArray as $d){
            $this->addDomain($d);
        }
    }


    public function addDomain($domain){
        $domain = trim($domain);
        if($domain == '')
            return;
        $dm = ClientDomain::where("client_domain", $domain)
            ->where("client_id", $this->client_id)->first();
        if ($dm != null) {
            return;
        }
        $this->domains()->save(new ClientDomain(["client_domain" => $domain]));
    }


    public function removeClientDomains(){
        ClientDomain::where("client_id", $this->client_id)->delete();
    }
    /**
     * Returns all the prefixes assigned to this client
     */


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * prefixes are a many to many relationships as of R28
     * client prefixes is to capture client's prefix relationships
     *
     */
    public function prefixes()
    {
        return $this->hasMany(
            ClientPrefixes::class, "client_id", "client_id"
        );
    }

    /**
     * @return mixed
     * each client can have One and only one active prefix that is used when minting NEW DOI
     *
     */
    public function getActivePrefix($mode = 'prod')
    {
        if($mode == 'test'){
            $is_test = 1;
        }else{
            $is_test = 0;
        }
        return ClientPrefixes::where("client_id", $this->client_id)->where(["active"=>true])->where(["is_test"=>$is_test])->first();
    }

    /**
     * @param $prefixes
     * not used outside of testing
     */
    public function addClientPrefixes($prefixes){
        $prefixArray = explode(",", $prefixes);
        foreach ($prefixArray as $p){
            $this->addClientPrefix($p);
        }
    }

    /**
     * @param $prefix_value
     * @return bool
     * tests if client has already been assigned this prefix
     *
     */
    public function hasPrefix($prefix_value){
        $clientPrefixes = ClientPrefixes::where("client_id", $this->client_id)->get();
        foreach ($clientPrefixes as $clientPrefix) {
            if($prefix_value ==  $clientPrefix->prefix->prefix_value)
                return true;
        }
        return false;
    }

    /** add prefix to client */

    /**
     * @param $prefix_value
     * @param bool $active
     *
     * assigns a prefix to a client
     */
    public function addClientPrefix($prefix_value, $mode = 'prod',$active = true){

        $prefix = null;
        $prefix_value = trim($prefix_value);
        if($prefix_value == null || $prefix_value == '')
            return;

        //set all other prefixes for this client as non active if this prefix is the active one
        if($mode == 'test'){
            $is_test = 1;
        }else{
            $is_test = 0;
        }
        if($active){
            ClientPrefixes::where("client_id", $this->client_id)->where("is_test", $is_test)->update(["active"=>false]);
        }

        try {
            // Get the Prefix if it exists
            $prefix = Prefix::where("prefix_value", $prefix_value)->first();
            if($prefix) {
                //if this prefix is already assigned to this client do nothing)
                $cp = ClientPrefixes::where("prefix_id", $prefix->id)
                    ->where("client_id", $this->client_id)->first();
                if ($cp != null) {
                    ClientPrefixes::where("prefix_id", $prefix->id)
                        ->where("client_id", $this->client_id)->update(["active"=>$active,"is_test" => $is_test]);
                    return;
                }
            }

        }
        catch(Exception $e)
        {}
        // should never happen since all prefixes must be preloaded
        if($prefix == null)// create a new prefix and assign it to the Client
        {
            $prefix = new Prefix(["prefix_value" => $prefix_value,"is_test" => $is_test]);
            $prefix->save();
        }

        $this->prefixes()->save(new ClientPrefixes(["prefix_id" => $prefix->id, "active"=>$active, "is_test"=>$is_test]));
    }

    /**
     * @param $prefix_value
     * not used outside of testing
     */
    public function removeClientPrefix($prefix_value){
        $prefix = Prefix::where("prefix_value", $prefix_value)->first();
        if($prefix == null){
            return;
        }
        ClientPrefixes::where("client_id", $this->client_id)->where("prefix_id", $prefix->id)->delete();
    }

    /**
     *
     * also not used outside of testing
     */
    public function removeClientPrefixes(){
        ClientPrefixes::where("client_id", $this->client_id)->delete();
    }
    
}