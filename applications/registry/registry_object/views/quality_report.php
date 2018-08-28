<p>
    Elements shaded green are present in the RIF-CS metadata. Elements shaded yellow are absent. Use this report to identify if there are additional elements that could be included in your records to increase their discoverability, connectedness and <a href="https://documentation.ands.org.au/display/DOC/Metadata+for+Impact%3A+make+RIF-CS+work+for+you" target="_blank">impact</a>.
</p>

<div class="qa_container">
<?php foreach ($report as $check):?>
    <span class="<?php echo $check['status'] === \ANDS\Registry\Providers\Quality\Types\CheckType::$PASS ? 'success' : 'warning'?>" style="display:block;">
        <?php echo $check['descriptor']; ?>
    </span>
<?php endforeach;?>
</div>
