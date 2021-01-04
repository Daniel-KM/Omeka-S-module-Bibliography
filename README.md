Bibliography (module for Omeka S)
=============================

> __New versions of this module and support for Omeka S version 3.0 and above
> are available on [GitLab], which seems to respect users and privacy better
> than the previous repository.__

[Bibliography] is a module for [Omeka S] that adds features to manage a
bibliography with standard citations, reference lists, suggested values, and
collected records from doi, isbn and other identifiers.

- The canonical bibliographic citations of resources are displayed according to
  any managed [citation style].
- Some record suggesters are added for the module [Value Suggest] in order to
  get multiple unique identifiers from [DOI], [ISBN], [ISSN], [OCLC], [LCCN],
  [OLID].
- Full bibliographic citation can be added through the module [Collecting], and
  even the full record.

The module adds basic output for some bibliographic formats for the module [Bulk Export]
too.

The doi and the issn are retrieved from the open service of [crossref] (a free
registration allows to get a better performance for service). The isbn, the
oclc, the lccn, and the olid are retrieved through the [OpenLibrary] api from
Internet Archive.


Installation
------------

This optional  module [Generic] may be installed first.

To use suggesters, it is required to use this [version of Value Suggest], that
contains all patchs, not yet integrated upstream. For the same reason, it is
required to use this [version of Collecting] to create a collecting form with
suggesters. To output resources in admin or public side, the module [Bulk Export]
should be installed.

The module uses external libraries, so use the release zip to install it, or use
and init the source.

See general end user documentation for [installing a module].

* From the zip

Download the last release [Bibliography.zip] from the list of releases (the
master does not contain the dependency), and uncompress it in the `modules`
directory.

* From the source and for development

If the module was installed from the source, rename the name of the folder of
the module to `Bibliography`, go to the root of the module, and run:

```sh
composer install --no-dev
```

If you used the module [Citation], it is automatically uninstalled: its features
are the same and improved in this module.


Usage
-----

### Ontology FaBiO

The ontology FaBiO uses external ontologies for some classes and properties,
mainly the Dublin Core and [Prism], and some others like [FRBR]. For easier
management, these related ontologies are not installed automatically, because
generally, the elements they uses are rare or can be replaced by the bibo ones.

### Suggesters

Once the specific [version of Value Suggest] is installed, the following
identifiers can be requested:
- DOI: Works
- DOI: Journals
- DOI: Funders
- DOI: Members
- DOI: Licenses
- DOI: Types
- ISBN: International standard book number
- LCCN: Library of Congress Control Number
- OCLC: Online computer library center
- OLID: Open library id from Internet Archive

In the config of the resource template or the collecting form, some items above
can be requested with different values. It allows to choose between a large
query with keywords, or a strict query with the id. It allows to set the label
too, that can be a name or an id according to properties, for example the title
or the article for dcterms:title, but the id for bibo:doi, or even the full
reference for dcterms:bibliographicCitation, or even the full record as a
resource.

### Creation of the citation

The citation can be displayed with any managed [citation style], in any of the
managed languages.

Remarks on the metadata:
- The process is based on the resource class, that allows to adapt the style. If
  the class is not present or [not managed], the citation may not be accurate.
- The journal (for an article) or the book (for a part) should be referenced as
  dcterms:relation or dcterms:isPartOf.
- The date should be a numeric data type or an iso8601 formatted date, partial
  or full. The year should be on 4 digits.

### Display of the citation

The citation is automatically added to the item/show page. You can have a better
control on the display with module [Blocks Disposition] or direct edition of the
theme.

To insert a citation in the theme, add this anywhere in any partial:
```php
echo $this->citation($resource, $options);
```

Default options are:
* `style` (string): the citation style (default to "chicago-fullnote-bibliography").
* `locale` (string): the locale for the citation (default to "en-US"). The
  language and the country are separated with "-", not "_".
* `defaults` (array): The default values to use when a property is not set. It
  is mainly used for own specific items to set the creator, the date, and the
  publisher of own specific records.
* `append_site` (boolean): to append the site or not.
* `append_date` (boolean): to append the access date.
* `bibliographic` (boolean): if true, set `append_site` and `append_date` false.
* `mode` (string): how to display the citation (option used to display it
  differently in various contexts). Default managed values are "list" and
  "single" (default). It may avoid to use a specific partial.
