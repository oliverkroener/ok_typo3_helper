..  include:: /Includes.rst.txt

..  _configuration:

=============
Configuration
=============

This extension does not ship any extension-manager settings. Its services are
registered through dependency injection in ``Configuration/Services.yaml``.

Service registration
=====================

All classes under ``Classes/`` are autowired and autoconfigured. The
``SiteRootService`` is additionally registered as a **public** singleton so it
can be fetched directly from the container or via
``GeneralUtility::makeInstance()``:

..  code-block:: yaml

    services:
      _defaults:
        autowire: true
        autoconfigure: true
        public: false

      OliverKroener\Helpers\:
        resource: '../Classes/*'

      OliverKroener\Helpers\Service\SiteRootService:
        public: true
        autowire: true
        autoconfigure: true
        tags: ['singleton']

Microsoft Graph credentials
===========================

``MSGraphMailApiService`` only converts a message into the Graph
``Message`` format — it does not authenticate against Microsoft Graph itself.
Authentication and the actual sending are the responsibility of the calling
code, which must provide a configured Microsoft Graph client (for example an
OAuth2 client-credentials flow with tenant ID, client ID and client secret).
