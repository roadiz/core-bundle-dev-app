<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use ParagonIE\HiddenString\HiddenString;
use RZ\Roadiz\CoreBundle\Crypto\UniqueKeyEncoderFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @package RZ\Roadiz\CoreBundle\Console
 */
final class EncodePrivateKeyCommand extends Command
{
    public function __construct(
        private readonly UniqueKeyEncoderFactory $uniqueKeyEncoderFactory,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName('crypto:private-key:encode')
            ->addArgument('key-name', InputArgument::REQUIRED)
            ->addArgument('data', InputArgument::REQUIRED)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $keyName  = $input->getArgument('key-name');
        $encoder = $this->uniqueKeyEncoderFactory->getEncoder($keyName);
        $encoded = $encoder->encode(new HiddenString($input->getArgument('data')));

        $io->note($encoded);
        return 0;
    }
}
