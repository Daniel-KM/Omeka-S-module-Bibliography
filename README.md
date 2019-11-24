Bibliography (module for Omeka S)
=============================

[Bibliography] is a module for [Omeka S] that allows to display the canonical
bibliographic citations of resources, according to any managed [citation style].
Furthermore, it adds some new suggesters to the module [Value Suggest] in order
to get multiple unique identifiers from [DOI], [ISBN], [ISSN], [OCLC], [LCCN],
[OLID].

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
suggesters.

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

```
composer install
```

If you used the module [Citation], it is automatically uninstalled: its features
are the same and improved in this module.


Usage
-----

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
or the article for dcterms:title, but the id for bibo:doi.

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
* `append_site` (boolean): to append the site or not.
* `append_access_date` (boolean): to append the access date.
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

* Improve conversion from dcterms and bibo into csl, or find a library for it. See https://docs.citationstyles.org/en/1.0.1/index.html.
* Use the dcterms:type if resource class is not present.


Warning
-------

Use it at your own risk.

It’s always recommended to backup your files and your databases and to check
your archives regularly so you can roll back if needed.


Troubleshooting
---------------

See online issues on the [module issues] page on GitHub.


License
-------

This module is published under the [CeCILL v2.1] licence, compatible with
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

* Copyright Daniel Berthereau, 2018-2019 (see [Daniel-KM] on GitHub)
* See third parties copyright for dependencies.

First version of this module was built for Daniel Berthereau for [Collegium Musicæ]
and LAM / Institut ∂’Alembert, of [Université Paris Sorbonne].


[Omeka S]: https://omeka.org/s
[Bibliography]: https://github.com/Daniel-KM/Omeka-S-module-Bibliography
[citation style]: https://citationstyles.org
[Value Suggest]: https://github.com/omeka-s-modules/ValueSuggest
[DOI]: https://doi.org
[ISBN]: https://www.isbn-international.org
[ISSN]: http://www.issn.org
[OCLC]: https://www.oclc.org
[LCCN]: https://loc.gov
[OLID]: https://openlibrary.org
[crossref]: https://www.crossref.org
[OpenLibrary]: https://openlibrary.org
[Generic]: https://github.com/Daniel-KM/Omeka-S-module-Generic
[Bibliography.zip]: https://github.com/Daniel-KM/Omeka-S-module-Bibliography/releases
[Installing a module]: http://dev.omeka.org/docs/s/user-manual/modules/#installing-modules
[Blocks Disposition]: https://github.com/Daniel-KM/Omeka-S-module-BlocksDisposition
[Citation]: https://github.com/Daniel-KM/Omeka-S-module-Citation
[version of Value Suggest]: https://github.com/Daniel-KM/Omeka-S-module-ValueSuggest
[version of Collecting]: https://github.com/Daniel-KM/Omeka-S-module-Collecting
[not managed]: https://github.com/Daniel-KM/Omeka-S-module-Bibliography/tree/master/data/mapping/resource_class_map.php
[module issues]: https://github.com/Daniel-KM/Omeka-S-module-Bibliography/issues
[CeCILL v2.1]: https://www.cecill.info/licences/Licence_CeCILL_V2.1-en.html
[GNU/GPL]: https://www.gnu.org/licenses/gpl-3.0.html
[FSF]: https://www.fsf.org
[OSI]: http://opensource.org
[Collegium Musicæ]: http://www.collegium.musicae.sorbonne-universite.fr
[Université Paris Sorbonne]: https://www.sorbonne-universite.fr
[Daniel-KM]: https://github.com/Daniel-KM "Daniel Berthereau"
