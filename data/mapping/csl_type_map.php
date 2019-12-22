<?php
/**
 * Mapping from citation style types to common resource classes.
 *
 * @see https://docs.citationstyles.org/en/1.0.1/specification.html#appendix-iii-types
 */

return [
    'article'                   => 'bibo:Article',
    'article-magazine'          => 'fabio:MagazineArticle',
    'article-newspaper'         => 'fabio:NewspaperArticle',
    'article-journal'           => 'fabio:JournalArticle',
    'bill'                      => 'bibo:LegalDocument',
    'book'                      => 'bibo:Book',
    'broadcast'                 => 'bibo:AudioVisualDocument', // 'mo:Stream',
    'chapter'                   => 'bibo:Chapter',
    'dataset'                   => 'dctype:Dataset',
    'entry'                     => 'fabio:Entry',
    'entry-dictionary'          => 'fabio:ReferenceEntry', // Find more precise.
    'entry-encyclopedia'        => 'fabio:ReferenceEntry',
    'figure'                    => 'fabio:Figure',
    'graphic'                   => 'dctype:StillImage', // Find more precise.
    'interview'                 => 'bibo:Interview',
    'legislation'               => 'bibo:Legislation',
    'legal_case'                => 'bibo:LegalCaseDocument',
    'manuscript'                => 'bibo:Manuscript',
    'map'                       => 'bibo:Map',
    'motion_picture'            => 'dctype:MovingImage',
    'musical_score'             => 'fabio:MusicalComposition', // http://rdaregistry.info/termList/formatNoteMus/1007 ? mo:Score ? mo:PublishedScore ?
    'pamphlet'                  => '', // To check. Short paper?
    'paper-conference'          => 'fabio:ConferencePaper',
    'patent'                    => 'bibo:Patent',
    'post'                      => 'fabio:Micropost',
    'post-weblog'               => 'fabio:BlogPost',
    'personal_communication'    => 'bibo:PersonalCommunication',
    'report'                    => 'bibo:Report',
    'review'                    => 'fabio:Review',
    'review-book'               => 'fabio:BookReview',
    'song'                      => 'fabio:Song',
    'speech'                    => 'dctype:Sound', // Find more precise.
    'thesis'                    => 'bibo:Thesis',
    'treaty'                    => '', // To check.
    'webpage'                   => 'bibo:Webpage',
];
