<?php

use Concrete\Core\Page\Page;

defined('C5_EXECUTE') or die('Access Denied.');

/** @var \Concrete\Core\Validation\CSRF\Token $token */
$token = Core::make('token');
$c = Page::getCurrentPage();
$buttonLabel = (isset($buttonLabel)) ? (string) $buttonLabel : '';
$disabled = (isset($disabled)) ? (bool) $disabled : false;

if (isset($target) && is_object($target)) {
    $action = URL::to($target, 'edit_page', $c->getCollectionID());
    /** @var \Concrete\Core\Page\Type\Type $pageType */
    if (isset($pageType) && is_object($pageType)) {
        $action = URL::to($target, 'add_page', $pageType->getPageTypeID());
    }
    $attrs = ['class' => 'btn-primary'];
    if ($disabled) {
        $attrs['disabled'] = 'disabled';
    } ?>
    <form method="post" action="<?= h($action); ?>">
        <?php $token->output('frontend_composer'); ?>
        <?php echo $form->submit('submit', $buttonLabel, $attrs); ?>
    </form>
    <?php
} else {
        ?>
    <p><?= t('Form not found'); ?></p>
    <?php
    }
