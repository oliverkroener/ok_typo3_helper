..  include:: /Includes.rst.txt

..  _introduction:

============
Introduction
============

The **TYPO3 Helpers** extension (``ok_typo3_helper``) provides a small set of
reusable helper traits and utilities for TYPO3 projects. Its centrepiece is a
Microsoft Graph mail conversion service that bridges TYPO3's Symfony-based
mailer with the Microsoft Graph API.

What it does
============

Microsoft Graph mail conversion
-------------------------------

The :php:`OliverKroener\Helpers\MSGraphApi\MSGraphMailApiService` service parses
a raw Symfony :php:`SentMessage` (MIME) and produces a
:php:`Microsoft\Graph\Model\Message` object that is ready to be sent through the
Microsoft Graph ``sendMail`` endpoint. It covers:

-  **Recipients** — maps ``From``, ``To``, ``CC``, ``BCC`` and ``Reply-To``
   addresses, including display names.
-  **Body** — supports both HTML and plain-text content, with a graceful
   fallback when neither is present.
-  **Attachments** — converts every file attachment into a Graph
   ``FileAttachment`` with base64-encoded content bytes.
-  **Inline images (cid:)** — inline parts are detected from their
   ``Content-Disposition: inline`` header, flagged with ``isInline`` and given
   their original **Content-ID**, so ``cid:`` references in the HTML body render
   correctly in the delivered mail.

Reflection properties trait
---------------------------

The :php:`OliverKroener\Helpers\Traits\ReflectionPropertiesTrait` trait adds
dynamic ``getX()`` / ``setX()`` magic accessors to any class, resolving the
underlying property through reflection.

Requirements
============

-  **TYPO3:** 10.4 LTS
-  **PHP:** 7.2 or higher
-  **Dependencies:** ``microsoft/microsoft-graph`` ``^1`` and
   ``zbateson/mail-mime-parser`` ``^2``