* `template` (string): partial to use for display, default is `common/citation`.
  currently).

The default options are used for Omeka resources. You may unset options to
append the site and the access date for real bibliographic resources. Any other
option is passed to template.

The result can be customized via the template `common/citation` in `view/`.


TODO
----

* Replace the library used to convert formats.
* Improve conversion from dcterms and bibo into csl, or find a library for it. See https://docs.citationstyles.org/en/1.0.1/index.html.
* Use the dcterms:type if resource class is not present.
* Add a bulk replacement from bibo to fabio.


Warning
-------

Use it at your own risk.

It’s always recommended to backup your files and your databases and to check
your archives regularly so you can roll back if needed.


Troubleshooting
---------------

See online issues on the [module issues] page on GitLab.


License
-------

This module is published under the [CeCILL v2.1] license, compatible with
[GNU/GPL] and approved by [FSF] and [OSI].

In consideration of access to the source code and the rights to copy, modify and
redistribute granted by the license, users are provided only with a limited
warranty and the software’s author, the holder of the economic rights, and the
successive licensors only have limited liability.

In this respect, the risks associated with loading, using, modifying and/or
developing or reproducing the software by the user are brought to the user’s
attention, given its Free Software status, which may make it complicated to use,
with the result that its use is reserved for developers and experienced
professionals having in-depth computer knowledge. Users are therefore encouraged
to load and test the suitability of the software as regards their requirements
in conditions enabling the security of their systems and/or data to be ensured
and, more generally, to use and operate it in the same conditions of security.
This Agreement may be freely reproduced and published, provided it is not
altered, and that no provisions are either added or removed herefrom.


Copyright
---------

* Copyright Daniel Berthereau, 2018-2021 (see [Daniel-KM] on GitLab)
* See third parties copyright for dependencies.

First version of this module was built for Daniel Berthereau for [Collegium Musicæ],
of [Sorbonne Université].


[Omeka S]: https://omeka.org/s
[Bibliography]: https://gitlab.com/Daniel-KM/Omeka-S-module-Bibliography
[citation style]: https://citationstyles.org
[Value Suggest]: https://github.com/omeka-s-modules/ValueSuggest
[Collecting]: https://github.com/omeka-s-modules/Collecting
[Bulk Export]: https://github.com/omeka-s-modules/BulkExport
[DOI]: https://doi.org
[ISBN]: https://www.isbn-international.org
[ISSN]: http://www.issn.org
[OCLC]: https://www.oclc.org
[LCCN]: https://loc.gov
[OLID]: https://openlibrary.org
[crossref]: https://www.crossref.org
[OpenLibrary]: https://openlibrary.org
[Generic]: https://gitlab.com/Daniel-KM/Omeka-S-module-Generic
[Bibliography.zip]: https://gitlab.com/Daniel-KM/Omeka-S-module-Bibliography/-/releases
[Installing a module]: http://dev.omeka.org/docs/s/user-manual/modules/#installing-modules
[Blocks Disposition]: https://gitlab.com/Daniel-KM/Omeka-S-module-BlocksDisposition
[Citation]: https://gitlab.com/Daniel-KM/Omeka-S-module-Citation
[version of Value Suggest]: https://gitlab.com/Daniel-KM/Omeka-S-module-ValueSuggest
[version of Collecting]: https://gitlab.com/Daniel-KM/Omeka-S-module-Collecting
[Prism]: http://prismstandard.org
[FRBR]: http://vocab.org/frbr/core.html
[not managed]: https://gitlab.com/Daniel-KM/Omeka-S-module-Bibliography/-/tree/master/data/mapping/csl_resource_class_map.php
[module issues]: https://gitlab.com/Daniel-KM/Omeka-S-module-Bibliography/-/issues
[CeCILL v2.1]: https://www.cecill.info/licences/Licence_CeCILL_V2.1-en.html
[GNU/GPL]: https://www.gnu.org/licenses/gpl-3.0.html
[FSF]: https://www.fsf.org
[OSI]: http://opensource.org
[Collegium Musicæ]: http://www.collegium.musicae.sorbonne-universite.fr
[Sorbonne Université]: https://www.sorbonne-universite.fr
[GitLab]: https://gitlab.com/Daniel-KM
[Daniel-KM]: https://gitlab.com/Daniel-KM "Daniel Berthereau"
