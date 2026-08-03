<?php

declare(strict_types=1);

namespace WEBprofil\WpMailworkflow\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Annotation\Validate;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
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
 * Mail
 */
class Mail extends AbstractEntity
{

    /**
     * Title
     *
     * @var string
     */
    #[Validate(['validator' => 'NotEmpty'])]
    protected $title = null;

    /**
     * Days to send
     *
     * @var int
     */
    #[Validate(['validator' => 'NotEmpty'])]
    protected $daysToSend = 0;

    /**
     * Send Time
     *
     * @var \DateTime
     */
    protected $sendTime = null;

    /**
     * Subject
     *
     * @var string
     */
    #[Validate(['validator' => 'NotEmpty'])]
    protected $subject = null;

    /**
     * Mailtext
     *
     * @var string
     */
    #[Validate(['validator' => 'NotEmpty'])]
    protected $mailtext = null;

    /**
     * Attachment
     *
     * @var FileReference
     */
    #[Cascade(['value' => 'remove'])]
    protected $attachment = null;

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
     * Returns the subject
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Sets the subject
     *
     * @param string $subject
     * @return void
     */
    public function setSubject(string $subject)
    {
        $this->subject = $subject;
    }

    /**
     * Returns the mailtext
     *
     * @return string
     */
    public function getMailtext()
    {
        return $this->mailtext;
    }

    /**
     * Sets the mailtext
     *
     * @param string $mailtext
     * @return void
     */
    public function setMailtext(string $mailtext)
    {
        $this->mailtext = $mailtext;
    }

    /**
     * Returns the attachment
     *
     * @return FileReference
     */
    public function getAttachment()
    {
        return $this->attachment;
    }

    /**
     * Sets the attachment
     *
     * @param FileReference $attachment
     * @return void
     */
    public function setAttachment(FileReference $attachment)
    {
        $this->attachment = $attachment;
    }

    /**
     * Returns the daysToSend
     *
     * @return int
     */
    public function getDaysToSend()
    {
        return $this->daysToSend;
    }

    /**
     * Sets the daysToSend
     *
     * @param int $daysToSend
     * @return void
     */
    public function setDaysToSend(int $daysToSend)
    {
        $this->daysToSend = $daysToSend;
    }

    /**
     * Returns the sendTime
     *
     * @return \DateTime
     */
    public function getSendTime()
    {
        return $this->sendTime;
    }

    /**
     * Sets the sendTime
     *
     * @param \DateTime $sendTime
     * @return void
     */
    public function setSendTime(\DateTime $sendTime)
    {
        $this->sendTime = $sendTime;
    }
}
