<?php

declare(strict_types=1);

namespace WEBprofil\WpMailworkflow\Controller;

use TYPO3\CMS\Backend\Attribute\AsController;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation\IgnoreValidation;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use Psr\Http\Message\ResponseInterface;
use WEBprofil\WpMailworkflow\Domain\Model\Queue;
use WEBprofil\WpMailworkflow\Domain\Model\Recipient;
use WEBprofil\WpMailworkflow\Domain\Repository\MailGroupRepository;
use WEBprofil\WpMailworkflow\Domain\Repository\QueueRepository;
use WEBprofil\WpMailworkflow\Domain\Repository\RecipientRepository;

/**
 * RecipientController
 */
#[AsController]
class RecipientController extends ActionController
{
    public function __construct(
        protected readonly ModuleTemplateFactory $moduleTemplateFactory,
        protected readonly IconFactory $iconFactory,
        private readonly RecipientRepository $recipientRepository,
        private readonly QueueRepository $queueRepository,
        private readonly MailGroupRepository $mailGroupRepository
    ) {
    }

    /**
     * action list
     */
    public function listAction(): ResponseInterface
    {
        // BE-Layout initialisieren
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $buttonBar = $moduleTemplate->getDocHeaderComponent()->getButtonBar();

        // Daten laden
        $recipients = $this->recipientRepository->findAll();
        $moduleTemplate->assign('recipients', $recipients);

        // Buttons
        $this->shortcutButton($buttonBar);
        $this->queueButton($buttonBar);

        // Neu: ModuleTemplate rendert direkt die View
        // Template liegt z.B. unter:
        // EXT:wp_mailworkflow/Resources/Private/Templates/Recipient/List.html
        return $moduleTemplate->renderResponse('Recipient/List');
    }

    /**
     * action new
     */
    public function newAction(): ResponseInterface
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $buttonBar = $moduleTemplate->getDocHeaderComponent()->getButtonBar();

        $mailGroups = $this->mailGroupRepository->findAll();
        $moduleTemplate->assign('mailGroups', $mailGroups);

        $this->shortcutButton($buttonBar);
        $this->queueButton($buttonBar);

        return $moduleTemplate->renderResponse('Recipient/New');
    }

    /**
     * action create
     */
    public function createAction(Recipient $newRecipient): RedirectResponse
    {
        $this->recipientRepository->add($newRecipient);
        $persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);
        $persistenceManager->persistAll();
        $this->createQueue($newRecipient);

        return new RedirectResponse($this->uriBuilder->uriFor('list'));
    }

    /**
     * action edit
     *
     * @IgnoreValidation("recipient")
     */
    public function editAction(Recipient $recipient): ResponseInterface
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $buttonBar = $moduleTemplate->getDocHeaderComponent()->getButtonBar();

        $mailGroups = $this->mailGroupRepository->findAll();

        $moduleTemplate->assignMultiple([
            'mailGroups' => $mailGroups,
            'recipient'  => $recipient,
        ]);

        $this->shortcutButton($buttonBar);
        $this->queueButton($buttonBar);

        return $moduleTemplate->renderResponse('Recipient/Edit');
    }

    /**
     * action update
     */
    public function updateAction(Recipient $recipient): RedirectResponse
    {
        $this->recipientRepository->update($recipient);
        return new RedirectResponse($this->uriBuilder->uriFor('list'));
    }

    /**
     * action delete
     */
    public function deleteAction(Recipient $recipient): RedirectResponse
    {
        $queues = $this->queueRepository->findByRecipient($recipient);
        foreach ($queues as $queue) {
            /** @var Queue $queue */
            if (!$queue->getIsSent()) {
                $this->queueRepository->remove($queue);
            }
        }
        $this->recipientRepository->remove($recipient);
        return new RedirectResponse($this->uriBuilder->uriFor('list'));
    }

    /**
     * Queue-Einträge für neuen Empfänger erzeugen
     */
    private function createQueue(Recipient $recipient): void
    {
        $mailGroup = $recipient->getMailGroup();
        foreach ($mailGroup->getMails() as $mail) {
            $hour = 0;
            $minute = 0;
            $sendAt = clone $recipient->getStart();
            $sendAt->modify('+ ' . $mail->getDaysToSend() . ' days');
            if ($mail->getSendTime()) {
                $hour = (int)$mail->getSendTime()->format('H');
                $minute = (int)$mail->getSendTime()->format('i');
            }
            $sendAt->setTime($hour, $minute);
            $queue = GeneralUtility::makeInstance(Queue::class);
            $queue->setRecipient($recipient);
            $queue->setMail($mail);
            $queue->setSendAt($sendAt);
            $this->queueRepository->add($queue);
        }
    }

    /**
     * shortcut menu Button
     */
    private function shortcutButton(ButtonBar $buttonBar): void
    {
        $shortcutButton = $buttonBar->makeShortcutButton()
            ->setRouteIdentifier('wp_mailworkflow')
            ->setDisplayName('Mailworkflow');

        $buttonBar->addButton($shortcutButton, ButtonBar::BUTTON_POSITION_RIGHT);
    }

    /**
     * queue menu Button
     */
    private function queueButton(ButtonBar $buttonBar): void
    {
        $url = $this->uriBuilder->reset()->uriFor('list', [], 'Queue');

        $list = $buttonBar->makeLinkButton()
            ->setHref($url)
            ->setTitle('Queue')
            ->setShowLabelText('Link')
            ->setIcon($this->iconFactory->getIcon('actions-heart', Icon::SIZE_SMALL));

        $buttonBar->addButton($list, ButtonBar::BUTTON_POSITION_LEFT, 1);
    }
}
