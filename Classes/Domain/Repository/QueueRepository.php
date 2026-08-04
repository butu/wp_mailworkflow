<?php

declare(strict_types=1);

namespace WEBprofil\WpMailworkflow\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use WEBprofil\WpMailworkflow\Domain\Model\Recipient;

/**
 * This file is part of the "Mail Workflow" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2023 Gernot Ploiner <gp@webprofil.at>, WEBprofil - Gernot Ploiner e.U.
 */

/**
 * The repository for Queues
 */
class QueueRepository extends Repository
{

    protected $defaultOrderings = array(
        'sendAt' => QueryInterface::ORDER_DESCENDING
    );

    public function initializeObject()
    {
        /** @var QuerySettingsInterface $querySettings */
        $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
        $querySettings->setRespectStoragePage(false);
        $this->setDefaultQuerySettings($querySettings);
    }

    /**
     * Method for scheduler: doesn't respect storage page!
     *
     * @return object[]|QueryResultInterface
     * @throws InvalidQueryException
     */
    public function findToSend(): QueryResultInterface|array
    {
        $now = new \DateTime();
        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd($query->lessThanOrEqual('sendAt', $now->format('U')), $query->equals('isSent', false))
        );
        return $query->execute();
    }

    public function findLast(int $limit): QueryResultInterface|array
    {
        $query = $this->createQuery();
        $query->setLimit($limit);
        return $query->execute();
    }

    public function findByRecipient(Recipient $recipient): QueryResultInterface|array
    {
        $query = $this->createQuery();
        $query->matching($query->equals('recipient', $recipient));
        return $query->execute();
    }

}
