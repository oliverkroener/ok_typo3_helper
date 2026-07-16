..  include:: /Includes.rst.txt

..  _usage:

=====
Usage
=====

Sending mail through Microsoft Graph
====================================

Convert an outgoing Symfony ``SentMessage`` into a Microsoft Graph message and
hand it to the Graph API:

..  code-block:: php

    use OliverKroener\Helpers\MSGraphApi\MSGraphMailApiService;

    $result = MSGraphMailApiService::convertToGraphMessage($sentMessage);
    $graphMessage = $result['message']; // \Microsoft\Graph\Generated\Models\Message
    $fromAddress  = $result['from'];    // string, the sender address

The returned ``Message`` already contains recipients, subject, body and
attachments (including inline images) and can be posted to the
``/users/{id}/sendMail`` endpoint with a configured Graph client.

Resolving the site root of a page
=================================

``SiteRootService`` is a public singleton and can be injected or fetched from
the container:

..  code-block:: php

    use OliverKroener\Helpers\Service\SiteRootService;
    use TYPO3\CMS\Core\Utility\GeneralUtility;

    $rootPageId = GeneralUtility::makeInstance(SiteRootService::class)
        ->findNextSiteRoot($currentPageId); // int|null

Reflection-based accessors
==========================

Add magic getters and setters to any class:

..  code-block:: php

    use OliverKroener\Helpers\Traits\ReflectionPropertiesTrait;

    class Example
    {
        use ReflectionPropertiesTrait;

        protected string $name = '';
    }

    $example = new Example();
    $example->setName('TYPO3');   // writes the protected property
    echo $example->getName();     // reads it back

Calling a getter or setter for a property that does not exist throws an
``\Exception``.
