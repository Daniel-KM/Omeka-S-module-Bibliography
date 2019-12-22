<?php
/**
 * One to one mapping from Crossref types to rdf types.
 *
 * @see https://api.crossref.org/types
 */

return [
    'book'                  => 'bibo:Book',
    'book-chapter'          => 'bibo:Chapter',
    'book-part'             => 'bibo:DocumentPart',
    'book-section'          => 'bibo:BookSection',
    'book-series'           => '',
    'book-set'              => 'bibo:MultiVolumeBook',
    'book-track'            => 'bibo:AudioDocument', // Find more precise.
    'component'             => 'bibo:Excerpt', // Find more precise.
    'dataset'               => 'dctype:Dataset',
    'dissertation'          => 'bibo:Thesis',
    'edited-book'           => 'bibo:EditedBook',
    'journal'               => 'bibo:Journal',
    'journal-article'       => 'bibo:AcademicArticle',
    'journal-issue'         => '',
    'journal-volume'        => '',
    'monograph'             => 'bibo:Document',
    'other'                 => 'dcterms:BibliographicResource',
    'peer-review'           => '',
    'posted-content'        => '',
    'proceedings'           => 'bibo:Proceedings',
    'proceedings-article'   => '',
    'proceedings-series'    => '',
    'reference-book'        => 'bibo:ReferenceSource',
    'reference-entry'       => '',
    'report'                => 'bibo:Report',
    'report-series'         => '',
    'standard'              => 'bibo:Standard',
    'standard-series'       => '',
];
