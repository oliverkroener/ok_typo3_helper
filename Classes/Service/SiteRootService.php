<?php

namespace OliverKroener\Helpers\Service;

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;

class SiteRootService implements SingletonInterface
{
    /**
     * @var SiteFinder
     */
    protected $siteFinder;

    /**
     * Inject the SiteFinder
     *
     * @param SiteFinder $siteFinder
     */
    public function injectSiteFinder(SiteFinder $siteFinder)
    {
        $this->siteFinder = $siteFinder;
    }

    /**
     * Finds the next lower site root in the rootline using an arrow function.
     *
     * @param int $currentPageId The UID of the current page.
     * @return int|null The UID of the site root if found, null otherwise.
     */
    public function findNextSiteRoot(int $currentPageId): ?int
    {
        try {
            // Retrieve the Site object associated with the given page ID
            $site = $this->siteFinder->getSiteByPageId($currentPageId);

            // Get the root page ID from the Site object
            $rootPageId = $site->getRootPageId();

            return $rootPageId;
        } 
        catch (SiteNotFoundException $e) {
            return null;
        }
    }
}
