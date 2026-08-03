<?php

declare(strict_types=1);

namespace WEBprofil\WpMailworkflow\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Annotation\Validate;
/**
 * This file is part of the "Mail Workflow" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2023 Gernot Ploiner <gp@webprofil.at>, WEBprofil - Gernot Ploiner e.U.
 */
/**
 * Log
 */
class Queue extends AbstractEntity
{

    /**
     * Is sent
     *
     * @var bool
     */
    protected $isSent = false;

    /**
     * Send at
     *
     * @var \DateTime
     */
    #[Validate(['validator' => 'NotEmpty'])]
    protected $sendAt = null;

    /**
     * Sent
     *
     * @var \DateTime
     */
    protected $sent = null;

    /**
     * Recipient
     *
     * @var Recipient
     */
    protected $recipient = null;

    /**
     * Mail
     *
     * @var Mail
     */
    protected $mail = null;

    /**
     * Returns the recipient
     *
     * @return Recipient
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * Sets the recipient
     *
     * @param Recipient $recipient
     * @return void
     */
    public function setRecipient(Recipient $recipient)
    {
        $this->recipient = $recipient;
    }

    /**
     * Returns the mail
     *
     * @return Mail
     */
    public function getMail()
    {
        return $this->mail;
    }

    /**
     * Sets the mail
     *
     * @param Mail $mail
     * @return void
     */
    public function setMail(Mail $mail)
    {
        $this->mail = $mail;
    }

    /**
     * Returns the isSent
     *
     * @return bool
     */
    public function getIsSent()
    {
        return $this->isSent;
    }

    /**
     * Sets the isSent
     *
     * @param bool $isSent
     * @return void
     */
    public function setIsSent(bool $isSent)
    {
        $this->isSent = $isSent;
    }

    /**
     * Returns the boolean state of isSent
     *
     * @return bool
     */
    public function isIsSent()
    {
        return $this->isSent;
    }

    /**
     * Returns the sendAt
     *
     * @return \DateTime
     */
    public function getSendAt()
    {
        return $this->sendAt;
    }

    /**
     * Sets the sendAt
     *
     * @param \DateTime $sendAt
     * @return void
     */
    public function setSendAt(\DateTime $sendAt)
    {
        $this->sendAt = $sendAt;
    }

    /**
     * Returns the sent
     *
     * @return \DateTime
     */
    public function getSent()
    {
        return $this->sent;
    }

    /**
     * Sets the sent
     *
     * @param \DateTime $sent
     * @return void
     */
    public function setSent(\DateTime $sent)
    {
        $this->sent = $sent;
    }
}
