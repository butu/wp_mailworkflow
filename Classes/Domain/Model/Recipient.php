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
 * Recipient
 */
class Recipient extends AbstractEntity
{

    /**
     * Start
     *
     * @var \DateTime
     */
    #[Validate(['validator' => 'NotEmpty'])]
    protected $start = null;

    /**
     * First name
     *
     * @var string
     */
    protected $firstName = null;

    /**
     * Last name
     *
     * @var string
     */
    protected $lastName = null;

    /**
     * email
     *
     * @var string
     */
    #[Validate(['validator' => 'NotEmpty'])]
    protected $email = null;

    /**
     * Parameter 1
     *
     * @var string
     */
    protected $parameter1 = null;

    /**
     * Parameter 2
     *
     * @var string
     */
    protected $parameter2 = null;

    /**
     * Parameter 3
     *
     * @var string
     */
    protected $parameter3 = null;

    /**
     * Parameter 4
     *
     * @var string
     */
    protected $parameter4 = null;

    /**
     * Parameter 5
     *
     * @var string
     */
    protected $parameter5 = null;

    /**
     * Object
     *
     * @var MailGroup
     */
    protected $mailGroup = null;

    /**
     * Returns the firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Sets the firstName
     *
     * @param string $firstName
     * @return void
     */
    public function setFirstName(string $firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * Returns the lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Sets the lastName
     *
     * @param string $lastName
     * @return void
     */
    public function setLastName(string $lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * Returns the email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Sets the email
     *
     * @param string $email
     * @return void
     */
    public function setEmail(string $email)
    {
        $this->email = $email;
    }

    /**
     * Returns the parameter1
     *
     * @return string
     */
    public function getParameter1()
    {
        return $this->parameter1;
    }

    /**
     * Sets the parameter1
     *
     * @param string $parameter1
     * @return void
     */
    public function setParameter1(string $parameter1)
    {
        $this->parameter1 = $parameter1;
    }

    /**
     * Returns the parameter2
     *
     * @return string
     */
    public function getParameter2()
    {
        return $this->parameter2;
    }

    /**
     * Sets the parameter2
     *
     * @param string $parameter2
     * @return void
     */
    public function setParameter2(string $parameter2)
    {
        $this->parameter2 = $parameter2;
    }

    /**
     * Returns the parameter3
     *
     * @return string
     */
    public function getParameter3()
    {
        return $this->parameter3;
    }

    /**
     * Sets the parameter3
     *
     * @param string $parameter3
     * @return void
     */
    public function setParameter3(string $parameter3)
    {
        $this->parameter3 = $parameter3;
    }

    /**
     * Returns the parameter4
     *
     * @return string
     */
    public function getParameter4()
    {
        return $this->parameter4;
    }

    /**
     * Sets the parameter4
     *
     * @param string $parameter4
     * @return void
     */
    public function setParameter4(string $parameter4)
    {
        $this->parameter4 = $parameter4;
    }

    /**
     * Returns the parameter5
     *
     * @return string
     */
    public function getParameter5()
    {
        return $this->parameter5;
    }

    /**
     * Sets the parameter5
     *
     * @param string $parameter5
     * @return void
     */
    public function setParameter5(string $parameter5)
    {
        $this->parameter5 = $parameter5;
    }

    /**
     * Returns the start
     *
     * @return \DateTime
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Sets the start
     *
     * @param \DateTime $start
     * @return void
     */
    public function setStart(\DateTime $start)
    {
        $this->start = $start;
    }

    /**
     * Returns the mailGroup
     *
     * @return MailGroup mailGroup
     */
    public function getMailGroup()
    {
        return $this->mailGroup;
    }

    /**
     * Sets the mailGroup
     *
     * @param MailGroup $mailGroup
     * @return void
     */
    public function setMailGroup(MailGroup $mailGroup)
    {
        $this->mailGroup = $mailGroup;
    }
}
