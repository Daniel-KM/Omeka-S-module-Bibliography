<?php
/**
 * Mapping from common resource classes to citation style types.
 *
 * @see https://docs.citationstyles.org/en/1.0.1/specification.html#appendix-iii-types
 */

return [
    'bibo:AcademicArticle'          => 'article-journal',
    'bibo:Article'                  => 'article-newspaper',
    'bibo:AudioDocument'            => 'speech', // song? interview? broadcast?
    'bibo:AudioVisualDocument'      => 'motion_picture',
    'bibo:Bill'                     => 'bill',
    'bibo:Book'                     => 'book',
    'bibo:BookSection'              => 'chapter',
    'bibo:Brief'                    => 'personal_communication', // To check.
    'bibo:Chapter'                  => 'chapter',
    'bibo:Code'                     => 'legislation',
    'bibo:Collection'               => 'dataset',
    'bibo:CollectedDocument'        => '', // To check.
    'bibo:Conference'               => '', // To check.
    'bibo:CourtReporter'            => 'article', // To check.
    'bibo:Document'                 => '',
    'bibo:DocumentPart'             => '', // To check.
    'bibo:DocumentStatus'           => '', // To check.
    'bibo:EditedBook'               => 'book',
    'bibo:Email'                    => 'post',
    'bibo:Event'                    => '', // To check.
    'bibo:Excerpt'                  => '', // To check.
    'bibo:Film'                     => 'motion_picture',
    'bibo:Hearing'                  => 'speech',
    'bibo:Image'                    => 'graphic', // figure?
    'bibo:Interview'                => 'interview',
    'bibo:Issue'                    => '', // To check.
    'bibo:Journal'                  => '', // To check.
    'bibo:LegalCaseDocument'        => 'legal_case',
    'bibo:LegalDecision'            => 'legal_case', // To check.
    'bibo:LegalDocument'            => 'legal_case', // To check.
    'bibo:Legislation'              => 'legislation',
    'bibo:Letter'                   => 'personal_communication',
    'bibo:Magazine'                 => '', // To check.
    'bibo:Manual'                   => 'book', // To check.
    'bibo:Manuscript'               => 'manuscript',
    'bibo:Map'                      => 'map',
    'bibo:MultiVolumeBook'          => 'book', // To check.
    'bibo:Newspaper'                => '', // To check.
    'bibo:Note'                     => 'personal_communication', // To check.
    'bibo:Patent'                   => 'patent',
    'bibo:Performance'              => '', // To check.
    'bibo:Periodical'               => '', // To check.
    'bibo:PersonalCommunication'    => 'personal_communication',
    'bibo:PersonalCommunicationDocument' => 'personal_communication', // To check.
    'bibo:Proceedings'              => 'paper-conference',
    'bibo:Quote'                    => '', // To check.
    'bibo:ReferenceSource'          => '', // To check.
    'bibo:Report'                   => 'report',
    'bibo:Series'                   => '', // To check.
    'bibo:Slide'                    => '', // To check.
    'bibo:Slideshow'                => '',
    'bibo:Standard'                 => '', // To check.
    'bibo:Statute'                  => '',
    'bibo:Thesis'                   => 'thesis',
    'bibo:ThesisDegree'             => '', // To check.
    'bibo:Webpage'                  => 'webpage',
    'bibo:Website'                  => 'webpage', // To check.
    'bibo:Workshop'                 => '', // To check.

    'dcterms:Agent'                 => '', // To check.
    'dcterms:AgentClass'            => '', // To check.
    'dcterms:BibliographicResource' => '', // To check.
    'dcterms:FileFormat'            => '', // To check.
    'dcterms:Frequency'             => '', // To check.
    'dcterms:Jurisdiction'          => '', // To check.
    'dcterms:LicenseDocument'       => '', // To check.
    'dcterms:LinguisticSystem'      => '', // To check.
    'dcterms:Location'              => '', // To check.
    'dcterms:LocationPeriodOrJurisdiction' => '', // To check.
    'dcterms:MediaType'             => '', // To check.
    'dcterms:MediaTypeOrExtent'     => '', // To check.
    'dcterms:MethodOfAccrual'       => '', // To check.
    'dcterms:MethodOfInstruction'   => '', // To check.
    'dcterms:PeriodOfTime'          => '', // To check.
    'dcterms:PhysicalMedium'        => '', // To check.
    'dcterms:PhysicalResource'      => '', // To check.
    'dcterms:Policy'                => '', // To check.
    'dcterms:ProvenanceStatement'   => '', // To check.
    'dcterms:RightsStatement'       => '', // To check.
    'dcterms:SizeOrDuration'        => '', // To check.
    'dcterms:Standard'              => '', // To check.

    'dctype:Collection'             => 'dataset',
    'dctype:Dataset'                => 'dataset',
    'dctype:Event'                  => '', // To check.
    'dctype:Image'                  => 'graphic', // figure?
    'dctype:InteractiveResource'    => '', // To check.
    'dctype:MovingImage'            => 'motion_picture',
    'dctype:PhysicalObject'         => '', // To check.
    'dctype:Service'                => '', // To check.
    'dctype:Software'               => '',
    'dctype:Sound'                  => 'speech', // song? interview? broadcast?
    'dctype:StillImage'             => 'graphic', // figure?
    'dctype:Text'                   => 'personal_communication', // To check.
];
