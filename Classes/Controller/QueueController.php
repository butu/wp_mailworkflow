<?php

declare(strict_types=1);

namespace WEBprofil\WpMailworkflow\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Attribute\AsController;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use WEBprofil\WpMailworkflow\Domain\Model\Queue;
use WEBprofil\WpMailworkflow\Domain\Repository\QueueRepository;

/**
 * QueueController
 */
#[AsController]
class QueueController extends ActionController
{
    public function __construct(
        protected readonly ModuleTemplateFactory $moduleTemplateFactory,
        protected readonly IconFactory $iconFactory,
        private readonly QueueRepository $queueRepository
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
        $limit = (int)($this->settings['queue']['limit'] ?? 50);
        $queues = $this->queueRepository->findLast($limit);

        // Variablen an ModuleTemplate übergeben
        $moduleTemplate->assignMultiple([
            'queues' => $queues,
            'limit'  => $limit,
        ]);

        // Buttons
        $this->shortcutButton($buttonBar);
        $this->recipientButton($buttonBar);

        // Neu: direktes Rendering über ModuleTemplate
        // erwartet: Templates/Queue/List.html mit <f:layout name="Module" />
        return $moduleTemplate->renderResponse('Queue/List');
    }

    /**
     * action delete
     *
     * @throws IllegalObjectTypeException
     */
    public function deleteAction(Queue $queue): RedirectResponse
    {
        $this->queueRepository->remove($queue);
        return new RedirectResponse($this->uriBuilder->uriFor('list'));
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
     * recipient menu Button
     */
    private function recipientButton(ButtonBar $buttonBar): void
    {
        $url = $this->uriBuilder->reset()->uriFor('list', [], 'Recipient');

        $list = $buttonBar->makeLinkButton()
            ->setHref($url)
            ->setTitle('Recipient')
            ->setShowLabelText('Link')
            ->setIcon($this->iconFactory->getIcon('actions-heart', Icon::SIZE_SMALL));

        $buttonBar->addButton($list, ButtonBar::BUTTON_POSITION_LEFT, 1);
    }
}
