<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use RZ\Crypto\KeyChain\KeyChainInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class GeneratePrivateKeyCommand extends Command
{
    public function __construct(
        private readonly KeyChainInterface $keyChain,
        private readonly string $privateKeyName,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName('crypto:private-key:generate')
            ->setDescription('Generate a default private key to encode data in your database.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->keyChain->generate($this->privateKeyName);
        $io->success(sprintf('Private key has been generated: %s', $this->privateKeyName));
        return 0;
    }
}
