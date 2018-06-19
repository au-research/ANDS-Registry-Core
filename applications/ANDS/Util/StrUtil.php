<?php


namespace ANDS\Util;


class StrUtil
{
    /**
     * Sanitize a string
     * Remove bad characters that breaks exporting
     *
     * @param $str
     * @return mixed
     */
    public static function sanitize($str)
    {
        $str = str_replace([',', '"', ';', '\t', '&#xA'], '', $str);
        $str = static::removeNewlines($str);
        $str = trim($str);
        return $str;
    }

    public static function removeNewlines($str)
    {
        return preg_replace( "/\r|\n/", " ", $str);
    }

    /**
     * Remove stop words defined in this file
     * For indexing purposes
     *
     * @param $str
     * @return mixed
     */
    public static function removeStopWords($str)
    {
        return str_replace(self::stopWords, "", $str);
    }

    /**
     * Takes a singular word and makes it plural
     *
     * @param $str
     * @param bool $force
     * @return mixed|string
     */
    public static function plural($str, $force = FALSE)
    {
        $result = strval($str);

        $plural_rules = array(
            '/^(ox)$/'                 => '\1\2en',     // ox
            '/([m|l])ouse$/'           => '\1ice',      // mouse, louse
            '/(matr|vert|ind)ix|ex$/'  => '\1ices',     // matrix, vertex, index
            '/(x|ch|ss|sh)$/'          => '\1es',       // search, switch, fix, box, process, address
            '/([^aeiouy]|qu)y$/'       => '\1ies',      // query, ability, agency
            '/(hive)$/'                => '\1s',        // archive, hive
            '/(?:([^f])fe|([lr])f)$/'  => '\1\2ves',    // half, safe, wife
            '/sis$/'                   => 'ses',        // basis, diagnosis
            '/([ti])um$/'              => '\1a',        // datum, medium
            '/(p)erson$/'              => '\1eople',    // person, salesperson
            '/(m)an$/'                 => '\1en',       // man, woman, spokesman
            '/(c)hild$/'               => '\1hildren',  // child
            '/(buffal|tomat)o$/'       => '\1\2oes',    // buffalo, tomato
            '/(bu|campu)s$/'           => '\1\2ses',    // bus, campus
            '/(alias|status|virus)/'   => '\1es',       // alias
            '/(octop)us$/'             => '\1i',        // octopus
            '/(ax|cris|test)is$/'      => '\1es',       // axis, crisis
            '/s$/'                     => 's',          // no change (compatibility)
            '/$/'                      => 's',
        );

        foreach ($plural_rules as $rule => $replacement)
        {
            if (preg_match($rule, $result))
            {
                $result = preg_replace($rule, $replacement, $result);
                break;
            }
        }

        return $result;
    }

