<?php

namespace Apiz\Utilities;

use SimpleXMLElement;
use Apiz\Constants\MimeType;

class Parser
{
    /**
     * @param $content
     * @param $mimeType
     * @return array|bool|mixed|SimpleXMLElement|string
     */
    public static function parseByMimeType($content, $mimeType)
    {
        if (in_array($mimeType, MimeType::JSON_TYPES)) {
            return self::parseJson($content, true);
        } elseif (in_array($mimeType, MimeType::XML_TYPES)) {
            return self::parseXml($content);
        } elseif (in_array($mimeType, MimeType::XML_TYPES)) {
            return self::parseYaml($content);
        }

        return $content;
    }

    /**
     * Parse raw contents to JSON
     *
     * @param string $content
     * @param bool $toAssoc
     * @return bool|mixed|string
     */
    private static function parseJson($content, $toAssoc = false)
    {
        $content = json_decode($content, $toAssoc);

        if ( json_last_error() == JSON_ERROR_NONE ) {
            return $content;
        }

        return false;
    }

    /**
     * Parse raw contents to XML
     *
     * @param string $content
     * @return array|SimpleXMLElement
     */
    private static function parseXml($content)
    {
        libxml_use_internal_errors(true);

        $elem = simplexml_load_string($content);

        if ( $elem === false ) {
            return libxml_get_errors();
        }

        return self::xml2array($elem);
    }

    /**
     * @param $data
     * @return array
     */
    private static function xml2array($data)
    {
        $out = [];
        foreach ( (array) $data as $key => $node ) {
            $out[$key] = ( is_object ( $node ) ) ? self::xml2array ( $node ) : $node;
        }

        return $out;
    }

    /**
     * Parse raw contents to Yaml
     *
     * @param string $content
     * @return mixed
     */
    private static function parseYaml($content)
    {
        return yaml_parse($content);
    }
}
