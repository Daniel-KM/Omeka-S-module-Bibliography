<?php
namespace Bibliography\View\Helper;

use Geissler\Converter\Converter;
use Geissler\Converter\Standard\RIS\RIS;
use Geissler\Converter\Standard\CSL\CSL;

use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Zend\View\Helper\AbstractHelper;

class RdfToRis extends AbstractHelper
{
    /**
     * @var AbstractResourceEntityRepresentation
     */
    protected $resource;

    /**
     * @var array
     */
    protected $defaults;

    /**
     * Convert a resource into the format bibtex.
     *
     * Currently, the process uses the csl converter.
     * @uses \Bibliography\View\Helper\RdfToCsl
     * @todo Use a direct conversion from Omeka to ris via another library.
     *
     * @param AbstractResourceEntityRepresentation $resource
     * @param array $defaults Not used currently. See RdfToCsl.
     * @return \stdClass
     */
    public function __invoke(AbstractResourceEntityRepresentation $resource, array $defaults = [])
    {
        $this->resource = $resource;
        $this->defaults = $defaults;

        // Currently, the process
        $view = $this->getView();

        // Convert to array to manage the library geissler/converter.
        $csl = $view->rdfToCsl($resource, $defaults);
        // For converter, Csl must be a list of json items encoded as a string.
        $csl = json_encode([$csl], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_LINE_TERMINATORS);

        $converter = new Converter();
        $ris = $converter->convert(new CSL($csl), new RIS());
        return $ris;
    }
}
