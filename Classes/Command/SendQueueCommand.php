<?php

namespace WEBprofil\WpMailworkflow\Command;

use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\ServerRequestFactory;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use WEBprofil\WpMailworkflow\Domain\Model\Queue;
use WEBprofil\WpMailworkflow\Domain\Repository\QueueRepository;

/**
 * GenerateInvoicesCommandController
 */
class SendQueueCommand extends Command
{

    private ?QueueRepository $queueRepository = null;

    private ?MailerInterface $mailer = null;

    public function injectQueueRepository(QueueRepository $queueRepository): void
    {
        $this->queueRepository = $queueRepository;
    }

    public function injectMailer(MailerInterface $mailer): void
    {
        $this->mailer = $mailer;
    }

    public function __construct(
        private readonly ViewFactoryInterface $viewFactory
    ) {
        parent::__construct();
    }

    public function configure()
    {
        $this->setDescription('Send mails from the queue.')
            ->addArgument(
                'templatesPath',
                InputArgument::REQUIRED,
                'Path to mail templates: EXT:wp_mailworkflow/Resources/Private/Backend/Templates/'
            )
            ->addArgument(
                'partialsPath',
                InputArgument::REQUIRED,
                'Path to mail partials: EXT:wp_mailworkflow/Resources/Private/Backend/Partials/'
            )
            ->addArgument(
                'layoutsPath',
                InputArgument::REQUIRED,
                'Path to mail layouts: EXT:wp_mailworkflow/Resources/Private/Backend/Layouts/',
            )
            ->addArgument(
                'templateName',
                InputArgument::REQUIRED,
                'Template name for mails: Email'
            )
            ->addArgument(
                'senderMail',
                InputArgument::REQUIRED,
                'E-Mail address of the Mails sender'
            )
            ->addArgument(
                'senderName',
                InputArgument::REQUIRED,
                'Name of the Mails sender'
            );
    }

    /**
     *  GenerateInvoicesCommand
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $queues = $this->queueRepository->findToSend();
        foreach ($queues as $queue) {
            /** @var Queue $queue */
            $body = $this->generateBody($queue->getMail()->getMailtext(), $queue, $input);
            $email = GeneralUtility::makeInstance(MailMessage::class);
            $email->from(new Address($input->getArgument('senderMail'), $input->getArgument('senderName')))
                ->to(new Address($queue->getRecipient()->getEmail(), $queue->getRecipient()->getFirstName()))
                ->subject($queue->getMail()->getSubject())
                ->html($body);
            if ($queue->getMail()->getAttachment()) {
                $filePath = Environment::getPublicPath() . $queue->getMail()->getAttachment()->getOriginalResource()->getPublicUrl();
                $email->attachFromPath($filePath);
            }
            $this->mailer->send($email);
            $queue->setIsSent(true);
            $queue->setSent(new \DateTime);
            $this->queueRepository->update($queue);
        }
        $persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);
        $persistenceManager->persistAll();
        return Command::SUCCESS;
    }

    /**
     * @param string $mailtext
     * @param Queue $queue
     * @param InputInterface $input
     * @return array|string
     */
    private function generateBody(string $mailtext, Queue $queue, InputInterface $input): array|string
    {
        $bodytext = str_replace(array(
            "{firstName}",
            "{lastName}",
            "{email}",
            "{parameter1}",
            "{parameter2}",
            "{parameter3}",
            "{parameter4}",
            "{parameter5}"
        ), array(
            $queue->getRecipient()->getFirstName(),
            $queue->getRecipient()->getLastName(),
            $queue->getRecipient()->getEmail(),
            $queue->getRecipient()->getParameter1(),
            $queue->getRecipient()->getParameter2(),
            $queue->getRecipient()->getParameter3(),
            $queue->getRecipient()->getParameter4(),
            $queue->getRecipient()->getParameter5()
        ), $mailtext);

        // Erstellen einer ServerRequest-Instanz für CLI:
        /** @var ServerRequestInterface $request */
        $request = GeneralUtility::makeInstance(ServerRequestFactory::class)->createServerRequest('GET', '/');
        $request = $request->withAttribute('applicationType', 2);

        // ViewFactoryInterface (v13/v14-compatible replacement for StandaloneView):
        $viewFactoryData = new ViewFactoryData(
            templateRootPaths: [GeneralUtility::getFileAbsFileName($input->getArgument('templatesPath'))],
            partialRootPaths: [GeneralUtility::getFileAbsFileName($input->getArgument('partialsPath'))],
            layoutRootPaths: [GeneralUtility::getFileAbsFileName($input->getArgument('layoutsPath'))],
            request: $request,
        );
        $view = $this->viewFactory->create($viewFactoryData);
        $view->assign('bodytext', $bodytext);
        return $view->render($input->getArgument('templateName'));
    }

}
