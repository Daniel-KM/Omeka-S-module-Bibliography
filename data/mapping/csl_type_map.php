<?php declare(strict_types=1);
/**
 * Mapping from citation style types to common resource classes.
 *
 * @see https://docs.citationstyles.org/en/stable/specification.html#appendix-iii-types
 *
 * A type is required, so "document" by default.
 */

return [
    'article'                   => 'bibo:Article',
    'article-journal'           => 'fabio:JournalArticle',
    'article-magazine'          => 'fabio:MagazineArticle',
    'article-newspaper'         => 'fabio:NewspaperArticle',
    'bill'                      => 'bibo:Bill',
    'book'                      => 'bibo:Book',
    'broadcast'                 => 'bibo:AudioVisualDocument', // 'mo:Stream',
    'chapter'                   => 'bibo:Chapter',
    'classic'                   => '', // Ancient work.
    'collection'                => 'bibo:Collection',
    'dataset'                   => 'dctype:Dataset',
    'document'                  => 'bibo:Document',
    'entry'                     => 'fabio:Entry',
    'entry-dictionary'          => 'fabio:ReferenceEntry', // Find more precise.
    'entry-encyclopedia'        => 'fabio:ReferenceEntry',
    'event'                     => 'bibo:Event',
    'figure'                    => 'fabio:Figure',
    'graphic'                   => 'dctype:StillImage', // Find more precise.
    'hearing'                   => 'bibo:Hearing',
    'interview'                 => 'bibo:Interview',
    'legal_case'                => 'bibo:LegalCaseDocument',
    'legislation'               => 'bibo:Legislation',
    'manuscript'                => 'bibo:Manuscript',
    'map'                       => 'bibo:Map',
    'motion_picture'            => 'dctype:MovingImage',
    'musical_score'             => 'fabio:MusicalComposition', // http://rdaregistry.info/termList/formatNoteMus/1007 ? mo:Score ? mo:PublishedScore ?
    'pamphlet'                  => '', // To check. Short paper?
    'paper-conference'          => 'fabio:ConferencePaper',
    'patent'                    => 'bibo:Patent',
    'performance'               => 'bibo:Performance',
    'periodical'                => 'bibo:Periodical',
    'personal_communication'    => 'bibo:PersonalCommunication',
    'post'                      => 'fabio:Micropost',
    'post-weblog'               => 'fabio:BlogPost',
    'regulation'                => '', // To check.
    'report'                    => 'bibo:Report',
    'review'                    => 'fabio:Review',
    'review-book'               => 'fabio:BookReview',
    'software'                  => 'dctype:Software',
    'song'                      => 'fabio:Song',
    'speech'                    => 'fabio:Oration',
    'standard'                  => 'dcterms:Standard',
    'thesis'                    => 'bibo:Thesis',
    'treaty'                    => '', // To check.
    'webpage'                   => 'bibo:Webpage',
];
