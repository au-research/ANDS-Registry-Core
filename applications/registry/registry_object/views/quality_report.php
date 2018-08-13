<p>
    Metadata Content Report - Elements shaded green are present in the RIF-CS metadata. Elements shaded yellow are absent. Use this report to identify if there are additional elements that could be included in your records to increase their discoverability, connectedness and impact.
</p>

<div class="qa_container">
<?php foreach ($report as $check):?>
    <span class="<?php echo $check['status'] === \ANDS\Registry\Providers\Quality\Types\CheckType::$PASS ? 'success' : 'warning'?>" style="display:block;">
        <?php echo $check['descriptor']; ?>
    </span>
<?php endforeach;?>
</div>
