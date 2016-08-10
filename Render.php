<?php
namespace SWD;

class Render
{
    /*
     * Sherman Web Design
     * http://shermanwebdesign.com
     *
     * Author: Ken Sherman
     * Github Repo: https://github.com/kennstphn/SWD-Render
     *
     *********************************************************************
     * ***************************************************************** *
     * *                                                               * *
     * *  distributed under the WTFPL license -- http://www.wtfpl.net  * *
     * *                                                               * *
     * ***************************************************************** *
     *********************************************************************
     */
    protected static $version = '1.0';

    static function get_version($asString = true){
        if ($asString == true){return self::$version;}
        if ($asString == false){return intval(self::$version);}
        throw new \Exception('non-boolean value passed to get_version');
    }

    static function render($object){

        /*
         * This function should only ever return
         *      -- a String
         *      -- an Array
         *      -- an Object
         */

        $emptyString = '';


        //CONTROLLER
        switch ($mytype =self::get_type($object)){
            case 'string':
            case 'integer':
            case 'null':
                $output = (string)$object;

                break;
            case 'array':
                $output = '';
                foreach($object as $arrayItem){
                    $output .= \SWD\Render::render($arrayItem);
                }
                break;
            case 'object':
                // Checks for overrides, then default ->template string
                // notify if template
                try{ $template = self::load_template($object); }
                catch (\Exception $ex) { trigger_error($ex->getMessage(), E_USER_NOTICE); return $emptyString; }

                //replace the temmplate {{placeholders}} with values
                try{ $output = self::propogate_values_to_placeholders($template, $object); }
                catch (\Exception $ex) { trigger_error($ex->getMessage(), E_USER_NOTICE); return $emptyString;}

                break;
            default:
                $output = $emptyString;
                break;
        }
        return $output;
    }
    
    protected static function get_type($incoming){
        if (is_string($incoming)){return 'string';}
        if (is_array($incoming)){return 'array';}
        if (is_object($incoming)){return 'object';}
        if (is_null($incoming)){return 'null';}
        if (is_int($incoming)){return 'integer';}
        if (is_bool($incoming)){return 'boolean';}

        throw new \Exception('Invalid type "'.gettype($incoming).'" passed to \SWD\Render');
    }

    protected static function load_template($object){

        // check for a template override
        $template = self::get_template_override($object);

        // If we don't have a template override set,
        // look for the default template property
        if ($template == false){ $template = self::get_template_default($object); }

        // at this point, dump out - we're not doing anything with this.
        // design docs define this error into a notice. 
        if ($template == false){ throw new \Exception('Missing template property for passed Object of class '. get_class($object));}
        return $template;
    }
    
    protected static function get_template_default($object){


        if (
            isset($object->template)
            && is_string($object->template)
        ){
        
            return $object->template;
        }
        return false;
    }

    protected static function get_template_override($object){

        global $swdTemplates;
        $classOfObject = get_class($object);
        $templateStringOrObjectWith_get_template_method = str_replace('\\', '_', $classOfObject);
        
        if (
            isset($swdTemplates->$templateStringOrObjectWith_get_template_method)
            && is_string($swdTemplates->$templateStringOrObjectWith_get_template_method)
        ){
            return $swdTemplates->$templateStringOrObjectWith_get_template_method;
        }

        if(
            isset($swdTemplates->$templateStringOrObjectWith_get_template_method)
            && is_object($swdTemplates->$templateStringOrObjectWith_get_template_method)
        ){
            return $swdTemplates->$templateStringOrObjectWith_get_template_method->get_template($object);
        }

        return false;
    }

    protected static function extract_placeholders($template, &$placeHoldersArray){

        $numberOfMatches = preg_match_all("/{{[^0-9][0-9a-zA-Z_]*}}/", $template, $placeHoldersArray);

        $placeHoldersArray = $placeHoldersArray[0];

        foreach ($placeHoldersArray as $index=> $placeholder){
            $placeHoldersArray[$index] = self::strip_brackets($placeholder);
        }

        if ($numberOfMatches === 0){ throw new \Exception('No placeholders found in template. Rendering template as-is');}
    }

    protected static function replace_placeholder_with_string($template, $placeholder, $object){

        switch (isset($object->$placeholder)){


            /*
             *
             * This section deals with Variables.
             * Each match should return a str_replace();
             *
             */
            case true:
                $type = self::get_type($object->$placeholder);
                switch($type){
                    case 'object':
                        return str_replace('{{'.$placeholder.'}}',\SWD\Render::render($object->$placeholder), $template);
                        break;
                    case 'array':
                        $arrayOfStrings = array() ;
                        foreach($object->$placeholder as $item){
                            array_push($arrayOfStrings, \SWD\Render::render($item));
                        }
                        return str_replace('{{'.$placeholder.'}}', implode(' ',$arrayOfStrings) , $template);
                        break;
                    case 'string':
                    case 'null':
                    case 'boolean':
                    case 'integer':
                    default:
                        return str_replace('{{'.$placeholder.'}}', $object->$placeholder , $template);
                        break;
                }
                break;

            /*
             *
             * This section deals with functions
             *
             */
            case false:

                if (
                    method_exists($object, $placeholder)
                    && is_callable(array($object, $placeholder))
                ){
                        return str_replace('{{' . $placeholder . '}}',self::render($object->$placeholder()), $template);
                }

                break;
        }

        // RETURN unchanged template string if no placeholder to string matches are found
        return $template;
    }

    protected static function propogate_values_to_placeholders($template, $object){
        if (!isset($int)){$int = 0;}

        self::extract_placeholders($template, $placeholderList);

        foreach($placeholderList as $placeholder){

            $template = self::replace_placeholder_with_string($template, $placeholder, $object);

        }

        return $template;
    }

    protected static function strip_brackets($variableWithBrackets){

        $variableWithOtherBrackets = str_replace('{{', '', $variableWithBrackets);

        $variableWithNoBrackets = str_replace('}}', '', $variableWithOtherBrackets);

        return $variableWithNoBrackets;
    }

}
