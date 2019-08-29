<?php
defined('C5_EXECUTE') or die('Access Denied.');
/** @var \Concrete\Core\Form\Service\Form $form */
/** @var \Concrete\Core\Form\Service\Widget\PageSelector $pageSelector */
$types = (isset($types)) ? (array) $types : [];
$type = (isset($type)) ? (string) $type : null;
$ptID = (isset($ptID)) ? (int) $ptID : null;
$tcID = (isset($tcID)) ? (int) $tcID : null;
?>
<div class="form-group">
    <?= $form->label('type', t('Button Type')); ?>
    <div class="radio">
        <label>
            <?= $form->radio('type', 'add', $type); ?>
            <?= t('Add'); ?>
        </label>
    </div>
    <div class="radio">
        <label>
            <?= $form->radio('type', 'edit', $type); ?>
            <?= t('Edit'); ?>
        </label>
    </div>
</div>
<div class="form-group" id="ccm_pageTypeSelector">
    <?= $form->label('ptID', t('Page Type')); ?>
    <?= $form->select('ptID', $types, $ptID); ?>
</div>
<div class="form-group">
    <?= $form->label('tcID', t('Composer Page')); ?>
    <?= $pageSelector->selectPage('tcID', $tcID); ?>
    <span class="help-block"><?= t('Please select the page that contains Frontend Composer block.'); ?></span>
</div>