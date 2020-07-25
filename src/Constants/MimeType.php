<?php

namespace Apiz\Constants;

class MimeType
{
    const APPLICATION_JSON = 'application/json';
    const TEXT_JSON = 'text/json';
    const APPLICATION_JAVASCRIPT = 'application/javascript';
    const APPLICATION_XML = 'application/xml';
    const TEXT_XML = 'text/xml';
    const APPLICATION_YAML = 'application/x-yaml';
    const TEXT_YAML = 'text/yaml';

    const JSON_TYPES = [
        self::APPLICATION_JSON,
        self::TEXT_JSON,
        self::APPLICATION_JAVASCRIPT
    ];

    const XML_TYPES = [
        self::APPLICATION_XML,
        self::TEXT_XML
    ];

    const YAML_TYPES = [
        self::APPLICATION_YAML,
        self::TEXT_YAML
    ];
}
