<?php

namespace Concrete\Package\FrontendComposer\Block\FrontendComposer;

use C5j\FrontendComposer\PermissionCheckerTrait;
use Concrete\Core\Block\BlockController;
use Concrete\Core\Error\ErrorList\ErrorList;
use Concrete\Core\Http\Request;
use Concrete\Core\Page\Page;
use Concrete\Core\Page\Type\Composer\FormLayoutSet;
use Concrete\Core\Page\Type\Type;
use Concrete\Core\Permission\Checker;
use Concrete\Core\Url\Resolver\Manager\ResolverManagerInterface;
use Concrete\Core\Validation\CSRF\Token;
use Symfony\Component\HttpFoundation\RedirectResponse;

class Controller extends BlockController
{
    use PermissionCheckerTrait;

    /**
     * {@inheritdoc}
     */
    public function getBlockTypeName()
    {
        return t('Frontend Composer');
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockTypeDescription()
    {
        return t('Make it enables to create pages from front end of your site.');
    }

    public function action_add_page($ptID)
    {
        /** @var ErrorList $error */
        $error = $this->app->make('error');
        /** @var Token $token */
        $token = $this->app->make('token');
        if (!$token->validate('frontend_composer')) {
            $error->addError($this->token->getErrorMessage());
        }
        $pageType = Type::getByID($ptID);
        if (is_object($pageType)) {
            if (!$this->canAddPageType($pageType)) {
                $error->addError(t('You can not add this type of page.'));
            }
        } else {
            $error->addError(t('Invalid Page Type'));
        }

        if (!$error->has()) {
            $pt = $pageType->getPageTypeDefaultPageTemplateObject();
            $d = $pageType->createDraft($pt);
            $rcID = (int) $this->post('rcID');

            return $this->buildRedirectToAction('composer', $d, $rcID);
        }
        $this->set('error', $error);
    }

    public function action_edit_page($cID)
    {
        $c = Page::getByID($cID);
        if (is_object($c) && !$c->isError() && $this->canEditPage($c)) {
            return $this->buildRedirectToAction('composer', $c);
        }
    }

    public function action_composer($cID, $rcID = 0)
    {
        $page = Page::getByID($cID);

        /** @var ErrorList $error */
        $error = $this->app->make('error');
        /** @var Token $token */
        $token = $this->app->make('token');

        if (is_object($page) && !$page->isError() && $this->canEditPage($page)) {
            $this->set('page', $page);

            $pageType = $page->getPageTypeObject();
            $this->set('pagetype', $pageType);

            $composer = $this->app->make('helper/concrete/composer');
            $this->set('composer', $composer);

            $resolver = $this->app->make(ResolverManagerInterface::class);
            $action_url = $resolver->resolve([Page::getCurrentPage(), 'composer', $cID]);
            $this->set('action_url', $action_url);
            if ($this->canDiscardPage($page)) {
                $discard_url = $resolver->resolve([Page::getCurrentPage(), 'discard', $rcID]);
                $this->set('discard_url', $discard_url);
            }
            if (!$page->isPageDraft()) {
                $cancel_url = $resolver->resolve([$page]);
                $this->set('cancel_url', $cancel_url);
            }

            if (Request::isPost()) {
                if (!$token->validate('frontend_composer_save')) {
                    $error->addError($token->getErrorMessage());
                }

                if (!$error->has()) {
                    $pageTemplate = $pageType->getPageTypeDefaultPageTemplateObject();
                    $validator = $pageType->getPageTypeValidatorObject();
                    $error->add($validator->validateCreateDraftRequest($pageTemplate));
                    $error->add($validator->validatePublishDraftRequest($page));
                    // We can check if a current user has a permission to publish this page to the given location,
                    // but I skip it
                    // if ($page->isPageDraft()) {
                    //     $target = Page::getByID($page->getPageDraftTargetParentPageID());
                    // } else {
                    //     $target = Page::getByID($page->getCollectionParentID());
                    // }
                    // $error->add($validator->validatePublishLocationRequest($target, $page));

                    if (!$error->has()) {
                        $page = $page->getVersionToModify();
                        $saver = $pageType->getPageTypeSaverObject();
                        $saver->saveForm($page);
                        $pageType->publish($page);

                        return $this->buildRedirectToAction('complete', $page);
                    }
                }
            }
        } else {
            $error->addError(t('You can not edit this page.'));
        }

        $this->set('error', $error);
    }

    public function action_complete($cID)
    {
        $c = Page::getByID($cID, 'RECENT');
        if (is_object($c) && !$c->isError() && $this->canViewCompletePage($c)) {
            $fieldSets = FormLayoutSet::getList($c->getPageTypeObject());
            $this->set('page', $c);
            $this->set('fieldSets', $fieldSets);
        }
    }

    public function action_discard($rcID)
    {
        /** @var ErrorList $error */
        $error = $this->app->make('error');
        /** @var Token $token */
        $token = $this->app->make('token');

        if (!$token->validate('frontend_composer_discard')) {
            $error->addError($token->getErrorMessage());
        }

        $cID = $this->post('cID');
        $c = Page::getByID($cID);
        if (!is_object($c) || $c->isError()) {
            $error->addError(t('Invalid Page'));
        } else {
            if (!$this->canDiscardPage($c)) {
                $error->addError(t('You do not have a permission to discard a draft.'));
            }

            if (!$c->isPageDraft()) {
                $error->addError(t('You can discard draft pages only.'));
            }
        }

        if (!$error->has()) {
            $c->delete();

            /** @var ResolverManagerInterface $resolver */
            $resolver = $this->app->make(ResolverManagerInterface::class);
            $rc = Page::getByID($rcID);
            if (is_object($rc) && !$rc->isError()) {
                $return_url = $resolver->resolve([$rc]);
            } else {
                $return_url = $resolver->resolve(['/']);
            }

            return $this->buildRedirect($return_url);
        } else {
            $this->set('error', $error);
        }
    }

    public function buildRedirectToAction(string $action, Page $target, $rcID = 0): RedirectResponse
    {
        $c = Page::getCurrentPage();
        /** @var ResolverManagerInterface $resolver */
        $resolver = $this->app->make(ResolverManagerInterface::class);
        if ($rcID) {
            $url = $resolver->resolve([$c, $action, $target->getCollectionID(), $rcID]);
        } else {
            $url = $resolver->resolve([$c, $action, $target->getCollectionID()]);
        }

        return $this->buildRedirect($url);
    }
}
