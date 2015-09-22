<div class="actions columns large-2 medium-3">
    <h3><?= __('Actions') ?></h3>
    <ul class="side-nav">
        <li><?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $federalConstituency->id_no],
                ['confirm' => __('Are you sure you want to delete # {0}?', $federalConstituency->id_no)]
            )
        ?></li>
        <li><?= $this->Html->link(__('List Federal Constituencies'), ['action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('List States'), ['controller' => 'States', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New State'), ['controller' => 'States', 'action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Local Govts'), ['controller' => 'LocalGovts', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Local Govt'), ['controller' => 'LocalGovts', 'action' => 'add']) ?></li>
    </ul>
</div>
<div class="federalConstituencies form large-10 medium-9 columns">
    <?= $this->Form->create($federalConstituency) ?>
    <fieldset>
        <legend><?= __('Edit Federal Constituency') ?></legend>
        <?php
            echo $this->Form->input('state_id', ['options' => $states]);
            echo $this->Form->input('federal_constituency');
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