    /* From Joel Benn, 2014 */
    const stopWords = [
        ' a ',
        ' about ',
        ' above ',
        ' above ',
        ' across ',
        ' after ',
        ' afterwards ',
        ' again ',
        ' against ',
        ' all ',
        ' almost ',
        ' alone ',
        ' along ',
        ' already ',
        ' also ',
        ' although ',
        ' always ',
        ' am ',
        ' among ',
        ' amongst ',
        ' amoungst ',
        ' amount”,  “an ',
        ' and ',
        ' another ',
        ' any ',
        ' anyhow ',
        ' anyone ',
        ' anything ',
        ' anyway ',
        ' anywhere ',
        ' are ',
        ' around ',
        ' as ',
        ' at ',
        ' back ',
        ' be ',
        ' became ',
        ' because ',
        ' become ',
        ' becomes ',
        ' becoming ',
        ' been ',
        ' before ',
        ' beforehand ',
        ' behind ',
        ' being ',
        ' below ',
        ' beside ',
        ' besides ',
        ' between ',
        ' beyond ',
        ' bill ',
        ' both ',
        ' bottom ',
        ' but ',
        ' by ',
        ' call ',
        ' can ',
        ' cannot ',
        ' cant ',
        ' co ',
        ' con ',
        ' could ',
        ' couldnt ',
        ' cry ',
        ' de ',
        ' describe ',
        ' detail ',
        ' do ',
        ' done ',
        ' down ',
        ' due ',
        ' during ',
        ' each ',
        ' eg ',
        ' eight ',
        ' either ',
        ' eleven”,”else ',
        ' elsewhere ',
        ' empty ',
        ' enough ',
        ' etc ',
        ' even ',
        ' ever ',
        ' every ',
        ' everyone ',
        ' everything ',
        ' everywhere ',
        ' except ',
        ' few ',
        ' fifteen ',
        ' fify ',
        ' fill ',
        ' find ',
        ' fire ',
        ' first ',
        ' five ',
        ' for ',
        ' former ',
        ' formerly ',
        ' forty ',
        ' found ',
        ' four ',
        ' from ',
        ' front ',
        ' full ',
        ' further ',
        ' get ',
        ' give ',
        ' go ',
        ' had ',
        ' has ',
        ' hasnt ',
        ' have ',
        ' he ',
        ' hence ',
        ' her ',
        ' here ',
        ' hereafter ',
        ' hereby ',
        ' herein ',
        ' hereupon ',
        ' hers ',
        ' herself ',
        ' him ',
        ' himself ',
        ' his ',
        ' how ',
        ' however ',
        ' hundred ',
        ' ie ',
        ' if ',
        ' in ',
        ' inc ',
        ' indeed ',
        ' interest ',
        ' into ',
        ' is ',
        ' it ',
        ' its ',
        ' itself ',
        ' keep ',
        ' last ',
        ' latter ',
        ' latterly ',
        ' least ',
        ' less ',
        ' ltd ',
        ' made ',
        ' many ',
        ' may ',
        ' me ',
        ' meanwhile ',
        ' might ',
        ' mill ',
        ' mine ',
        ' more ',
        ' moreover ',
        ' most ',
        ' mostly ',
        ' move ',
        ' much ',
        ' must ',
        ' my ',
        ' myself ',
        ' name ',
        ' namely ',
        ' neither ',
        ' never ',
        ' nevertheless ',
        ' next ',
        ' nine ',
        ' no ',
        ' nobody ',
        ' none ',
        ' noone ',
        ' nor ',
        ' not ',
        ' nothing ',
        ' now ',
        ' nowhere ',
        ' of ',
        ' off ',
        ' often ',
        ' on ',
        ' once ',
        ' one ',
        ' only ',
        ' onto ',
        ' or ',
        ' other ',
        ' others ',
        ' otherwise ',
        ' our ',
        ' ours ',
        ' ourselves ',
        ' out ',
        ' over ',
        ' own”,”part ',
        ' per ',
        ' perhaps ',
        ' please ',
        ' put ',
        ' rather ',
        ' re ',
        ' same ',
        ' see ',
        ' seem ',
        ' seemed ',
        ' seeming ',
        ' seems ',
        ' serious ',
        ' several ',
        ' she ',
        ' should ',
        ' show ',
        ' side ',
        ' since ',
        ' sincere ',
        ' six ',
        ' sixty ',
        ' so ',
        ' some ',
        ' somehow ',
        ' someone ',
        ' something ',
        ' sometime ',
        ' sometimes ',
        ' somewhere ',
        ' still ',
        ' such ',
        ' system ',
        ' take ',
        ' ten ',
        ' than ',
        ' that ',
        ' the ',
        ' their ',
        ' them ',
        ' themselves ',
        ' then ',
        ' thence ',
        ' there ',
        ' thereafter ',
        ' thereby ',
        ' therefore ',
        ' therein ',
        ' thereupon ',
        ' these ',
        ' they ',
        ' thickv ',
        ' thin ',
        ' third ',
        ' this ',
        ' those ',
        ' though ',
        ' three ',
        ' through ',
        ' throughout ',
        ' thru ',
        ' thus ',
        ' to ',
        ' together ',
        ' too ',
        ' top ',
        ' toward ',
        ' towards ',
        ' twelve ',
        ' twenty ',
        ' two ',
        ' un ',
        ' under ',
        ' until ',
        ' up ',
        ' upon ',
        ' us ',
        ' very ',
        ' via ',
        ' was ',
        ' we ',
        ' well ',
        ' were ',
        ' what ',
        ' whatever ',
        ' when ',
        ' whence ',
        ' whenever ',
        ' where ',
        ' whereafter ',
        ' whereas ',
        ' whereby ',
        ' wherein ',
        ' whereupon ',
        ' wherever ',
        ' whether ',
        ' which ',
        ' while ',
        ' whither ',
        ' who ',
        ' whoever ',
        ' whole ',
        ' whom ',
        ' whose ',
        ' why ',
        ' will ',
        ' with ',
        ' within ',
        ' without ',
        ' would ',
        ' yet ',
        ' you ',
        ' your ',
        ' yours ',
        ' yourself ',
        ' yourselves ',
        ' the ',
        ' data ',
        ' record '
    ];
}