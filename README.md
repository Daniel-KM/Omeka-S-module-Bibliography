Bibliography (module for Omeka S)
=============================

> __New versions of this module and support for Omeka S version 3.0 and above
> are available on [GitLab], which seems to respect users and privacy better
> than the previous repository.__

[Bibliography] is a module for [Omeka S] that adds features to manage a
bibliography with standard citations, reference lists, suggested values, and
collected records from doi, isbn and other identifiers. The reference can be
added via site or resource blocks.

- The canonical bibliographic citations of resources are displayed according to
  any managed [citation style].
- Some record suggesters are added for the module [Value Suggest] in order to
  get multiple unique identifiers from [DOI], [ISBN], [ISSN], [OCLC], [LCCN],
  [OLID].

The module adds basic output for some bibliographic formats for the module [Bulk Export]
too.

The doi and the issn are retrieved from the open service of [crossref] (a free
registration allows to get a better performance for service). The isbn, the
oclc, the lccn, and the olid are retrieved through the [OpenLibrary] api from
Internet Archive.


Installation
------------

See general end user documentation for [installing a module].

This module requires the module [Common], that should be installed first.

The module uses external libraries, so use the release zip to install it, or use
and init the source.

* From the zip

Download the last release [Bibliography.zip] from the list of releases, and
uncompress it in the `modules` directory.

* From the source and for development

If the module was installed from the source, rename the name of the folder of
the module to `Bibliography`, go to the root of the module, and run:

```sh
composer install --no-dev
```

* For test

The module includes a comprehensive test suite with unit and functional tests.
Run them from the root of Omeka:

```sh
vendor/bin/phpunit -c modules/Bibliography/phpunit.xml --testdox
```

If you used the module [Citation], it is automatically uninstalled: its features
are the same and improved in this module.

To use the suggesters, the module [Value Suggest] should be installed (any
version compatible with Omeka S 4). To export resources in bibliographic formats
(BibTeX, CSL, RIS, citations as text or OpenDocument), the module [Bulk Export]
should be installed.


Usage
-----

### Ontology FaBiO

The ontology FaBiO uses external ontologies for some classes and properties,
mainly the Dublin Core and [Prism], and some others like [FRBR]. For easier
management, these related ontologies are not installed automatically, because
generally, the elements they uses are rare or can be replaced by the bibo ones.

### Suggesters

Once the module [Value Suggest] is installed, the following
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

In the config of the resource template, some items above can be requested with
different values. It allows to choose between a large query with keywords, or a
strict query with the id. It allows to set the label too, that can be a name or
an id according to properties, for example the title or the article for
dcterms:title, but the id for bibo:doi, or even the full reference for
dcterms:bibliographicCitation.

The data types "(collected record)" fetch the whole record: when a suggestion is
selected in the resource form, the empty properties and the empty resource class
are filled with the metadata of the record, and the properties already filled
are listed, but not modified. The value itself keeps the doi or the url of the
record, with the formatted reference as label. The same process is done when the
resource is saved via the api or an import.

In previous versions, these data types were designed only for a specific branch
of the module [Collecting] (Omeka S 2), now obsolete: the suggester sent the
whole record, converted into resource data, as a json string in place of the
uri, and the collecting form decoded it to append all its values and its
resource class to the new item, so a contributor could create a full
bibliographic record from a doi or an isbn. The value itself was not stored.
Elsewhere, for example in a resource template, the json was saved as the uri of
the value, so it was displayed as a broken link. The upgrade to version 3.4.13
expands these values like a new save, and removes the alternative ids that are
not doi, that were stored as doi.

### Creation of the citation

The citation can be displayed with any managed [citation style], in any of the
managed languages. The style and the language are set in the main settings and
in the site settings.

Some recent styles use options that are not managed yet by the library CiteProc,
for example the page range format "chicago-16" of the default style: they are
replaced by the nearest managed one. Some styles cannot output a bibliographic
reference, for example some note styles without bibliography: the citation uses
then a simple format similar to Chicago.

Remarks on the metadata:
- The process is based on the resource class, that allows to adapt the style. If
  the class is not present or [not managed], the citation may not be accurate.
- The journal (for an article) or the book (for a part) should be referenced as
  dcterms:relation or dcterms:isPartOf.
- The date should be an iso 8601 date, partial or full ("1916", "2015-02",
  "2015-02-04"), with any data type (literal, numeric or edtf). An interval like
  "1914/1918" or "1914-1918" and an approximate or uncertain edtf date like
  "1916~" or "1916?" are managed too. For any other value, the first year is
  used, if any.

