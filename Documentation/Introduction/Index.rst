..  include:: /Includes.rst.txt

..  _introduction:

============
Introduction
============

``ok_typo3_helper`` provides general-purpose functionality and utilities that
streamline the development of other TYPO3 extensions. It is used as a shared
foundation by the Oliver Kröner extension family — most notably
``ok_exchange365_mailer``, which relies on the Microsoft Graph mail converter
shipped here.

Features
========

Microsoft Graph mail converter
------------------------------

``MSGraphMailApiService`` converts a Symfony ``SentMessage`` into a Microsoft
Graph ``Message`` object ready for the Graph ``sendMail`` endpoint. It maps:

-  From / To / Cc / Bcc / Reply-To recipients
-  HTML and plain-text message bodies
-  File attachments, including **inline images**

Inline images (``cid:``)
~~~~~~~~~~~~~~~~~~~~~~~~~~

Attachments carrying ``Content-Disposition: inline`` are flagged as inline on
the Graph ``FileAttachment`` **and** their original MIME ``Content-ID`` is
carried over. This is essential: Microsoft Graph resolves an
``<img src="cid:xxx">`` in the HTML body by matching ``xxx`` against the
attachment's ``contentId``. Without the Content-ID, the image bytes still
arrive but the body reference cannot bind, so the image renders broken.

The value is read via ``getContentId()``, which already returns the identifier
without the surrounding ``<>`` — so it matches the ``cid:`` reference in the
body verbatim, with no manual trimming.

Site root resolver
------------------

``SiteRootService`` is a singleton service that returns the site root page UID
for any given page UID, resolving a ``SiteNotFoundException`` to ``null``.

Reflection accessor trait
-------------------------

``ReflectionPropertiesTrait`` adds dynamic ``getXxx()`` / ``setXxx()`` access to
any class's properties — including protected and private ones — via ``__call()``
and reflection.

Requirements
============

-  TYPO3 12.4 LTS, 13.4 LTS, or 14 LTS
-  PHP 8.1 or newer
-  ``microsoft/microsoft-graph`` ``^2`` (installed automatically)
-  ``zbateson/mail-mime-parser`` ``^3`` (installed automatically)
