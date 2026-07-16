..  include:: /Includes.rst.txt

..  _usage:

=====
Usage
=====

Converting a message for Microsoft Graph
========================================

``MSGraphMailApiService::convertToGraphMessage()`` is ``static`` and takes a
Symfony ``SentMessage``. It returns an array with the built Graph ``message``
and the resolved ``from`` address:

..  code-block:: php

    use OliverKroener\Helpers\MSGraphApi\MSGraphMailApiService;

    $result = MSGraphMailApiService::convertToGraphMessage($sentMessage);
    $graphMessage = $result['message']; // Microsoft\Graph\Generated\Models\Message
    $fromAddress  = $result['from'];    // string|null

Inline images work automatically: build the mail with Symfony's ``->embed()``
(or a TYPO3 Fluid mail template with an inline image), and the ``cid:``
reference in the HTML body is preserved end-to-end into the Graph attachment's
``contentId``. This is what lets Outlook / OWA render the image inside the body
instead of only listing it as an attachment.

Resolving the site root
=======================

``SiteRootService`` returns the site root page UID for a given page UID. It is
registered as a public singleton in ``Configuration/Services.yaml`` and can be
injected via dependency injection:

..  code-block:: php

    use OliverKroener\Helpers\Service\SiteRootService;

    $rootPageId = $siteRootService->findNextSiteRoot($currentPageId); // int|null

If the page is not part of any configured site, ``null`` is returned.

Dynamic property accessors
==========================

``ReflectionPropertiesTrait`` provides dynamic getters and setters via
``__call()`` and reflection, giving access to protected and private properties:

..  code-block:: php

    use OliverKroener\Helpers\Traits\ReflectionPropertiesTrait;

    class MyModel
    {
        use ReflectionPropertiesTrait;

        private string $title = '';
    }

    $model = new MyModel();
    $model->setTitle('Hello');   // routed through __call → reflection
    echo $model->getTitle();     // "Hello"

An unknown method name, or access to a property that does not exist, throws an
``\Exception``.