### Display of the citation

The citation can be added to the item page via the resource page blocks of the
theme, configured in the site admin:
- "Bibliography: Citation": the bibliographic reference;
- "Bibliography: Citation (button)": a button to copy the reference into the
  clipboard.

For old themes that do not use resource page blocks, an option in the site
settings allows to display the citation at the top or the bottom of the item
page. It is not set by default.

A list of references can be added to a site page via the page block
"Bibliography".

To insert a citation in the theme, add this anywhere in any partial:
```php
echo $this->citation($resource, $options);
```

Default options are:
* `style` (string): the citation style (default to "chicago-author-date"),
   similar to "din-1505-2", the Deutsch Norm with author, title and date.
* `locale` (string): the locale for the citation and the access date (default
  to the locale set in the settings, else the locale of the site, else "en-US").
  The language and the country are separated with "-", not "_".
* `defaults` (array): The default values to use when a property is not set. It
  is mainly used for own specific items to set the creator, the date, and the
  publisher of own specific records.
* `append_site` (boolean): to append the site or not.
* `append_date` (boolean): to append the access date.
* `bibliographic` (boolean): if true, set `append_site` and `append_date` false.
* `tag` (string): the html tag wrapping the citation: "p" (default), "div", or
  an empty string to skip it.
* `as_text` (boolean): output a plain text instead of html (italic for title,
  etc.).
* `template` (string): partial to use for display, default is `common/citation`.

The default options are used for Omeka resources. You may unset options to
append the site and the access date for real bibliographic resources. Any other
option is passed to template.

The result can be customized via the template `common/citation` in `view/`. The
access date, localized according to the option `locale`, is passed to it as the
variable `accessed`.


TODO
----

- [ ] Create a builder from resource template: select title/author/date/publisher/subinfo and use or create a citation template.
- [ ] Replace the library used to convert formats.
- [ ] Improve conversion from dcterms and bibo into csl, or find a library for it. See https://docs.citationstyles.org/en/1.0.1/index.html.
- [ ] Use the dcterms:type if resource class is not present.
- [ ] Add a bulk replacement from bibo to fabio?


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

* Copyright Daniel Berthereau, 2018-2026 (see [Daniel-KM] on GitLab)
* See third parties copyright for dependencies.

First version of this module was built for Daniel Berthereau for [Collegium Musicæ],
of [Sorbonne Université].


[Omeka S]: https://omeka.org/s
[Bibliography]: https://gitlab.com/Daniel-KM/Omeka-S-module-Bibliography
[citation style]: https://citationstyles.org
[Value Suggest]: https://github.com/omeka-s-modules/ValueSuggest
[Bulk Export]: https://github.com/omeka-s-modules/BulkExport
[Collecting]: https://github.com/omeka-s-modules/Collecting
[DOI]: https://doi.org
[ISBN]: https://www.isbn-international.org
[ISSN]: http://www.issn.org
[OCLC]: https://www.oclc.org
[LCCN]: https://loc.gov
[OLID]: https://openlibrary.org
[crossref]: https://www.crossref.org
[OpenLibrary]: https://openlibrary.org
[Common]: https://gitlab.com/Daniel-KM/Omeka-S-module-Common
[Bibliography.zip]: https://gitlab.com/Daniel-KM/Omeka-S-module-Bibliography/-/releases
[installing a module]: https://omeka.org/s/docs/user-manual/modules/#installing-modules
[Citation]: https://gitlab.com/Daniel-KM/Omeka-S-module-Citation
[Prism]: http://prismstandard.org
[FRBR]: http://vocab.org/frbr/core.html
[not managed]: https://gitlab.com/Daniel-KM/Omeka-S-module-Bibliography/-/tree/master/data/mapping/csl_resource_class_map.php
[module issues]: https://gitlab.com/Daniel-KM/Omeka-S-module-Bibliography/-/work_items
[CeCILL v2.1]: https://www.cecill.info/licences/Licence_CeCILL_V2.1-en.html
[GNU/GPL]: https://www.gnu.org/licenses/gpl-3.0.html
[FSF]: https://www.fsf.org
[OSI]: http://opensource.org
[Collegium Musicæ]: http://www.collegium.musicae.sorbonne-universite.fr
[Sorbonne Université]: https://www.sorbonne-universite.fr
[GitLab]: https://gitlab.com/Daniel-KM
[Daniel-KM]: https://gitlab.com/Daniel-KM "Daniel Berthereau"
