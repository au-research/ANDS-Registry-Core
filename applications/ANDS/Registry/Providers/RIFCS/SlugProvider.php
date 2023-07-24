<?php


namespace ANDS\Registry\Providers\RIFCS;


use ANDS\Registry\Providers\RIFCSProvider;
use ANDS\RegistryObject;
use ANDS\Util\StrUtil;

class SlugProvider implements RIFCSProvider
{
    const maxLength = 200;
    const maxNumWords = 5;

    public static function process(RegistryObject $record)
    {
        $slug = self::get($record);
        $record->slug = $slug;
        $record->save();
    }

    public static function get(RegistryObject $record)
    {
        $title = $record->title;

        //remove stopwords
        $simplifiedTitle = StrUtil::removeStopWords($title);
        $result = strtolower($simplifiedTitle);

        if (trim($simplifiedTitle) == "") {
            $result = strtolower($title);
        }

        // remove weird chars and replace spaces with blanks
        $result = preg_replace("/[^a-z0-9\s-]/", "", $result);
        $result = trim(preg_replace("/[\s-]+/", " ", $result));
        // $result = trim(substr($result, 0, self::maxLength));

        $result = explode(' ', $result);

        $possibleSlug = implode('-', $result);

        // if the slug still consist of emptiness
        // group-id
        if (count($result) == 0 || trim($possibleSlug) == "") {
            $result = [
                url_title(strtolower($record->group)), $record->id
            ];
        }

        if (sizeof($result) > self::maxNumWords) {
            $first_three = array_slice($result, 0, 3);
            $last_two = array_slice($result, -2, 2, true);
            $result = array_merge($first_three, $last_two);
            $result = implode('-', $result);
        } else {
            $result = implode('-', $result);
        }

        return $result;
    }
}