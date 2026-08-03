<?php

declare(strict_types=1);

namespace WEBprofil\WpMailworkflow\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Annotation\Validate;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Annotation\ORM\Cascade;
/**
 * This file is part of the "Mail Workflow" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2023 Gernot Ploiner <gp@webprofil.at>, WEBprofil - Gernot Ploiner e.U.
 */
/**
 * Set
 */
class MailGroup extends AbstractEntity
{

    /**
     * Title
     *
     * @var string
     */
    #[Validate(['validator' => 'NotEmpty'])]
    protected $title = null;

    /**
     * Mails
     *
     * @var ObjectStorage<Mail>
     */
    #[Cascade(['value' => 'remove'])]
    protected $mails = null;

    /**
     * __construct
     */
    public function __construct()
    {

        // Do not remove the next line: It would break the functionality
        $this->initializeObject();
    }

    /**
     * Initializes all ObjectStorage properties when model is reconstructed from DB (where __construct is not called)
     * Do not modify this method!
     * It will be rewritten on each save in the extension builder
     * You may modify the constructor of this class instead
     *
     * @return void
     */
    public function initializeObject()
    {
        $this->mails = $this->mails ?: new ObjectStorage();
    }

    /**
     * Returns the title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets the title
     *
     * @param string $title
     * @return void
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    /**
     * Adds a Mail
     *
     * @param Mail $mail
     * @return void
     */
    public function addMail(Mail $mail)
    {
        $this->mails->attach($mail);
    }

    /**
     * Removes a Mail
     *
     * @param Mail $mailToRemove The Mail to be removed
     * @return void
     */
    public function removeMail(Mail $mailToRemove)
    {
        $this->mails->detach($mailToRemove);
    }

    /**
     * Returns the mails
     *
     * @return ObjectStorage<Mail>
     */
    public function getMails()
    {
        return $this->mails;
    }

    /**
     * Sets the mails
     *
     * @param ObjectStorage<Mail> $mails
     * @return void
     */
    public function setMails(ObjectStorage $mails)
    {
        $this->mails = $mails;
    }
}
