Bibliography (module for Omeka S)
=============================

[Bibliography] is a module for [Omeka S] that allows to display the canonical
bibliographic citations of resources.


Installation
------------

Uncompress files and rename module folder `Bibliography`. Then install it like
any other Omeka module and follow the config instructions.

See general end user documentation for [Installing a module].

If you used the module `Citation`, it is automatically uninstalled: its features
are the same and improved in this module.


Usage
-----

The citation is automatically added to the item/show page. You can have a better
control on the display with module [Blocks Disposition] or direct edition of the
theme.

To insert a citation in the theme, add this anywhere in any partial:
```php
echo $this->citation($resource, $options);
```

Default options are:
* `format` (string): format of the citation (only Chicago is supported
  currently).
* `append_site` (boolean): to append the site or not.
* `append_access_date` (boolean): to append the access date.
* `bibliographic` (boolean): used for a real bibliographic reference, when an
  item is a simple book or article. It implies no site and no access date.
* `mode` (string): how to display the citation (option used to display it
  differently in various contexts). Default managed values are "list" and
  "single" (default). It may avoid to use a specific partial.
* `template` (string): partial to use for display, default is `common/citation`.
  currently).
  Any other option is passed to the template.

The result can be customized via the template `common/citation` in `view/`.


TODO
----

* Use a csl-styling tool to be more accurate and allow automatic format.


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


[Omeka S]: https://omeka.org/s
[Bibliography]: https://github.com/Daniel-KM/Omeka-S-module-Bibliography
[Installing a module]: http://dev.omeka.org/docs/s/user-manual/modules/#installing-modules
[Blocks Disposition]: https://github.com/Daniel-KM/Omeka-S-module-BlocksDisposition
[module issues]: https://github.com/Daniel-KM/Omeka-S-module-Bibliography/issues
[CeCILL v2.1]: https://www.cecill.info/licences/Licence_CeCILL_V2.1-en.html
[GNU/GPL]: https://www.gnu.org/licenses/gpl-3.0.html
[FSF]: https://www.fsf.org
[OSI]: http://opensource.org
[MIT]: http://http://opensource.org/licenses/MIT
[Daniel-KM]: https://github.com/Daniel-KM "Daniel Berthereau"
