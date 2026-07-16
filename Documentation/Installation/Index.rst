..  include:: /Includes.rst.txt

..  _installation:

============
Installation
============

The extension is installed with Composer.

Composer
========

..  code-block:: bash

    composer require oliverkroener/ok-typo3-helper

Local path repository
=====================

For local development, expose the extension through a path repository in your
project's root ``composer.json``:

..  code-block:: json

    {
        "repositories": [
            {
                "type": "path",
                "url": "packages/*"
            }
        ]
    }

Then require and activate it:

..  code-block:: bash

    composer require oliverkroener/ok-typo3-helper:@dev
    vendor/bin/typo3 extension:activate ok_typo3_helper

Dependencies
============

Composer resolves the runtime dependencies automatically:

-  `microsoft/microsoft-graph <https://github.com/microsoftgraph/msgraph-sdk-php>`__ ``^1``
-  `zbateson/mail-mime-parser <https://github.com/zbateson/mail-mime-parser>`__ ``^2``
