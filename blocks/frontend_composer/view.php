<?php

use Concrete\Core\Application\Service\Composer;
use Concrete\Core\Error\ErrorList\ErrorList;
use Concrete\Core\Form\Service\Form;
use Concrete\Core\Page\Page;
use Concrete\Core\Page\Type\Composer\FormLayoutSet;
use Concrete\Core\Page\Type\Composer\FormLayoutSetControl;
use Concrete\Core\Validation\CSRF\Token;
use Concrete\Core\View\View;

defined('C5_EXECUTE') or die('Access Denied.');

/* @var Composer $composer */
/* @var ErrorList $error */
/* @var Page $page */
/* @var Form $form */
/* @var View $view */
/* @var FormLayoutSet[] $fieldSets */
/* @var Token $token */
$token = Core::make('token');

if (isset($error) && is_object($error) && $error->has()) {
    ?>
    <div class="alert alert-danger">
        <?= $error; ?>
    </div>
    <?php
}

if (isset($pagetype) && is_object($pagetype) && isset($action_url) && !empty($action_url) && isset($composer) && is_object($composer) && isset($page) && is_object($page)) {
    ?>
    <form method="post" action="<?= h($action_url); ?>">
        <?php $token->output('frontend_composer_save'); ?>
        <?php $composer->display($pagetype, $page); ?>
        <?= $form->submit('submit', $composer->getPublishButtonTitle($page), ['class' => 'btn-primary']); ?>
    </form>
    <?php
} elseif (isset($fieldSets)) {
    ?>
    <h2><?= t('Successfully saved.') ?></h2>
        <?php
    foreach ($fieldSets as $fieldSet) {
        if ($fieldSet->getPageTypeComposerFormLayoutSetDisplayName()) {
            ?>
            <h3><?= $fieldSet->getPageTypeComposerFormLayoutSetDisplayName() ?></h3>
            <?php
        }
        ?>
        <?php
        $controls = FormLayoutSetControl::getList($fieldSet);
        /** @var FormLayoutSetControl $control */
        foreach ($controls as $control) {
            ?>
            <h4><?= $control->getPageTypeComposerControlDisplayLabel() ?></h4>
            <?php
            $composerControl = $control->getPageTypeComposerControlObject();
            $composerControl->setPageObject($page);
            $value = $composerControl->getPageTypeComposerControlDraftValue();
            if (is_object($value)) {
                if (method_exists($value, 'display')) {
                    $value->display();
                } elseif (method_exists($value, 'getDisplayValue')) {
                    ?>
                    <p><?= $value->getDisplayValue(); ?></p>
                    <?php
                }
            } else {
                ?>
                <p><?= $composerControl->getPageTypeComposerControlDraftValue() ?></p>
                <?php
            }
        }
    }
    ?>
    <?php
} else { ?>
    <p><?= t('Invalid Composer Form.'); ?></p>
<?php }
