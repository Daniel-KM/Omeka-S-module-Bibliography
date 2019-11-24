<?php
namespace Bibliography\Form;

trait TraitCslData
{
    /**
     * @var array
     */
    protected $citationStyles = [];

    /**
     * @var array
     */
    protected $citationLocales = [];

    /**
     * @param array $citationStyles
     * @return self
     */
    public function setCitationStyles(array $citationStyles)
    {
        $this->citationStyles = $citationStyles;
        return $this;
    }

    /**
     * @return array
     */
    public function getCitationStyles()
    {
        return $this->citationStyles;
    }

    /**
     * @param array $citationLocales
     * @return self
     */
    public function setCitationLocales(array $citationLocales)
    {
        $this->citationLocales = $citationLocales;
        return $this;
    }

    /**
     * @return array
     */
    public function getCitationLocales()
    {
        return $this->citationLocales;
    }
}
