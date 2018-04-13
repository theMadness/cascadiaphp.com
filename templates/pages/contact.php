<?php declare(strict_types=1) ?>
<?php /** @var \CascadiaPHP\Site\Template\Template $this */ ?>
<?php
// Set the template
$this->layout('layout', [
    'active' => '/schedule'
]);

// Start the css section
$this->start('css');

// Echo out the stuff we want to be in the css section
echo $this->inline('/css/pages/brand.css');

// Stop the css section
$this->stop();
?>

<?php $this->start('components'); ?>
<script async custom-element="amp-form" src="https://cdn.ampproject.org/v0/amp-form-0.1.js"></script>
<script async custom-template="amp-mustache" src="https://cdn.ampproject.org/v0/amp-mustache-0.1.js"></script>
<?php $this->stop() ?>

<h2 class="center my0 p3">Cascadia PHP is a Portland Oregon based Non Profit</h2>
<h3 class="center my0 p3">Run by people who love the Pacific Northwest</h3>

<div class="contributors mx1 my1">
    <?php $this->insert('structure/organizer', ['name' => '👑 Alena Holligan', 'role' => 'President']) ?>
    <?php $this->insert('structure/organizer', ['name' => '🤴 Kevin DeCapite', 'role' => 'Vice President']) ?>
    <?php $this->insert('structure/organizer', ['name' => '🏦 Melinda Serven', 'role' => 'Treasurer']) ?>
    <?php $this->insert('structure/organizer', ['name' => '🗒️ Korvin Szanto', 'role' => 'Secretary']) ?>
    <?php $this->insert('structure/organizer', ['name' => 'Danielle Grillenzoni']) ?>
    <?php $this->insert('structure/organizer', ['name' => 'Kurtis Holsapple']) ?>
</div>

<h3 class="center">
    Contact Us
</h3>
