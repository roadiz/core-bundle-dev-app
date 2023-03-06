<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use ParagonIE\HiddenString\HiddenString;
use RZ\Crypto\KeyChain\KeyChainInterface;
use RZ\Roadiz\CoreBundle\Crypto\UniqueKeyEncoderFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @package RZ\Roadiz\CoreBundle\Console
 */
class EncodePrivateKeyCommand extends Command
{
    protected KeyChainInterface $keyChain;
    protected UniqueKeyEncoderFactory $uniqueKeyEncoderFactory;

    public function __construct(KeyChainInterface $keyChain, UniqueKeyEncoderFactory $uniqueKeyEncoderFactory)
    {
        parent::__construct();
        $this->keyChain = $keyChain;
        $this->uniqueKeyEncoderFactory = $uniqueKeyEncoderFactory;
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
