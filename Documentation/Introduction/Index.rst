..  include:: /Includes.rst.txt

..  _introduction:

============
Introduction
============

``ok_typo3_helper`` is a TYPO3 extension developed by Oliver Kroener that
provides general-purpose services, traits and utilities to streamline the
development of other TYPO3 extensions.

Features
========

Microsoft Graph mailer service
------------------------------

``MSGraphMailApiService`` converts a Symfony Mailer ``SentMessage`` into a
Microsoft Graph-compatible ``Message`` object, ready to be sent through the
Microsoft Graph API. It transfers:

-  ``From``, ``To``, ``Cc``, ``Bcc`` and ``Reply-To`` recipients, including
   both address and display name
-  the HTML or plain-text body, auto-detected from the parsed MIME message
-  attachments, including inline images with their original ``Content-ID`` so
   that ``cid:`` references in the HTML body keep rendering correctly

Site root service
-----------------

``SiteRootService`` is a singleton that resolves the root page UID for a given
page ID using TYPO3's ``SiteFinder``. It returns ``null`` when no site is
configured for the given page.

Reflection properties trait
---------------------------

``ReflectionPropertiesTrait`` adds magic ``getXxx()`` / ``setXxx()`` accessors
that read and write protected or private properties through reflection.

Requirements
============

-  TYPO3 11.5 LTS
-  PHP 7.4 or higher
-  ``microsoft/microsoft-graph`` ``^2`` (installed automatically)
-  ``zbateson/mail-mime-parser`` ``^2`` (installed automatically)
