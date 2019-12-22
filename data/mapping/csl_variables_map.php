<?php
/**
 * Mapping from citation style variables to common properties.
 *
 * @see https://docs.citationstyles.org/en/1.0.1/specification.html#appendix-iv-variables
 */

return [

    // Standard Variables.

    'abstract' => [
        'property' => 'dcterms:abstract',
    ],
    'annote' => [
        'property' => 'skos:note',
    ],
    'archive' => [
        'property' => 'dcterms:isPartOf',
    ],
    'archive_location' => [
        'property' => 'dcterms:source', // See mapping of module ZoteroImport.
    ],
    'archive-place' => [
        // Like event-place?
        'property' => 'dcterms:spatial',
    ],
    'authority' => [
        'property' => 'bibo:issuer',
    ],
    'call-number' => [
        'property' => 'dcterms:identifier',
        'prepend' => 'call-number:',
    ],
    // Useless.
    'citation-label' => [
        'property' => '',
    ],
    // Useless.
    'citation-number' => [
        'property' => '',
    ],
    'collection-title' => [
        'property' => 'dcterms:isPartOf', // rdau:seriesContainerOf
    ],
    'container-title' => [
        'property' => 'dcterms:isPartOf', // rdau:seriesContainerOf
    ],
    'container-title-short' => [
        'property' => '',
    ],
    'dimensions' => [
        'property' => 'dcterms:format',
    ],
    'DOI' => [
        'property' => 'bibo:doi',
    ],
    'event' => [
        'property' => 'bibo:presentedAt',
    ],
    // Like archive-place.
    'event-place' => [
        'property' => 'dcterms:spatial',
    ],
    // Useless.
    'first-reference-note-number' => [
        'property' => '',
    ],
    'genre' => [
        'property' => 'fabio:hasPrimarySubjectTerm', // Module Zotero uses dcterms:type'
    ],
    'ISBN' => [
        'property' => 'bibo:isbn',
    ],
    'ISSN' => [
        'property' => 'bibo:issn',
    ],
    'jurisdiction' => [
        'property' => '',
    ],
    'keyword' => [
        'property' => 'dcterms:subject', // fabio:hasSubjectTerm // prism:keyword
        'multiple' => true,
    ],
    'locator' => [
        'property' => 'bibo:locator',
    ],
    'medium' => [
        'property' => 'dcterms:medium',
    ],
    // Inline note.
    'note' => [
        'property' => '',
    ],
    'original-publisher' => [
        'property' => 'dcterms:publisher',
    ],
    'original-publisher-place' => [
        'property' => 'fabio:hasPlaceOfPublication',
    ],
    'original-title' => [
        'property' => 'dctemrs:title',
    ],
    'page' => [
        'property' => 'bibo:pages',
    ],
    'page-first' => [
        'property' => 'bibo:pageStart',
    ],
    // Not in the official list.
    'page-last' => [
        'property' => 'bibo:pageEnd',
    ],
    'PMCID' => [
        'property' => 'dcterms:identifier',
        'prepend' => 'pmcid:',
    ],
    'PMID' => [
        'property' => 'bibo:pmid',
    ],
    'publisher' => [
        'property' => 'dcterms:publisher',
    ],
    'publisher-place' => [
        'property' => 'fabio:hasPlaceOfPublication',
    ],
    'references' => [
        'property' => 'dcterms:references',
    ],
    'reviewed-title' => [
        'property' => '',
    ],
    'scale' => [
        'property' => 'dcterms:extent', // See mapping of module ZoteroImport.
    ],
    'section' => [
        'property' => 'bibo:section',
    ],
    'source' => [
        'property' => 'dcterms:source',
    ],
    'status' => [
        'property' => 'bibo:status',
    ],
    'title' => [
        'property' => 'dcterms:title',
    ],
    'title-short' => [
        'property' => 'bibo:shortTitle',
    ],
    'URL' => [
        'property' => 'bibo:uri',
    ],
    'version' => [
        'property' => 'fabio:hasSequenceIdentifier', // prism:versionIdentifier
    ],
    'year-suffix' => [
        'property' => 'fabio:hasPublicationYear',
    ],

    // Number Variables.

    'chapter-number' => [
        'property' => 'bibo:chapterNumber',
        'type' => 'number',
    ],
    'collection-number' => [
        'property' => '',
        'type' => 'number',
    ],
    'edition' => [
        'property' => 'bibo:edition',
        'type' => 'number',
    ],
    'issue' => [
        'property' => 'bibo:issue',
        'type' => 'number',
    ],
    'number' => [
        'property' => 'bibo:number',
        'type' => 'number',
    ],
    'number-of-pages' => [
        'property' => 'bibo:numPages',
        'type' => 'number',
    ],
    'number-of-volumes' => [
        'property' => 'bibo:numVolumes',
        'type' => 'number',
    ],
    'volume' => [
        'property' => 'bibo:volume',
        'type' => 'number',
    ],

    // Date Variables.

    // ?
    'accessed' => [
        'property' => 'dcterms:date',
        'type' => 'date',
    ],
    // ?
    'container' => [
        'property' => '',
        'type' => 'date',
    ],
    'event-date' => [
        'property' => 'dcterms:date',
        'type' => 'date',
    ],
    'issued' => [
        'property' => 'dcterms:issued',
        'type' => 'date',
    ],
    'original-date' => [
        'property' => 'dcterms:created',
        'type' => 'date',
    ],
    'submitted' => [
        'property' => 'dcterms:dateSubmitted',
        'type' => 'date',
    ],

    // Name Variables.

    'author' => [
        'property' => 'dcterms:creator',
        'type' => 'name',
    ],
    'collection-editor' => [
        'property' => '',
        'type' => 'name',
    ],
    'composer' => [
        'property' => '',
        'type' => 'name',
    ],
    'container-author' => [
        'property' => '',
        'type' => 'name',
    ],
    'director' => [
        'property' => 'bibo:director',
        'type' => 'name',
    ],
    'editor' => [
        'property' => 'bibo:editor',
        'type' => 'name',
    ],
    'editorial-director' => [
        'property' => '',
        'type' => 'name',
    ],
    'illustrator' => [
        'property' => '',
        'type' => 'name',
    ],
    'interviewer' => [
        'property' => 'bibo:interviewer',
        'type' => 'name',
    ],
    // ?
    'original-author' => [
        'property' => 'dcterms:creator',
        'type' => 'name',
    ],
    'recipient' => [
        'property' => 'bibo:recipient',
        'type' => 'name',
    ],
    'reviewed-author' => [
        'property' => '',
        'type' => 'name',
    ],
    'translator' => [
        'property' => 'bibo:translator',
        'type' => 'name',
    ],
];
