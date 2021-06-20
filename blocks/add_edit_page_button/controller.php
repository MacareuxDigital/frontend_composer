<?php

namespace Concrete\Package\FrontendComposer\Block\AddEditPageButton;

use C5j\FrontendComposer\PermissionCheckerTrait;
use Concrete\Core\Block\BlockController;
use Concrete\Core\Form\Service\Widget\PageSelector;
use Concrete\Core\Page\Page;
use Concrete\Core\Page\Type\Type;

class Controller extends BlockController
{
    use PermissionCheckerTrait;

    protected $btTable = 'btAddEditPageButton';
    protected $btInterfaceWidth = '400';
    protected $btInterfaceHeight = '350';
    protected $btWrapperClass = 'ccm-ui';
    protected $btCacheBlockRecord = true;

    /**
     * {@inheritdoc}
     */
    public function getBlockTypeName()
    {
        return t('Add/Edit Page Button');
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockTypeDescription()
    {
        return t('A link to the frontend composer.');
    }

    public function add()
    {
        $this->edit();
    }

    public function edit()
    {
        $types = ['' => t('** Select Page Type')];
        $typeList = Type::getList();
        /** @var Type $type */
        foreach ($typeList as $type) {
            $types[$type->getPageTypeID()] = $type->getPageTypeDisplayName();
        }

        $this->set('types', $types);

        $pageSelector = $this->app->make(PageSelector::class);
        $this->set('pageSelector', $pageSelector);
    }

    /**
     * {@inheritdoc}
     */
    public function validate($args)
    {
        $e = parent::validate($args);
        if (!in_array($args['type'], ['add', 'edit'])) {
            $e->add(t('Invalid Button Type'));
        }
        if (!$args['tcID']) {
            $e->add(t('Please select Composer Page.'));
        }

        return $e;
    }

    /**
     * {@inheritdoc}
     */
    public function save($args)
    {
        $args['type'] = (isset($args['type'])) ? (string) $args['type'] : '';
        $args['ptID'] = (isset($args['ptID'])) ? (int) $args['ptID'] : 0;
        $args['tcID'] = (isset($args['tcID'])) ? (int) $args['tcID'] : 0;

        parent::save($args);
    }

    public function view()
    {
        $tcID = (isset($this->tcID)) ? (int) $this->tcID : null;
        $tc = Page::getByID($tcID);
        $this->set('target', $tc);

        $buttonLabel = t('Edit');
        $buttonType = (isset($this->type)) ? (string) $this->type : 'edit';
        $disabled = false;

        if ($buttonType == 'add') {
            $ptID = (isset($this->ptID)) ? (int) $this->ptID : null;
            $pt = Type::getByID($ptID);
            if (is_object($pt)) {
                $buttonLabel = t('Add %s', $pt->getPageTypeDisplayName());
                $this->set('pageType', $pt);
                if (!$this->canAddPageType($pt)) {
                    $disabled = true;
                }
            }
        } else {
            $c = Page::getCurrentPage();
            if (!$this->canEditPage($c)) {
                $disabled = true;
            }
        }

        $this->set('buttonLabel', $buttonLabel);
        $this->set('disabled', $disabled);
    }
}
