<?php

namespace C5j\FrontendComposer;

use Concrete\Core\Page\Page;
use Concrete\Core\Page\Type\Type;
use Concrete\Core\Permission\Checker;

trait PermissionCheckerTrait
{
    /**
     * @param Type $type
     *
     * @return bool
     */
    protected function canAddPageType(Type $type): bool
    {
        $cp = new Checker($type);

        return $cp->canAddFromFrontendComposer();
    }

    /**
     * @param Page $page
     *
     * @return bool
     */
    protected function canEditPage(Page $page): bool
    {
        if ($page->isPageDraft()) {
            return $this->canAddPageType($page->getPageTypeObject());
        }

        $cp = new Checker($page);

        return $cp->canEditInFrontendComposer();
    }
}
