<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Command;

use RZ\Roadiz\RozierBundle\TranslateAssistant\TranslateAssistantInput;
use RZ\Roadiz\RozierBundle\TranslateAssistant\TranslateAssistantInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class TranslateAssistantTranslateCommand extends Command
{
    public function __construct(
        private readonly TranslateAssistantInterface $translateAssistant,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    #[\Override]
    protected function configure(): void
    {
        $this->setName('translate-assistant:translate')
            ->setDescription('Use translator assistant to translate a string.')
            ->addArgument(
                'text',
                InputArgument::REQUIRED,
                'Text to translate.'
            )
            ->addArgument(
                'lang',
                InputArgument::REQUIRED,
                'Set locale to translate to.'
            )
        ;
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $text = $input->getArgument('text');
        $lang = $input->getArgument('lang');

        if (!\is_string($text) || empty($text)) {
            throw new \InvalidArgumentException('Text argument is required.');
        }

        if (!\is_string($lang) || empty($lang)) {
            throw new \InvalidArgumentException('Lang option is required.');
        }

        $dto = new TranslateAssistantInput(
            $text,
            $lang
        );

        $result = $this->translateAssistant->translate($dto);

        $io->success($result->translatedText);

        return 0;
    }
}
