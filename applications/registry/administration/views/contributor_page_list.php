<?php $this->load->view('header'); ?>
<div class="container" id="main-content">
    <div class="widget-box">
        <div class="widget-title">
            <h5>Contributor page under Requested Status</h5>
        </div>
        <div class="widget-content">
            <?php if($page_requested->num_rows()==0): ?>
                <p>There are no contributor page waiting to be assessed</p>
            <?php endif; ?>
            <ul>
                <?php foreach($page_requested->result() as $row): ?>
                    <li>
                        <span class="badge"><?php echo $row->status ?></span>
                        <?php echo anchor(portal_url('group/cms/#/groups/'.$row->name), $row->name) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <div class="widget-box">
        <div class="widget-title">
            <h5>All Contributor Pages</h5>
        </div>
        <div class="widget-content">
            <ul>
                <?php foreach($page_all->result() as $row): ?>
                    <li>
                        <span class="badge"><?php echo $row->status ?></span>
                        <?php echo anchor(portal_url('group/cms/#/groups/'.$row->name), $row->name) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>
<?php $this->load->view('footer'); ?>
