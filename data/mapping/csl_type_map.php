<?php
/**
 * Mapping from citation style types to common resource classes.
 *
 * @see https://docs.citationstyles.org/en/1.0.1/specification.html#appendix-iii-types
 */

return [
    'article'                   => 'bibo:Article',
    'article-magazine'          => '',
    'article-newspaper'         => '',
    'article-journal'           => '',
    'bill'                      => 'bibo:LegalDocument',
    'book'                      => 'bibo:Book',
    'broadcast'                 => 'bibo:AudioVisualDocument',
    'chapter'                   => 'bibo:Chapter',
    'dataset'                   => 'dctype:Dataset',
    'entry'                     => '',
    'entry-dictionary'          => '',
    'entry-encyclopedia'        => '',
    'figure'                    => '',
    'graphic'                   => 'dctype:StillImage', // Find more precise.
    'interview'                 => 'bibo:Interview',
    'legislation'               => 'bibo:Legislation',
    'legal_case'                => 'bibo:LegalCaseDocument',
    'manuscript'                => 'bibo:Manuscript',
    'map'                       => 'bibo:Map',
    'motion_picture'            => 'dctype:MovingImage',
    'musical_score'             => '',
    'pamphlet'                  => '',
    'paper-conference'          => '',
    'patent'                    => 'bibo:Patent',
    'post'                      => '',
    'post-weblog'               => '',
    'personal_communication'    => 'bibo:PersonalCommunication',
    'report'                    => 'bibo:Report',
    'review'                    => '',
    'review-book'               => '',
    'song'                      => '',
    'speech'                    => 'dctype:Sound', // Find more precise.
    'thesis'                    => 'bibo:Thesis',
    'treaty'                    => '',
    'webpage'                   => 'bibo:Webpage',
];
