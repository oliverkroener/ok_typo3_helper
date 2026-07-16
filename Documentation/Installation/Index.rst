..  include:: /Includes.rst.txt

..  _installation:

============
Installation
============

Composer
========

Install the extension via Composer:

..  code-block:: bash

    composer require oliverkroener/ok-typo3-helper

Local path repository
=====================

When used as a local path-repository extension (as in the
``oliverkroener`` project layout), place the package under ``packages/`` and
require it as a development version:

..  code-block:: bash

    composer require oliverkroener/ok-typo3-helper:@dev

Setup
=====

After installation, run the extension setup and flush caches:

..  code-block:: bash

    vendor/bin/typo3 extension:setup
    vendor/bin/typo3 cache:flush

The Microsoft Graph SDK (``microsoft/microsoft-graph``) and the MIME parser
(``zbateson/mail-mime-parser``) are declared as dependencies and are installed
automatically.
