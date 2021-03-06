<?php
/** @var TheNandan\Grids\Components\SelectFilter $component */
?>
<?php if ($component->getLabel()): ?>
    <span><?= $component->getLabel() ?></span>
<?php endif ?>
<?=
\Form::select(
    $component->getInputName(),
    $component->getVariants(),
    $component->getValue(),
    [
        'class' => "form-control form-control-sm",
        'style' => 'display: inline; width: 160px; margin-right: 10px'
    ]
);
?>
