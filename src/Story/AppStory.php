<?php

declare(strict_types=1);

namespace App\Story;

use Zenstruck\Foundry\Attribute\AsFixture;
use Zenstruck\Foundry\Story;

use function Zenstruck\Foundry\faker;

#[AsFixture(name: 'main')]
final class AppStory extends Story
{
    #[\Override]
    public function build(): void
    {
        faker()->seed(20260306);

        TranslationsStory::load();
        PageHierarchyStory::load();
        FieldsStory::load();
        ArticleStory::load();
        TagStory::load();
        OfferStory::load();
    }
}
