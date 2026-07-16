..  include:: /Includes.rst.txt

..  _usage:

=====
Usage
=====

Convert a message for Microsoft Graph
=====================================

Pass a Symfony :php:`SentMessage` to
:php:`MSGraphMailApiService::convertToGraphMessage()`. It returns an array with
the Graph message object and the resolved sender address:

..  code-block:: php

    use OliverKroener\Helpers\MSGraphApi\MSGraphMailApiService;
    use Symfony\Component\Mailer\SentMessage;

    /** @var SentMessage $sentMessage */
    $result = MSGraphMailApiService::convertToGraphMessage($sentMessage);

    $graphMessage = $result['message']; // Microsoft\Graph\Model\Message
    $fromAddress  = $result['from'];    // string, the sender address

    // $graphMessage can now be passed to the Microsoft Graph "sendMail" request.

Inline images referenced in the HTML body via ``cid:`` are detected
automatically from their ``Content-Disposition: inline`` header and carried over
with their Content-ID, so embedded graphics render in the delivered mail.

Dynamic property accessors
==========================

Use :php:`ReflectionPropertiesTrait` to add magic getters and setters to any
class. The trait resolves the property through reflection:

..  code-block:: php

    use OliverKroener\Helpers\Traits\ReflectionPropertiesTrait;

    class Contact
    {
        use ReflectionPropertiesTrait;

        private string $email = '';
    }

    $contact = new Contact();
    $contact->setEmail('ok@oliver-kroener.de'); // magic setter
    echo $contact->getEmail();                   // magic getter

Calling an accessor for a property that does not exist throws an
:php:`\Exception`.

Development tooling
===================

The extension ships with static analysis and coding-standards tooling, exposed
as Composer scripts:

..  code-block:: bash

    composer run stan       # PHPStan (level 5, TYPO3 stubs)
    composer run cs:check   # PHP-CS-Fixer dry-run (TYPO3 coding standards)
    composer run cs:fix     # PHP-CS-Fixer apply
