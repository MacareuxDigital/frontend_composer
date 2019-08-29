<?php

use Concrete\Core\Application\Service\Composer;
use Concrete\Core\Error\ErrorList\ErrorList;
use Concrete\Core\Page\Page;
use Concrete\Core\Validation\CSRF\Token;
use Concrete\Core\View\View;

defined('C5_EXECUTE') or die('Access Denied.');

/* @var Composer $composer */
/* @var ErrorList $error */
/* @var Page $page */
/* @var Token $token */
/* @var View $view */
$token = Core::make('token');

if (isset($error) && is_object($error) && $error->has()) {
    ?>
    <div class="alert alert-danger">
        <?= $error; ?>
    </div>
    <?php
}

if (isset($pagetype) && is_object($pagetype) && isset($action_url) && !empty($action_url) && isset($composer) && is_object($composer) && isset($page) && is_object($page)) {
    $label = ($page->isPageDraft()) ? t('Add') : t('Save'); ?>
    <form method="post" action="<?= h($action_url); ?>">
        <?php $token->output('frontend_composer_save'); ?>
        <?php $composer->display($pagetype, $page); ?>
        <?= $form->submit('submit', $label, ['class' => 'btn-primary']); ?>
    </form>
    <?php
} else { ?>
    <p><?= t('Invalid Composer Form.'); ?></p>
<?php }
