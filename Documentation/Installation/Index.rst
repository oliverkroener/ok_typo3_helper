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

The required dependencies ``microsoft/microsoft-graph`` and
``zbateson/mail-mime-parser`` are installed automatically.

Local development
=================

To use a local checkout during development, add a path repository to your
project's ``composer.json``:

..  code-block:: json

    {
        "repositories": [
            { "type": "path", "path": "packages/ok_typo3_helper" }
        ]
    }

Then require the package and activate the extension:

..  code-block:: bash

    composer require oliverkroener/ok-typo3-helper
    vendor/bin/typo3 extension:activate ok_typo3_helper
