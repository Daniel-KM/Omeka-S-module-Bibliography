<?php declare(strict_types=1);
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
    'book-series'           => 'fabio:BookSeries',
    'book-set'              => 'bibo:MultiVolumeBook',
    'book-track'            => 'bibo:AudioDocument', // Find more precise.
    'component'             => 'bibo:Excerpt', // Find more precise.
    'dataset'               => 'dctype:Dataset',
    'dissertation'          => 'bibo:Thesis',
    'edited-book'           => 'bibo:EditedBook',
    'journal'               => 'bibo:Journal',
    'journal-article'       => 'bibo:AcademicArticle',
    'journal-issue'         => 'fabio:JournalIssue',
    'journal-volume'        => 'fabio:JournalVolume',
    'monograph'             => 'bibo:Document',
    'other'                 => 'dcterms:BibliographicResource',
    'peer-review'           => 'fabio:ReviewPaper',
    'posted-content'        => 'fabio:Micropost',
    'proceedings'           => 'bibo:Proceedings',
    'proceedings-article'   => 'fabio:ProceedingsPaper',
    'proceedings-series'    => '',
    'reference-book'        => 'bibo:ReferenceSource',
    'reference-entry'       => 'fabio:ReferenceEntry',
    'report'                => 'bibo:Report',
    'report-series'         => '',
    'standard'              => 'bibo:Standard',
    'standard-series'       => '',
];
