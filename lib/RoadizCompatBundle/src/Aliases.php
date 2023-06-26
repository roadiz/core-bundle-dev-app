<?php

declare(strict_types=1);

namespace RZ\Roadiz\CompatBundle;

final class Aliases
{
    /**
     * @return array<class-string, class-string>
     */
    public static function getAliases(): array
    {
        return [
            \RZ\Roadiz\CompatBundle\Controller\AppController::class => \RZ\Roadiz\CMS\Controllers\AppController::class,
            \RZ\Roadiz\CompatBundle\Controller\Controller::class => \RZ\Roadiz\CMS\Controllers\Controller::class,
            \RZ\Roadiz\CompatBundle\Controller\FrontendController::class => \RZ\Roadiz\CMS\Controllers\FrontendController::class,
            \RZ\Roadiz\CompatBundle\Theme\ThemeResolverInterface::class => \RZ\Roadiz\Utils\Theme\ThemeResolverInterface::class,
            \RZ\Roadiz\CoreBundle\Bag\NodeTypes::class => \RZ\Roadiz\Core\Bags\NodeTypes::class,
            \RZ\Roadiz\CoreBundle\Bag\Roles::class => \RZ\Roadiz\Core\Bags\Roles::class,
            \RZ\Roadiz\CoreBundle\Bag\Settings::class => \RZ\Roadiz\Core\Bags\Settings::class,
            \RZ\Roadiz\CoreBundle\Configuration\CollectionFieldConfiguration::class => \RZ\Roadiz\Config\CollectionFieldConfiguration::class,
            \RZ\Roadiz\CoreBundle\Configuration\JoinNodeTypeFieldConfiguration::class => \RZ\Roadiz\Config\JoinNodeTypeFieldConfiguration::class,
            \RZ\Roadiz\CoreBundle\Configuration\ProviderFieldConfiguration::class => \RZ\Roadiz\Config\ProviderFieldConfiguration::class,
            \RZ\Roadiz\CoreBundle\CustomForm\CustomFormAnswerSerializer::class => \RZ\Roadiz\Utils\CustomForm\CustomFormAnswerSerializer::class,
            \RZ\Roadiz\CoreBundle\CustomForm\CustomFormHelper::class => \RZ\Roadiz\Utils\CustomForm\CustomFormHelper::class,
            \RZ\Roadiz\CoreBundle\DependencyInjection\Configuration::class => \RZ\Roadiz\Config\Configuration::class,
            \RZ\Roadiz\CoreBundle\Doctrine\Event\FilterNodesSourcesQueryBuilderCriteriaEvent::class => \RZ\Roadiz\Core\Events\FilterNodesSourcesQueryBuilderCriteriaEvent::class,
            \RZ\Roadiz\CoreBundle\Doctrine\Event\FilterQueryBuilderCriteriaEvent::class => \RZ\Roadiz\Core\Events\FilterQueryBuilderCriteriaEvent::class,
            \RZ\Roadiz\CoreBundle\Doctrine\Event\FilterQueryBuilderEvent::class => \RZ\Roadiz\Core\Events\FilterQueryBuilderEvent::class,
            \RZ\Roadiz\CoreBundle\Doctrine\Event\FilterQueryCriteriaEvent::class => \RZ\Roadiz\Core\Events\FilterQueryCriteriaEvent::class,
            \RZ\Roadiz\CoreBundle\Doctrine\Event\QueryBuilder\QueryBuilderApplyEvent::class => \RZ\Roadiz\Core\Events\QueryBuilder\QueryBuilderApplyEvent::class,
            \RZ\Roadiz\CoreBundle\Doctrine\Event\QueryBuilder\QueryBuilderBuildEvent::class => \RZ\Roadiz\Core\Events\QueryBuilder\QueryBuilderBuildEvent::class,
            \RZ\Roadiz\CoreBundle\Doctrine\Event\QueryBuilder\QueryBuilderNodesSourcesApplyEvent::class => \RZ\Roadiz\Core\Events\QueryBuilder\QueryBuilderNodesSourcesApplyEvent::class,
            \RZ\Roadiz\CoreBundle\Doctrine\Event\QueryBuilder\QueryBuilderNodesSourcesBuildEvent::class => \RZ\Roadiz\Core\Events\QueryBuilder\QueryBuilderNodesSourcesBuildEvent::class,
            \RZ\Roadiz\CoreBundle\Doctrine\Event\QueryBuilder\QueryBuilderSelectEvent::class => \RZ\Roadiz\Core\Events\QueryBuilder\QueryBuilderSelectEvent::class,
            \RZ\Roadiz\CoreBundle\Doctrine\Event\QueryEvent::class => \RZ\Roadiz\Core\Events\QueryEvent::class,
            \RZ\Roadiz\CoreBundle\Doctrine\Event\QueryNodesSourcesEvent::class => \RZ\Roadiz\Core\Events\QueryNodesSourcesEvent::class,
            \RZ\Roadiz\CoreBundle\Doctrine\SchemaUpdater::class => \RZ\Roadiz\Utils\Doctrine\SchemaUpdater::class,
            \RZ\Roadiz\CoreBundle\Document\DocumentFactory::class => \RZ\Roadiz\Utils\Document\DocumentFactory::class,
            \RZ\Roadiz\CoreBundle\EntityApi\NodeApi::class => \RZ\Roadiz\CMS\Utils\NodeApi::class,
            \RZ\Roadiz\CoreBundle\EntityApi\NodeSourceApi::class => \RZ\Roadiz\CMS\Utils\NodeSourceApi::class,
            \RZ\Roadiz\CoreBundle\EntityApi\NodeTypeApi::class => \RZ\Roadiz\CMS\Utils\NodeTypeApi::class,
            \RZ\Roadiz\CoreBundle\EntityApi\TagApi::class => \RZ\Roadiz\CMS\Utils\TagApi::class,
            \RZ\Roadiz\CoreBundle\EntityHandler\DocumentHandler::class => \RZ\Roadiz\Core\Handlers\DocumentHandler::class,
            \RZ\Roadiz\CoreBundle\EntityHandler\FolderHandler::class => \RZ\Roadiz\Core\Handlers\FolderHandler::class,
            \RZ\Roadiz\CoreBundle\EntityHandler\NodeHandler::class => \RZ\Roadiz\Core\Handlers\NodeHandler::class,
            \RZ\Roadiz\CoreBundle\EntityHandler\NodeTypeHandler::class => \RZ\Roadiz\Core\Handlers\NodeTypeHandler::class,
            \RZ\Roadiz\CoreBundle\EntityHandler\NodesSourcesHandler::class => \RZ\Roadiz\Core\Handlers\NodesSourcesHandler::class,
            \RZ\Roadiz\CoreBundle\EntityHandler\TagHandler::class => \RZ\Roadiz\Core\Handlers\TagHandler::class,
            \RZ\Roadiz\CoreBundle\EntityHandler\TranslationHandler::class => \RZ\Roadiz\Core\Handlers\TranslationHandler::class,
            \RZ\Roadiz\CoreBundle\Entity\Attribute::class => \RZ\Roadiz\Core\Entities\Attribute::class,
            \RZ\Roadiz\CoreBundle\Entity\AttributeDocuments::class => \RZ\Roadiz\Core\Entities\AttributeDocuments::class,
            \RZ\Roadiz\CoreBundle\Entity\AttributeGroup::class => \RZ\Roadiz\Core\Entities\AttributeGroup::class,
            \RZ\Roadiz\CoreBundle\Entity\AttributeGroupTranslation::class => \RZ\Roadiz\Core\Entities\AttributeGroupTranslation::class,
            \RZ\Roadiz\CoreBundle\Entity\AttributeTranslation::class => \RZ\Roadiz\Core\Entities\AttributeTranslation::class,
            \RZ\Roadiz\CoreBundle\Entity\AttributeValue::class => \RZ\Roadiz\Core\Entities\AttributeValue::class,
            \RZ\Roadiz\CoreBundle\Entity\AttributeValueTranslation::class => \RZ\Roadiz\Core\Entities\AttributeValueTranslation::class,
            \RZ\Roadiz\CoreBundle\Entity\CustomForm::class => \RZ\Roadiz\Core\Entities\CustomForm::class,
            \RZ\Roadiz\CoreBundle\Entity\CustomFormAnswer::class => \RZ\Roadiz\Core\Entities\CustomFormAnswer::class,
            \RZ\Roadiz\CoreBundle\Entity\CustomFormField::class => \RZ\Roadiz\Core\Entities\CustomFormField::class,
            \RZ\Roadiz\CoreBundle\Entity\CustomFormFieldAttribute::class => \RZ\Roadiz\Core\Entities\CustomFormFieldAttribute::class,
            \RZ\Roadiz\CoreBundle\Entity\Document::class => \RZ\Roadiz\Core\Entities\Document::class,
            \RZ\Roadiz\CoreBundle\Entity\DocumentTranslation::class => \RZ\Roadiz\Core\Entities\DocumentTranslation::class,
            \RZ\Roadiz\CoreBundle\Entity\Folder::class => \RZ\Roadiz\Core\Entities\Folder::class,
            \RZ\Roadiz\CoreBundle\Entity\FolderTranslation::class => \RZ\Roadiz\Core\Entities\FolderTranslation::class,
            \RZ\Roadiz\CoreBundle\Entity\Group::class => \RZ\Roadiz\Core\Entities\Group::class,
            \RZ\Roadiz\CoreBundle\Entity\Log::class => \RZ\Roadiz\Core\Entities\Log::class,
            \RZ\Roadiz\CoreBundle\Entity\LoginAttempt::class => \RZ\Roadiz\Core\Entities\LoginAttempt::class,
            \RZ\Roadiz\CoreBundle\Entity\Node::class => \RZ\Roadiz\Core\Entities\Node::class,
            \RZ\Roadiz\CoreBundle\Entity\NodeType::class => \RZ\Roadiz\Core\Entities\NodeType::class,
            \RZ\Roadiz\CoreBundle\Entity\NodeTypeField::class => \RZ\Roadiz\Core\Entities\NodeTypeField::class,
            \RZ\Roadiz\CoreBundle\Entity\NodesCustomForms::class => \RZ\Roadiz\Core\Entities\NodesCustomForms::class,
            \RZ\Roadiz\CoreBundle\Entity\NodesSources::class => \RZ\Roadiz\Core\Entities\NodesSources::class,
            \RZ\Roadiz\CoreBundle\Entity\NodesSourcesDocuments::class => \RZ\Roadiz\Core\Entities\NodesSourcesDocuments::class,
            \RZ\Roadiz\CoreBundle\Entity\NodesToNodes::class => \RZ\Roadiz\Core\Entities\NodesToNodes::class,
            \RZ\Roadiz\CoreBundle\Entity\Redirection::class => \RZ\Roadiz\Core\Entities\Redirection::class,
            \RZ\Roadiz\CoreBundle\Entity\Role::class => \RZ\Roadiz\Core\Entities\Role::class,
            \RZ\Roadiz\CoreBundle\Entity\Setting::class => \RZ\Roadiz\Core\Entities\Setting::class,
            \RZ\Roadiz\CoreBundle\Entity\SettingGroup::class => \RZ\Roadiz\Core\Entities\SettingGroup::class,
            \RZ\Roadiz\CoreBundle\Entity\Tag::class => \RZ\Roadiz\Core\Entities\Tag::class,
            \RZ\Roadiz\CoreBundle\Entity\TagTranslation::class => \RZ\Roadiz\Core\Entities\TagTranslation::class,
            \RZ\Roadiz\CoreBundle\Entity\TagTranslationDocuments::class => \RZ\Roadiz\Core\Entities\TagTranslationDocuments::class,
            \RZ\Roadiz\CoreBundle\Entity\Theme::class => \RZ\Roadiz\Core\Entities\Theme::class,
            \RZ\Roadiz\CoreBundle\Entity\Translation::class => \RZ\Roadiz\Core\Entities\Translation::class,
            \RZ\Roadiz\CoreBundle\Entity\UrlAlias::class => \RZ\Roadiz\Core\Entities\UrlAlias::class,
            \RZ\Roadiz\CoreBundle\Entity\User::class => \RZ\Roadiz\Core\Entities\User::class,
            \RZ\Roadiz\CoreBundle\Entity\UserLogEntry::class => \RZ\Roadiz\Core\Entities\UserLogEntry::class,
            \RZ\Roadiz\CoreBundle\Entity\Webhook::class => \RZ\Roadiz\Webhook\Entity\Webhook::class,
            \RZ\Roadiz\Documents\Events\CachePurgeAssetsRequestEvent::class => \RZ\Roadiz\Core\Events\Cache\CachePurgeAssetsRequestEvent::class,
            \RZ\Roadiz\CoreBundle\Event\Cache\CachePurgeRequestEvent::class => \RZ\Roadiz\Core\Events\Cache\CachePurgeRequestEvent::class,
            \RZ\Roadiz\CoreBundle\Event\Document\DocumentTranslationUpdatedEvent::class => \RZ\Roadiz\Core\Events\DocumentTranslationUpdatedEvent::class,
            \RZ\Roadiz\CoreBundle\Event\FilterCacheEvent::class => \RZ\Roadiz\Core\Events\FilterCacheEvent::class,
            \RZ\Roadiz\CoreBundle\Event\FilterFolderEvent::class => \RZ\Roadiz\Core\Events\FilterFolderEvent::class,
            \RZ\Roadiz\CoreBundle\Event\FilterNodeEvent::class => \RZ\Roadiz\Core\Events\FilterNodeEvent::class,
            \RZ\Roadiz\CoreBundle\Event\FilterNodePathEvent::class => \RZ\Roadiz\Core\Events\FilterNodePathEvent::class,
            \RZ\Roadiz\CoreBundle\Event\FilterNodesSourcesEvent::class => \RZ\Roadiz\Core\Events\FilterNodesSourcesEvent::class,
            \RZ\Roadiz\CoreBundle\Event\FilterTagEvent::class => \RZ\Roadiz\Core\Events\FilterTagEvent::class,
            \RZ\Roadiz\CoreBundle\Event\FilterTranslationEvent::class => \RZ\Roadiz\Core\Events\FilterTranslationEvent::class,
            \RZ\Roadiz\CoreBundle\Event\FilterUrlAliasEvent::class => \RZ\Roadiz\Core\Events\FilterUrlAliasEvent::class,
            \RZ\Roadiz\CoreBundle\Event\FilterUserEvent::class => \RZ\Roadiz\Core\Events\FilterUserEvent::class,
            \RZ\Roadiz\CoreBundle\Event\Folder\FolderCreatedEvent::class => \RZ\Roadiz\Core\Events\Folder\FolderCreatedEvent::class,
            \RZ\Roadiz\CoreBundle\Event\Folder\FolderDeletedEvent::class => \RZ\Roadiz\Core\Events\Folder\FolderDeletedEvent::class,
            \RZ\Roadiz\CoreBundle\Event\Folder\FolderUpdatedEvent::class => \RZ\Roadiz\Core\Events\Folder\FolderUpdatedEvent::class,
            \RZ\Roadiz\CoreBundle\Event\Node\NodeCreatedEvent::class => \RZ\Roadiz\Core\Events\Node\NodeCreatedEvent::class,
            \RZ\Roadiz\CoreBundle\Event\Node\NodeDeletedEvent::class => \RZ\Roadiz\Core\Events\Node\NodeDeletedEvent::class,
            \RZ\Roadiz\CoreBundle\Event\Node\NodeDuplicatedEvent::class => \RZ\Roadiz\Core\Events\Node\NodeDuplicatedEvent::class,
            \RZ\Roadiz\CoreBundle\Event\Node\NodePathChangedEvent::class => \RZ\Roadiz\Core\Events\Node\NodePathChangedEvent::class,
            \RZ\Roadiz\CoreBundle\Event\Node\NodeTaggedEvent::class => \RZ\Roadiz\Core\Events\Node\NodeTaggedEvent::class,
            \RZ\Roadiz\CoreBundle\Event\Node\NodeUndeletedEvent::class => \RZ\Roadiz\Core\Events\Node\NodeUndeletedEvent::class,
            \RZ\Roadiz\CoreBundle\Event\Node\NodeUpdatedEvent::class => \RZ\Roadiz\Core\Events\Node\NodeUpdatedEvent::class,
            \RZ\Roadiz\CoreBundle\Event\Node\NodeVisibilityChangedEvent::class => \RZ\Roadiz\Core\Events\Node\NodeVisibilityChangedEvent::class,
            \RZ\Roadiz\CoreBundle\Event\NodesSources\NodesSourcesCreatedEvent::class => \RZ\Roadiz\Core\Events\NodesSources\NodesSourcesCreatedEvent::class,
            \RZ\Roadiz\CoreBundle\Event\NodesSources\NodesSourcesDeletedEvent::class => \RZ\Roadiz\Core\Events\NodesSources\NodesSourcesDeletedEvent::class,
            \RZ\Roadiz\CoreBundle\Event\NodesSources\NodesSourcesIndexingEvent::class => \RZ\Roadiz\Core\Events\NodesSources\NodesSourcesIndexingEvent::class,
            \RZ\Roadiz\CoreBundle\Event\NodesSources\NodesSourcesPathGeneratingEvent::class => \RZ\Roadiz\Core\Events\NodesSources\NodesSourcesPathGeneratingEvent::class,
            \RZ\Roadiz\CoreBundle\Event\NodesSources\NodesSourcesPreUpdatedEvent::class => \RZ\Roadiz\Core\Events\NodesSources\NodesSourcesPreUpdatedEvent::class,
            \RZ\Roadiz\CoreBundle\Event\NodesSources\NodesSourcesUpdatedEvent::class => \RZ\Roadiz\Core\Events\NodesSources\NodesSourcesUpdatedEvent::class,
            \RZ\Roadiz\CoreBundle\Event\Role\PreCreatedRoleEvent::class => \RZ\Roadiz\Core\Events\Role\PreCreatedRoleEvent::class,
            \RZ\Roadiz\CoreBundle\Event\Role\PreDeletedRoleEvent::class => \RZ\Roadiz\Core\Events\Role\PreDeletedRoleEvent::class,
            \RZ\Roadiz\CoreBundle\Event\Role\PreUpdatedRoleEvent::class => \RZ\Roadiz\Core\Events\Role\PreUpdatedRoleEvent::class,
            \RZ\Roadiz\CoreBundle\Event\Role\RoleEvent::class => \RZ\Roadiz\Core\Events\Role\RoleEvent::class,
            \RZ\Roadiz\CoreBundle\Event\Tag\TagCreatedEvent::class => \RZ\Roadiz\Core\Events\Tag\TagCreatedEvent::class,
            \RZ\Roadiz\CoreBundle\Event\Tag\TagDeletedEvent::class => \RZ\Roadiz\Core\Events\Tag\TagDeletedEvent::class,
            \RZ\Roadiz\CoreBundle\Event\Tag\TagUpdatedEvent::class => \RZ\Roadiz\Core\Events\Tag\TagUpdatedEvent::class,
            \RZ\Roadiz\CoreBundle\Event\Translation\TranslationCreatedEvent::class => \RZ\Roadiz\Core\Events\Translation\TranslationCreatedEvent::class,
            \RZ\Roadiz\CoreBundle\Event\Translation\TranslationDeletedEvent::class => \RZ\Roadiz\Core\Events\Translation\TranslationDeletedEvent::class,
            \RZ\Roadiz\CoreBundle\Event\Translation\TranslationUpdatedEvent::class => \RZ\Roadiz\Core\Events\Translation\TranslationUpdatedEvent::class,
            \RZ\Roadiz\CoreBundle\Event\UrlAlias\UrlAliasCreatedEvent::class => \RZ\Roadiz\Core\Events\UrlAlias\UrlAliasCreatedEvent::class,
            \RZ\Roadiz\CoreBundle\Event\UrlAlias\UrlAliasDeletedEvent::class => \RZ\Roadiz\Core\Events\UrlAlias\UrlAliasDeletedEvent::class,
            \RZ\Roadiz\CoreBundle\Event\UrlAlias\UrlAliasUpdatedEvent::class => \RZ\Roadiz\Core\Events\UrlAlias\UrlAliasUpdatedEvent::class,
            \RZ\Roadiz\CoreBundle\Event\User\UserCreatedEvent::class => \RZ\Roadiz\Core\Events\User\UserCreatedEvent::class,
            \RZ\Roadiz\CoreBundle\Event\User\UserDeletedEvent::class => \RZ\Roadiz\Core\Events\User\UserDeletedEvent::class,
            \RZ\Roadiz\CoreBundle\Event\User\UserDisabledEvent::class => \RZ\Roadiz\Core\Events\User\UserDisabledEvent::class,
            \RZ\Roadiz\CoreBundle\Event\User\UserEnabledEvent::class => \RZ\Roadiz\Core\Events\User\UserEnabledEvent::class,
            \RZ\Roadiz\CoreBundle\Event\User\UserPasswordChangedEvent::class => \RZ\Roadiz\Core\Events\User\UserPasswordChangedEvent::class,
            \RZ\Roadiz\CoreBundle\Event\User\UserUpdatedEvent::class => \RZ\Roadiz\Core\Events\User\UserUpdatedEvent::class,
            \RZ\Roadiz\CoreBundle\Event\User\UserJoinedGroupEvent::class => \RZ\Roadiz\Core\Events\User\UserJoinedGroupEvent::class,
            \RZ\Roadiz\CoreBundle\Event\User\UserLeavedGroupEvent::class => \RZ\Roadiz\Core\Events\User\UserLeavedGroupEvent::class,
            \RZ\Roadiz\CoreBundle\Exception\EntityAlreadyExistsException::class => \RZ\Roadiz\Core\Exceptions\EntityAlreadyExistsException::class,
            \RZ\Roadiz\CoreBundle\Exception\ForceResponseException::class => \RZ\Roadiz\Core\Exceptions\ForceResponseException::class,
            \RZ\Roadiz\CoreBundle\Exception\NoTranslationAvailableException::class => \RZ\Roadiz\Core\Exceptions\NoTranslationAvailableException::class,
            \RZ\Roadiz\CoreBundle\Node\Exception\SameNodeUrlException::class => \RZ\Roadiz\Utils\Node\Exception\SameNodeUrlException::class,
            \RZ\Roadiz\CoreBundle\Explorer\AbstractExplorerProvider::class => \RZ\Roadiz\Explorer\AbstractExplorerProvider::class,
            \RZ\Roadiz\CoreBundle\Explorer\AbstractExplorerItem::class => \RZ\Roadiz\Explorer\AbstractExplorerItem::class,
            \RZ\Roadiz\CoreBundle\Explorer\AbstractDoctrineExplorerProvider::class => \RZ\Roadiz\Explorer\AbstractDoctrineExplorerProvider::class,
            \RZ\Roadiz\CoreBundle\Explorer\ExplorerItemInterface::class => \RZ\Roadiz\Explorer\ExplorerItemInterface::class,
            \RZ\Roadiz\CoreBundle\Explorer\ExplorerProviderInterface::class => \RZ\Roadiz\Explorer\ExplorerProviderInterface::class,
            \RZ\Roadiz\CoreBundle\Form\AttributeChoiceType::class => \RZ\Roadiz\Attribute\Form\AttributeChoiceType::class,
            \RZ\Roadiz\CoreBundle\Form\AttributeDocumentType::class => \RZ\Roadiz\Attribute\Form\AttributeDocumentType::class,
            \RZ\Roadiz\CoreBundle\Form\AttributeGroupTranslationType::class => \RZ\Roadiz\Attribute\Form\AttributeGroupTranslationType::class,
            \RZ\Roadiz\CoreBundle\Form\AttributeGroupType::class => \RZ\Roadiz\Attribute\Form\AttributeGroupType::class,
            \RZ\Roadiz\CoreBundle\Form\AttributeGroupsType::class => \RZ\Roadiz\Attribute\Form\AttributeGroupsType::class,
            \RZ\Roadiz\CoreBundle\Form\AttributeImportType::class => \RZ\Roadiz\Attribute\Form\AttributeImportType::class,
            \RZ\Roadiz\CoreBundle\Form\AttributeTranslationType::class => \RZ\Roadiz\Attribute\Form\AttributeTranslationType::class,
            \RZ\Roadiz\CoreBundle\Form\AttributeType::class => \RZ\Roadiz\Attribute\Form\AttributeType::class,
            \RZ\Roadiz\CoreBundle\Form\AttributeValueTranslationType::class => \RZ\Roadiz\Attribute\Form\AttributeValueTranslationType::class,
            \RZ\Roadiz\CoreBundle\Form\AttributeValueType::class => \RZ\Roadiz\Attribute\Form\AttributeValueType::class,
            \RZ\Roadiz\CoreBundle\Form\ColorType::class => \RZ\Roadiz\CMS\Forms\ColorType::class,
            \RZ\Roadiz\CoreBundle\Form\CompareDateType::class => \RZ\Roadiz\CMS\Forms\CompareDateType::class,
            \RZ\Roadiz\CoreBundle\Form\CompareDatetimeType::class => \RZ\Roadiz\CMS\Forms\CompareDatetimeType::class,
            \RZ\Roadiz\CoreBundle\Form\Constraint\HexadecimalColor::class => \RZ\Roadiz\CMS\Forms\Constraints\HexadecimalColor::class,
            \RZ\Roadiz\CoreBundle\Form\Constraint\HexadecimalColorValidator::class => \RZ\Roadiz\CMS\Forms\Constraints\HexadecimalColorValidator::class,
            \RZ\Roadiz\CoreBundle\Form\Constraint\NodeTypeField::class => \RZ\Roadiz\CMS\Forms\Constraints\NodeTypeField::class,
            \RZ\Roadiz\CoreBundle\Form\Constraint\NodeTypeFieldValidator::class => \RZ\Roadiz\CMS\Forms\Constraints\NodeTypeFieldValidator::class,
            \RZ\Roadiz\CoreBundle\Form\Constraint\NonSqlReservedWord::class => \RZ\Roadiz\CMS\Forms\Constraints\NonSqlReservedWord::class,
            \RZ\Roadiz\CoreBundle\Form\Constraint\NonSqlReservedWordValidator::class => \RZ\Roadiz\CMS\Forms\Constraints\NonSqlReservedWordValidator::class,
            \RZ\Roadiz\CoreBundle\Form\Constraint\Recaptcha::class => \RZ\Roadiz\CMS\Forms\Constraints\Recaptcha::class,
            \RZ\Roadiz\CoreBundle\Form\Constraint\RecaptchaValidator::class => \RZ\Roadiz\CMS\Forms\Constraints\RecaptchaValidator::class,
            \RZ\Roadiz\CoreBundle\Form\Constraint\SimpleLatinString::class => \RZ\Roadiz\CMS\Forms\Constraints\SimpleLatinString::class,
            \RZ\Roadiz\CoreBundle\Form\Constraint\SimpleLatinStringValidator::class => \RZ\Roadiz\CMS\Forms\Constraints\SimpleLatinStringValidator::class,
            \RZ\Roadiz\CoreBundle\Form\Constraint\UniqueFilename::class => \RZ\Roadiz\CMS\Forms\Constraints\UniqueFilename::class,
            \RZ\Roadiz\CoreBundle\Form\Constraint\UniqueFilenameValidator::class => \RZ\Roadiz\CMS\Forms\Constraints\UniqueFilenameValidator::class,
            \RZ\Roadiz\CoreBundle\Form\Constraint\UniqueNodeName::class => \RZ\Roadiz\CMS\Forms\Constraints\UniqueNodeName::class,
            \RZ\Roadiz\CoreBundle\Form\Constraint\UniqueNodeNameValidator::class => \RZ\Roadiz\CMS\Forms\Constraints\UniqueNodeNameValidator::class,
            \RZ\Roadiz\CoreBundle\Form\Constraint\UniqueTagName::class => \RZ\Roadiz\CMS\Forms\Constraints\UniqueTagName::class,
            \RZ\Roadiz\CoreBundle\Form\Constraint\UniqueTagNameValidator::class => \RZ\Roadiz\CMS\Forms\Constraints\UniqueTagNameValidator::class,
            \RZ\Roadiz\CoreBundle\Form\Constraint\ValidAccountConfirmationToken::class => \RZ\Roadiz\CMS\Forms\Constraints\ValidAccountConfirmationToken::class,
            \RZ\Roadiz\CoreBundle\Form\Constraint\ValidAccountConfirmationTokenValidator::class => \RZ\Roadiz\CMS\Forms\Constraints\ValidAccountConfirmationTokenValidator::class,
            \RZ\Roadiz\CoreBundle\Form\Constraint\ValidAccountEmail::class => \RZ\Roadiz\CMS\Forms\Constraints\ValidAccountEmail::class,
            \RZ\Roadiz\CoreBundle\Form\Constraint\ValidAccountEmailValidator::class => \RZ\Roadiz\CMS\Forms\Constraints\ValidAccountEmailValidator::class,
            \RZ\Roadiz\CoreBundle\Form\Constraint\ValidFacebookName::class => \RZ\Roadiz\CMS\Forms\Constraints\ValidFacebookName::class,
            \RZ\Roadiz\CoreBundle\Form\Constraint\ValidFacebookNameValidator::class => \RZ\Roadiz\CMS\Forms\Constraints\ValidFacebookNameValidator::class,
            \RZ\Roadiz\CoreBundle\Form\Constraint\ValidYaml::class => \RZ\Roadiz\CMS\Forms\Constraints\ValidYaml::class,
            \RZ\Roadiz\CoreBundle\Form\Constraint\ValidYamlValidator::class => \RZ\Roadiz\CMS\Forms\Constraints\ValidYamlValidator::class,
            \RZ\Roadiz\CoreBundle\Form\CreatePasswordType::class => \RZ\Roadiz\CMS\Forms\CreatePasswordType::class,
            \RZ\Roadiz\CoreBundle\Form\CssType::class => \RZ\Roadiz\CMS\Forms\CssType::class,
            \RZ\Roadiz\CoreBundle\Form\CustomFormsType::class => \RZ\Roadiz\CMS\Forms\CustomFormsType::class,
            \RZ\Roadiz\CoreBundle\Form\DataTransformer\DocumentCollectionTransformer::class => \RZ\Roadiz\CMS\Forms\DataTransformer\DocumentCollectionTransformer::class,
            \RZ\Roadiz\CoreBundle\Form\DataTransformer\EntityCollectionTransformer::class => \RZ\Roadiz\CMS\Forms\DataTransformer\EntityCollectionTransformer::class,
            \RZ\Roadiz\CoreBundle\Form\DataTransformer\ExplorerProviderItemTransformer::class => \RZ\Roadiz\CMS\Forms\DataTransformer\ExplorerProviderItemTransformer::class,
            \RZ\Roadiz\CoreBundle\Form\DataTransformer\FolderCollectionTransformer::class => \RZ\Roadiz\CMS\Forms\DataTransformer\FolderCollectionTransformer::class,
            \RZ\Roadiz\CoreBundle\Form\DataTransformer\JoinDataTransformer::class => \RZ\Roadiz\CMS\Forms\DataTransformer\JoinDataTransformer::class,
            \RZ\Roadiz\CoreBundle\Form\DataTransformer\NodeTypeTransformer::class => \RZ\Roadiz\CMS\Forms\DataTransformer\NodeTypeTransformer::class,
            \RZ\Roadiz\CoreBundle\Form\DataTransformer\PersistableTransformer::class => \RZ\Roadiz\CMS\Forms\DataTransformer\PersistableTransformer::class,
            \RZ\Roadiz\CoreBundle\Form\DataTransformer\ProviderDataTransformer::class => \RZ\Roadiz\CMS\Forms\DataTransformer\ProviderDataTransformer::class,
            \RZ\Roadiz\CoreBundle\Form\DataTransformer\ReversePersistableTransformer::class => \RZ\Roadiz\CMS\Forms\DataTransformer\ReversePersistableTransformer::class,
            \RZ\Roadiz\CoreBundle\Form\DataTransformer\TagTranslationDocumentsTransformer::class => \RZ\Roadiz\CMS\Forms\DataTransformer\TagTranslationDocumentsTransformer::class,
            \RZ\Roadiz\CoreBundle\Form\DataTransformer\TranslationTransformer::class => \RZ\Roadiz\CMS\Forms\DataTransformer\TranslationTransformer::class,
            \RZ\Roadiz\CoreBundle\Form\DocumentCollectionType::class => \RZ\Roadiz\CMS\Forms\DocumentCollectionType::class,
            \RZ\Roadiz\CoreBundle\Form\EnumerationType::class => \RZ\Roadiz\CMS\Forms\EnumerationType::class,
            \RZ\Roadiz\CoreBundle\Form\ExplorerProviderItemType::class => \RZ\Roadiz\CMS\Forms\ExplorerProviderItemType::class,
            \RZ\Roadiz\CoreBundle\Form\ExtendedBooleanType::class => \RZ\Roadiz\CMS\Forms\ExtendedBooleanType::class,
            \RZ\Roadiz\CoreBundle\Form\GroupsType::class => \RZ\Roadiz\CMS\Forms\GroupsType::class,
            \RZ\Roadiz\CoreBundle\Form\HoneypotType::class => \RZ\Roadiz\CMS\Forms\HoneypotType::class,
            \RZ\Roadiz\CoreBundle\Form\JsonType::class => \RZ\Roadiz\CMS\Forms\JsonType::class,
            \RZ\Roadiz\CoreBundle\Form\LoginRequestForm::class => \RZ\Roadiz\CMS\Forms\LoginRequestForm::class,
            \RZ\Roadiz\CoreBundle\Form\LoginResetForm::class => \RZ\Roadiz\CMS\Forms\LoginResetForm::class,
            \RZ\Roadiz\CoreBundle\Form\MarkdownType::class => \RZ\Roadiz\CMS\Forms\MarkdownType::class,
            \RZ\Roadiz\CoreBundle\Form\MultipleEnumerationType::class => \RZ\Roadiz\CMS\Forms\MultipleEnumerationType::class,
            \RZ\Roadiz\CoreBundle\Form\NodeStatesType::class => \RZ\Roadiz\CMS\Forms\NodeStatesType::class,
            \RZ\Roadiz\CoreBundle\Form\NodeTypesType::class => \RZ\Roadiz\CMS\Forms\NodeTypesType::class,
            \RZ\Roadiz\CoreBundle\Form\NodesType::class => \RZ\Roadiz\CMS\Forms\NodesType::class,
            \RZ\Roadiz\CoreBundle\Form\RecaptchaType::class => \RZ\Roadiz\CMS\Forms\RecaptchaType::class,
            \RZ\Roadiz\CoreBundle\Form\RolesType::class => \RZ\Roadiz\CMS\Forms\RolesType::class,
            \RZ\Roadiz\CoreBundle\Form\SeparatorType::class => \RZ\Roadiz\CMS\Forms\SeparatorType::class,
            \RZ\Roadiz\CoreBundle\Form\SettingDocumentType::class => \RZ\Roadiz\CMS\Forms\SettingDocumentType::class,
            \RZ\Roadiz\CoreBundle\Form\SettingGroupType::class => \RZ\Roadiz\CMS\Forms\SettingGroupType::class,
            \RZ\Roadiz\CoreBundle\Form\SettingType::class => \RZ\Roadiz\CMS\Forms\SettingType::class,
            \RZ\Roadiz\CoreBundle\Form\SettingTypeResolver::class => \RZ\Roadiz\CMS\Forms\SettingTypeResolver::class,
            \RZ\Roadiz\CoreBundle\Form\TagTranslationDocumentType::class => \RZ\Roadiz\CMS\Forms\TagTranslationDocumentType::class,
            \RZ\Roadiz\CoreBundle\Form\TagsType::class => \RZ\Roadiz\CMS\Forms\TagsType::class,
            \RZ\Roadiz\CoreBundle\Form\ThemesType::class => \RZ\Roadiz\CMS\Forms\ThemesType::class,
            \RZ\Roadiz\CoreBundle\Form\TranslationsType::class => \RZ\Roadiz\CMS\Forms\TranslationsType::class,
            \RZ\Roadiz\CoreBundle\Form\UrlAliasType::class => \RZ\Roadiz\CMS\Forms\UrlAliasType::class,
            \RZ\Roadiz\CoreBundle\Form\UsersType::class => \RZ\Roadiz\CMS\Forms\UsersType::class,
            \RZ\Roadiz\CoreBundle\Form\WebhookType::class => \RZ\Roadiz\Webhook\Form\WebhookType::class,
            \RZ\Roadiz\CoreBundle\Form\YamlType::class => \RZ\Roadiz\CMS\Forms\YamlType::class,
            \RZ\Roadiz\CoreBundle\Importer\AttributeImporter::class => \RZ\Roadiz\Attribute\Importer\AttributeImporter::class,
            \RZ\Roadiz\CoreBundle\Importer\ChainImporter::class => \RZ\Roadiz\CMS\Importers\ChainImporter::class,
            \RZ\Roadiz\CoreBundle\Importer\GroupsImporter::class => \RZ\Roadiz\CMS\Importers\GroupsImporter::class,
            \RZ\Roadiz\CoreBundle\Importer\NodeTypesImporter::class => \RZ\Roadiz\CMS\Importers\NodeTypesImporter::class,
            \RZ\Roadiz\CoreBundle\Importer\RolesImporter::class => \RZ\Roadiz\CMS\Importers\RolesImporter::class,
            \RZ\Roadiz\CoreBundle\Importer\SettingsImporter::class => \RZ\Roadiz\CMS\Importers\SettingsImporter::class,
            \RZ\Roadiz\CoreBundle\Importer\TagsImporter::class => \RZ\Roadiz\CMS\Importers\TagsImporter::class,
            \RZ\Roadiz\CoreBundle\ListManager\EntityListManager::class => \RZ\Roadiz\Core\ListManagers\EntityListManager::class,
            \RZ\Roadiz\CoreBundle\ListManager\EntityListManagerInterface::class => \RZ\Roadiz\Core\ListManagers\EntityListManagerInterface::class,
            \RZ\Roadiz\CoreBundle\ListManager\NodePaginator::class => \RZ\Roadiz\Core\ListManagers\NodePaginator::class,
            \RZ\Roadiz\CoreBundle\ListManager\NodesSourcesPaginator::class => \RZ\Roadiz\Core\ListManagers\NodesSourcesPaginator::class,
            \RZ\Roadiz\CoreBundle\ListManager\Paginator::class => \RZ\Roadiz\Core\ListManagers\Paginator::class,
            \RZ\Roadiz\CoreBundle\ListManager\QueryBuilderListManager::class => \RZ\Roadiz\Core\ListManagers\QueryBuilderListManager::class,
            \RZ\Roadiz\CoreBundle\ListManager\TagListManager::class => \RZ\Roadiz\Core\ListManagers\TagListManager::class,
            \RZ\Roadiz\CoreBundle\Mailer\ContactFormManager::class => \RZ\Roadiz\Utils\ContactFormManager::class,
            \RZ\Roadiz\CoreBundle\Mailer\EmailManager::class => \RZ\Roadiz\Utils\EmailManager::class,
            \RZ\Roadiz\CoreBundle\Node\NodeDuplicator::class => \RZ\Roadiz\Utils\Node\NodeDuplicator::class,
            \RZ\Roadiz\CoreBundle\Node\NodeFactory::class => \RZ\Roadiz\Utils\Node\NodeFactory::class,
            \RZ\Roadiz\CoreBundle\Node\NodeMover::class => \RZ\Roadiz\Utils\Node\NodeMover::class,
            \RZ\Roadiz\CoreBundle\Node\NodeNameChecker::class => \RZ\Roadiz\Utils\Node\NodeNameChecker::class,
            \RZ\Roadiz\CoreBundle\Node\NodeNamePolicyInterface::class => \RZ\Roadiz\Utils\Node\NodeNamePolicyInterface::class,
            \RZ\Roadiz\CoreBundle\Node\NodeTranstyper::class => \RZ\Roadiz\Utils\Node\NodeTranstyper::class,
            \RZ\Roadiz\CoreBundle\Node\UniqueNodeGenerator::class => \RZ\Roadiz\Utils\Node\UniqueNodeGenerator::class,
            \RZ\Roadiz\CoreBundle\Node\UniversalDataDuplicator::class => \RZ\Roadiz\Utils\Node\UniversalDataDuplicator::class,
            \RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface::class => \RZ\Roadiz\Preview\PreviewResolverInterface::class,
            \RZ\Roadiz\CoreBundle\Repository\DocumentRepository::class => \RZ\Roadiz\Core\Repositories\DocumentRepository::class,
            \RZ\Roadiz\CoreBundle\Repository\EntityRepository::class => \RZ\Roadiz\Core\Repositories\EntityRepository::class,
            \RZ\Roadiz\CoreBundle\Repository\NodeRepository::class => \RZ\Roadiz\Core\Repositories\NodeRepository::class,
            \RZ\Roadiz\CoreBundle\Repository\TagRepository::class => \RZ\Roadiz\Core\Repositories\TagRepository::class,
            \RZ\Roadiz\CoreBundle\Repository\TranslationRepository::class => \RZ\Roadiz\Core\Repositories\TranslationRepository::class,
            \RZ\Roadiz\CoreBundle\Routing\NodeRouteHelper::class => \RZ\Roadiz\Core\Routing\NodeRouteHelper::class,
            \RZ\Roadiz\CoreBundle\Routing\NodeRouter::class => \RZ\Roadiz\Core\Routing\NodeRouter::class,
            \RZ\Roadiz\CoreBundle\SearchEngine\GlobalNodeSourceSearchHandler::class => \RZ\Roadiz\Core\SearchEngine\GlobalNodeSourceSearchHandler::class,
            \RZ\Roadiz\CoreBundle\SearchEngine\NodeSourceSearchHandlerInterface::class => \RZ\Roadiz\Core\SearchEngine\NodeSourceSearchHandlerInterface::class,
            \RZ\Roadiz\CoreBundle\Security\Authorization\Chroot\NodeChrootResolver::class => \RZ\Roadiz\Core\Authorization\Chroot\NodeChrootResolver::class,
            \RZ\Roadiz\CoreBundle\Tag\TagFactory::class => \RZ\Roadiz\Utils\Tag\TagFactory::class,
            \RZ\Roadiz\CoreBundle\Traits\LoginResetTrait::class => \RZ\Roadiz\CMS\Traits\LoginResetTrait::class,
            \RZ\Roadiz\CoreBundle\Webhook\WebhookDispatcher::class => \RZ\Roadiz\Webhook\WebhookDispatcher::class,
            \RZ\Roadiz\CoreBundle\Webhook\Exception\TooManyWebhookTriggeredException::class => \RZ\Roadiz\Webhook\Exception\TooManyWebhookTriggeredException::class,
            \RZ\Roadiz\CoreBundle\Xlsx\AbstractXlsxSerializer::class => \RZ\Roadiz\Core\Serializers\AbstractXlsxSerializer::class,
            \RZ\Roadiz\CoreBundle\Xlsx\NodeSourceXlsxSerializer::class => \RZ\Roadiz\Core\Serializers\NodeSourceXlsxSerializer::class,
            \RZ\Roadiz\CoreBundle\Xlsx\SerializerInterface::class => \RZ\Roadiz\Core\Serializers\SerializerInterface::class,
            \RZ\Roadiz\CoreBundle\Xlsx\XlsxExporter::class => \RZ\Roadiz\Utils\XlsxExporter::class,
            \Symfony\Component\HttpKernel\Kernel::class => \RZ\Roadiz\Core\Kernel::class,
            \RZ\Roadiz\CoreBundle\Document\MediaFinder\SoundcloudEmbedFinder::class => \RZ\Roadiz\Utils\MediaFinders\SoundcloudEmbedFinder::class,
            \RZ\Roadiz\CoreBundle\Document\MediaFinder\YoutubeEmbedFinder::class => \RZ\Roadiz\Utils\MediaFinders\YoutubeEmbedFinder::class,
            \RZ\Roadiz\CoreBundle\Document\MediaFinder\VimeoEmbedFinder::class => \RZ\Roadiz\Utils\MediaFinders\VimeoEmbedFinder::class,
            \RZ\Roadiz\CoreBundle\Document\MediaFinder\DailymotionEmbedFinder::class => \RZ\Roadiz\Utils\MediaFinders\DailymotionEmbedFinder::class,
        ];
    }
}
